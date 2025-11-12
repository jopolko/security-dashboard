<?php
// Enable gzip compression for faster transfer
if (!ob_start('ob_gzhandler')) ob_start();

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate'); // No browser cache - always fresh

// No main cache file - we parse logs on every request for real-time data
// Geolocation is still cached per-IP for 24 hours in /tmp/geo_cache/

// Parse auth.log for failed login attempts (with 7-day filter)
function parseAuthLog($logFile, $daysBack = 7) {
    $attacks = [];
    $portStats = [];

    if (!file_exists($logFile)) {
        return ['attacks' => $attacks, 'portStats' => $portStats];
    }

    // Calculate cutoff timestamp (7 days ago)
    $cutoffTime = time() - ($daysBack * 86400);
    $currentYear = date('Y');

    // Read log file (handle compressed .gz files)
    if (substr($logFile, -3) === '.gz') {
        $lines = gzfile($logFile);
    } else {
        $lines = file($logFile);
    }

    if ($lines === false) {
        return ['attacks' => $attacks, 'portStats' => $portStats];
    }

    // Patterns to match failed login attempts
    // Note: We track the SERVICE being attacked, not the source port
    $patterns = [
        '/sshd.*Failed password for .+ from ([\d\.]+) port/' => 22,
        '/sshd.*Invalid user .+ from ([\d\.]+) port/' => 22,
        '/sshd.*Connection closed by authenticating user .+ ([\d\.]+) port/' => 22,
        '/sshd.*Disconnected from authenticating user .+ ([\d\.]+) port/' => 22,
        '/sshd.*Disconnected from invalid user .+ ([\d\.]+) port/' => 22,
        '/sshd.*Failed password for invalid user .+ from ([\d\.]+) port/' => 22,
        '/sshd.*Received disconnect from ([\d\.]+) port/' => 22,
        '/sshd.*Connection reset by ([\d\.]+) port/' => 22,
        '/sshd.*Bad protocol version identification .+ from ([\d\.]+) port/' => 22,
        // Add patterns for other services if found in logs
        '/ftpd.*from ([\d\.]+)/' => 21,
        '/telnetd.*from ([\d\.]+)/' => 23,
    ];

    foreach ($lines as $line) {
        foreach ($patterns as $pattern => $targetPort) {
            if (preg_match($pattern, $line, $matches)) {
                $ip = $matches[1];

                // Skip local/private IPs
                if (preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.|127\.)/', $ip)) {
                    break; // Break to next line
                }

                // Extract timestamp
                preg_match('/^(\w+\s+\d+\s+\d+:\d+:\d+)/', $line, $timeMatch);
                $timestamp = $timeMatch[1] ?? date('M d H:i:s');

                // Convert to Unix timestamp for filtering
                $timeWithYear = $timestamp . ' ' . $currentYear;
                $unixTime = strtotime($timeWithYear);

                // Handle year rollover - if parsed date is in the future, it's from last year
                if ($unixTime > time()) {
                    $unixTime = strtotime($timestamp . ' ' . ($currentYear - 1));
                }

                // Skip attacks older than 7 days
                if ($unixTime < $cutoffTime) {
                    break; // Skip to next line
                }

                if (!isset($attacks[$ip])) {
                    $attacks[$ip] = [
                        'ip' => $ip,
                        'count' => 0,
                        'firstSeen' => $timestamp,
                        'lastSeen' => $timestamp,
                        'attempts' => []
                    ];
                }

                $attacks[$ip]['count']++;
                $attacks[$ip]['lastSeen'] = $timestamp;
                $attacks[$ip]['attempts'][] = $timestamp;

                // Track service/port being attacked (destination port)
                if (!isset($portStats[$targetPort])) {
                    $portStats[$targetPort] = 0;
                }
                $portStats[$targetPort]++;

                // Break after first successful match for this line
                break;
            }
        }
    }

    return ['attacks' => $attacks, 'portStats' => $portStats];
}

// Get fail2ban banned IPs count
function getFail2banBannedCount() {
    $bannedCount = 0;

    // Try to get fail2ban status
    $output = [];
    $returnVar = 0;

    // Get all jails
    exec('sudo fail2ban-client status 2>/dev/null', $output, $returnVar);

    if ($returnVar === 0 && !empty($output)) {
        // Parse jail list
        foreach ($output as $line) {
            if (preg_match('/Jail list:\s*(.+)/', $line, $matches)) {
                $jails = array_map('trim', explode(',', $matches[1]));

                // Get banned IPs for each jail
                foreach ($jails as $jail) {
                    if (empty($jail)) continue;

                    $jailOutput = [];
                    exec("sudo fail2ban-client status {$jail} 2>/dev/null", $jailOutput);

                    foreach ($jailOutput as $jailLine) {
                        if (preg_match('/Currently banned:\s*(\d+)/', $jailLine, $countMatch)) {
                            $bannedCount += (int)$countMatch[1];
                        }
                    }
                }
                break;
            }
        }
    }

    return $bannedCount;
}

// Get geolocation for IP using free API
function getGeoLocation($ip) {
    static $apiCalls = 0; // Track API calls for rate limiting

    $cacheDir = '/tmp/geo_cache/';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheFile = $cacheDir . md5($ip) . '.json';

    // Check cache (24 hour cache for geo data)
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    // Rate limit: ip-api.com allows 45 requests/minute
    // Sleep 1.5 seconds per API call to stay under limit (40/min)
    // Only sleep if we're making actual API calls (not cached)
    if ($apiCalls > 0) {
        usleep(1500000); // 1.5 seconds between requests
    }
    $apiCalls++;

    // Use ip-api.com (free, no key required, 45 req/min limit)
    $url = "http://ip-api.com/json/{$ip}?fields=status,country,countryCode,city,lat,lon,timezone";

    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    if ($data && $data['status'] === 'success') {
        file_put_contents($cacheFile, json_encode($data));
        return $data;
    }

    return null;
}

// Main execution
try {
    // Read current and rotated logs, filter to last 7 days
    $logFiles = [
        '/var/log/auth.log',
        '/var/log/auth.log.1',
        '/var/log/auth.log.2.gz',
        '/var/log/auth.log.3.gz',
        '/var/log/auth.log.4.gz'
        // Include up to 4 rotated logs to cover 7 days (assuming weekly rotation)
    ];

    $attacks = [];
    $portStats = [];

    foreach ($logFiles as $logFile) {
        if (file_exists($logFile)) {
            $logData = parseAuthLog($logFile, 7); // Last 7 days

            // Merge attacks (combine counts for same IP)
            foreach ($logData['attacks'] as $ip => $data) {
                if (!isset($attacks[$ip])) {
                    $attacks[$ip] = $data;
                } else {
                    // Merge attack data for same IP
                    $attacks[$ip]['count'] += $data['count'];
                    $attacks[$ip]['attempts'] = array_merge($attacks[$ip]['attempts'], $data['attempts']);
                    // Keep the most recent lastSeen
                    if (strtotime($data['lastSeen']) > strtotime($attacks[$ip]['lastSeen'])) {
                        $attacks[$ip]['lastSeen'] = $data['lastSeen'];
                    }
                    // Keep the earliest firstSeen
                    if (strtotime($data['firstSeen']) < strtotime($attacks[$ip]['firstSeen'])) {
                        $attacks[$ip]['firstSeen'] = $data['firstSeen'];
                    }
                }
            }

            // Merge port stats
            foreach ($logData['portStats'] as $port => $count) {
                if (!isset($portStats[$port])) {
                    $portStats[$port] = 0;
                }
                $portStats[$port] += $count;
            }
        }
    }

    // Geolocate IPs (limit to top 100 by attempt count for 7-day view)
    $sortedAttacks = $attacks;
    uasort($sortedAttacks, function($a, $b) {
        return $b['count'] - $a['count'];
    });

    $geolocatedAttacks = [];
    $count = 0;

    foreach ($sortedAttacks as $ip => $data) {
        if ($count >= 100) break; // Limit to 100 for comprehensive 7-day view

        $geo = getGeoLocation($ip);

        if ($geo) {
            $geolocatedAttacks[] = [
                'ip' => $ip,
                'count' => $data['count'],
                'lat' => $geo['lat'] ?? null,
                'lon' => $geo['lon'] ?? null,
                'country' => $geo['country'] ?? 'Unknown',
                'countryCode' => $geo['countryCode'] ?? 'XX',
                'city' => $geo['city'] ?? 'Unknown',
                'lastSeen' => $data['lastSeen'],
                'firstSeen' => $data['firstSeen']
            ];
        } else {
            // Add without geo data
            $geolocatedAttacks[] = [
                'ip' => $ip,
                'count' => $data['count'],
                'lat' => null,
                'lon' => null,
                'country' => 'Unknown',
                'countryCode' => 'XX',
                'city' => 'Unknown',
                'lastSeen' => $data['lastSeen'],
                'firstSeen' => $data['firstSeen']
            ];
        }

        $count++;
    }

    // Calculate statistics
    $countryCounts = [];
    foreach ($geolocatedAttacks as $attack) {
        $country = $attack['countryCode'];
        if (!isset($countryCounts[$country])) {
            $countryCounts[$country] = 0;
        }
        $countryCounts[$country] += $attack['count'];
    }

    // Recent attacks for log - sort by most recent first
    $currentYear = date('Y');
    $recentAttacksList = [];

    foreach ($geolocatedAttacks as $attack) {
        // Parse timestamp with current year (format: "Oct 19 09:30:27")
        $timeWithYear = $attack['lastSeen'] . ' ' . $currentYear;
        $timestamp = strtotime($timeWithYear);

        // Handle year rollover - if parsed date is in the future, it's from last year
        if ($timestamp > time()) {
            $timestamp = strtotime($attack['lastSeen'] . ' ' . ($currentYear - 1));
        }

        $recentAttacksList[] = [
            'ip' => $attack['ip'],
            'time' => $attack['lastSeen'],
            'timestamp' => $timestamp, // Unix timestamp for sorting and client conversion
            'location' => $attack['city'] . ', ' . $attack['country'],
            'count' => $attack['count']
        ];
    }

    // Sort by timestamp (most recent first)
    usort($recentAttacksList, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });

    // Keep timestamp field for client-side timezone conversion, take top 20
    $recentAttacks = array_slice($recentAttacksList, 0, 20);

    // Get fail2ban banned count
    $bannedIpCount = getFail2banBannedCount();

    // Sort port statistics
    arsort($portStats);

    // Get common port names
    $portNames = [
        22 => 'SSH',
        23 => 'Telnet',
        25 => 'SMTP',
        80 => 'HTTP',
        110 => 'POP3',
        143 => 'IMAP',
        443 => 'HTTPS',
        3306 => 'MySQL',
        3389 => 'RDP',
        5432 => 'PostgreSQL',
        8080 => 'HTTP Alt',
        21 => 'FTP',
        53 => 'DNS',
        445 => 'SMB',
        1433 => 'MSSQL',
        27017 => 'MongoDB'
    ];

    $portData = [];
    foreach ($portStats as $port => $count) {
        $portData[] = [
            'port' => $port,
            'count' => $count,
            'service' => $portNames[$port] ?? 'Unknown'
        ];
    }

    $result = [
        'attacks' => $geolocatedAttacks,
        'totalAttempts' => array_sum(array_column($attacks, 'count')),
        'uniqueIps' => count($attacks),
        'countryCount' => count($countryCounts),
        'bannedIps' => $bannedIpCount,
        'countryCounts' => $countryCounts,
        'portStats' => $portData,
        'recentAttacks' => $recentAttacks,
        'lastUpdate' => date('Y-m-d H:i:s')
    ];

    // No main cache - return fresh data every time
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'attacks' => [],
        'totalAttempts' => 0,
        'uniqueIps' => 0,
        'countryCount' => 0,
        'bannedIps' => 0,
        'countryCounts' => [],
        'portStats' => [],
        'recentAttacks' => []
    ]);
}
