#!/usr/bin/env php
<?php
/**
 * Pre-generate attack data for dashboard
 * Run via cron every 5 minutes to cache expensive operations
 */

// Output file for cached data
$cacheFile = '/tmp/attack_data_cache.json';
$lockFile = '/tmp/attack_data_cache.lock';

// Prevent concurrent execution
$lockHandle = fopen($lockFile, 'w');
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
    echo "Another instance is already running\n";
    exit(0);
}

echo "[" . date('Y-m-d H:i:s') . "] Starting attack data generation...\n";

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
        '/ftpd.*from ([\d\.]+)/' => 21,
        '/telnetd.*from ([\d\.]+)/' => 23,
    ];

    foreach ($lines as $line) {
        foreach ($patterns as $pattern => $targetPort) {
            if (preg_match($pattern, $line, $matches)) {
                $ip = $matches[1];

                // Skip local/private IPs
                if (preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.|127\.)/', $ip)) {
                    break;
                }

                // Extract timestamp
                preg_match('/^(\w+\s+\d+\s+\d+:\d+:\d+)/', $line, $timeMatch);
                $timestamp = $timeMatch[1] ?? date('M d H:i:s');

                // Convert to Unix timestamp for filtering
                $timeWithYear = $timestamp . ' ' . $currentYear;
                $unixTime = strtotime($timeWithYear);

                // Handle year rollover
                if ($unixTime > time()) {
                    $unixTime = strtotime($timestamp . ' ' . ($currentYear - 1));
                }

                // Skip attacks older than 7 days
                if ($unixTime < $cutoffTime) {
                    break;
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

                if (!isset($portStats[$targetPort])) {
                    $portStats[$targetPort] = 0;
                }
                $portStats[$targetPort]++;

                break;
            }
        }
    }

    return ['attacks' => $attacks, 'portStats' => $portStats];
}

// Parse Apache access.log for WordPress login attempts
function parseWordPressLog($logFile, $daysBack = 7) {
    $attacks = [];
    $portStats = [];

    if (!file_exists($logFile)) {
        return ['attacks' => $attacks, 'portStats' => $portStats];
    }

    $cutoffTime = time() - ($daysBack * 86400);

    $lines = file($logFile);
    if ($lines === false) {
        return ['attacks' => $attacks, 'portStats' => $portStats];
    }

    foreach ($lines as $line) {
        if (preg_match('/^(\S+) .* \[([^\]]+)\] "POST \/wp-login\.php HTTP\/\d\.\d" 200 [4-9]\d{3}/', $line, $matches)) {
            $ip = $matches[1];
            $timestamp = $matches[2];

            // Skip local/private IPs
            if (preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.|127\.)/', $ip)) {
                continue;
            }

            $timestampFormatted = preg_replace('/:/', ' ', $timestamp, 1);
            $timestampFormatted = str_replace('/', '-', $timestampFormatted);
            $unixTime = strtotime($timestampFormatted);

            if ($unixTime < $cutoffTime) {
                continue;
            }

            $readableTime = date('M d H:i:s', $unixTime);

            if (!isset($attacks[$ip])) {
                $attacks[$ip] = [
                    'ip' => $ip,
                    'count' => 0,
                    'firstSeen' => $readableTime,
                    'lastSeen' => $readableTime,
                    'attempts' => [],
                    'service' => 'wordpress'
                ];
            }

            $attacks[$ip]['count']++;
            $attacks[$ip]['lastSeen'] = $readableTime;
            $attacks[$ip]['attempts'][] = $readableTime;

            if (!isset($portStats[80])) {
                $portStats[80] = 0;
            }
            $portStats[80]++;
        }
    }

    return ['attacks' => $attacks, 'portStats' => $portStats];
}

// Parse WordPress "Limit Login Attempts Reloaded" plugin data
function parseWordPressPluginData($daysBack = 7) {
    $attacks = [];
    $portStats = [];

    $wpLoad = dirname(__DIR__) . '/wp-load.php';
    if (!file_exists($wpLoad)) {
        return ['attacks' => $attacks, 'portStats' => $portStats];
    }

    define('WP_USE_THEMES', false);
    require_once($wpLoad);

    $logged = get_option('limit_login_logged', array());
    $lockouts = get_option('limit_login_lockouts', array());
    $blacklist = get_option('limit_login_blacklist', array());

    $cutoffTime = time() - ($daysBack * 86400);

    if (is_array($logged)) {
        foreach ($logged as $timestamp => $event) {
            if ($timestamp < $cutoffTime) {
                continue;
            }

            $ip = $event['ip'];

            if (preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.|127\.)/', $ip)) {
                continue;
            }

            $readableTime = date('M d H:i:s', $timestamp);

            if (!isset($attacks[$ip])) {
                $attacks[$ip] = [
                    'ip' => $ip,
                    'count' => 0,
                    'firstSeen' => $readableTime,
                    'lastSeen' => $readableTime,
                    'attempts' => [],
                    'service' => 'wordpress',
                    'locked' => false,
                    'blacklisted' => false
                ];
            }

            $attacks[$ip]['count'] += $event['counter'];
            $attacks[$ip]['lastSeen'] = $readableTime;
            $attacks[$ip]['attempts'][] = $readableTime;

            if (isset($lockouts[$ip]) && $lockouts[$ip] > time()) {
                $attacks[$ip]['locked'] = true;
            }

            if (is_array($blacklist) && in_array($ip, $blacklist)) {
                $attacks[$ip]['blacklisted'] = true;
            }

            if (!isset($portStats[80])) {
                $portStats[80] = 0;
            }
            $portStats[80] += $event['counter'];
        }
    }

    return ['attacks' => $attacks, 'portStats' => $portStats];
}

// Get fail2ban banned IPs count
function getFail2banBannedCount() {
    $bannedCount = 0;
    $output = [];
    $returnVar = 0;

    exec('sudo fail2ban-client status 2>/dev/null', $output, $returnVar);

    if ($returnVar === 0 && !empty($output)) {
        foreach ($output as $line) {
            if (preg_match('/Jail list:\s*(.+)/', $line, $matches)) {
                $jails = array_map('trim', explode(',', $matches[1]));

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
    static $apiCalls = 0;

    $cacheDir = '/tmp/geo_cache/';
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $cacheFileLocal = $cacheDir . md5($ip) . '.json';

    // Check cache (24 hour cache for geo data)
    if (file_exists($cacheFileLocal) && (time() - filemtime($cacheFileLocal)) < 86400) {
        return json_decode(file_get_contents($cacheFileLocal), true);
    }

    // Rate limit: 1.5 seconds between requests
    if ($apiCalls > 0) {
        usleep(1500000);
    }
    $apiCalls++;

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
        file_put_contents($cacheFileLocal, json_encode($data));
        return $data;
    }

    return null;
}

// Main execution
try {
    $sshLogFiles = [
        '/var/log/auth.log',
        '/var/log/auth.log.1',
        '/var/log/auth.log.2.gz',
        '/var/log/auth.log.3.gz',
        '/var/log/auth.log.4.gz'
    ];

    $wordpressLogFiles = [
        '/var/log/apache2/access.log',
        '/var/log/apache2/access.log.1'
    ];

    $attacks = [];
    $portStats = [];

    echo "Parsing SSH logs...\n";
    foreach ($sshLogFiles as $logFile) {
        if (file_exists($logFile)) {
            echo "  - $logFile\n";
            $logData = parseAuthLog($logFile, 7);

            foreach ($logData['attacks'] as $ip => $data) {
                if (!isset($attacks[$ip])) {
                    $attacks[$ip] = $data;
                    $attacks[$ip]['service'] = 'ssh';
                } else {
                    $attacks[$ip]['count'] += $data['count'];
                    $attacks[$ip]['attempts'] = array_merge($attacks[$ip]['attempts'], $data['attempts']);
                    if (strtotime($data['lastSeen']) > strtotime($attacks[$ip]['lastSeen'])) {
                        $attacks[$ip]['lastSeen'] = $data['lastSeen'];
                    }
                    if (strtotime($data['firstSeen']) < strtotime($attacks[$ip]['firstSeen'])) {
                        $attacks[$ip]['firstSeen'] = $data['firstSeen'];
                    }
                }
            }

            foreach ($logData['portStats'] as $port => $count) {
                if (!isset($portStats[$port])) {
                    $portStats[$port] = 0;
                }
                $portStats[$port] += $count;
            }
        }
    }

    echo "Parsing WordPress logs...\n";
    foreach ($wordpressLogFiles as $logFile) {
        if (file_exists($logFile)) {
            echo "  - $logFile\n";
            $logData = parseWordPressLog($logFile, 7);

            foreach ($logData['attacks'] as $ip => $data) {
                if (!isset($attacks[$ip])) {
                    $attacks[$ip] = $data;
                } else {
                    $attacks[$ip]['count'] += $data['count'];
                    $attacks[$ip]['attempts'] = array_merge($attacks[$ip]['attempts'], $data['attempts']);
                    if (strtotime($data['lastSeen']) > strtotime($attacks[$ip]['lastSeen'])) {
                        $attacks[$ip]['lastSeen'] = $data['lastSeen'];
                    }
                    if (strtotime($data['firstSeen']) < strtotime($attacks[$ip]['firstSeen'])) {
                        $attacks[$ip]['firstSeen'] = $data['firstSeen'];
                    }
                }
            }

            foreach ($logData['portStats'] as $port => $count) {
                if (!isset($portStats[$port])) {
                    $portStats[$port] = 0;
                }
                $portStats[$port] += $count;
            }
        }
    }

    echo "Parsing WordPress plugin data...\n";
    $pluginData = parseWordPressPluginData(7);
    foreach ($pluginData['attacks'] as $ip => $data) {
        if (!isset($attacks[$ip])) {
            $attacks[$ip] = $data;
        } else {
            $attacks[$ip]['count'] += $data['count'];
            $attacks[$ip]['attempts'] = array_merge($attacks[$ip]['attempts'], $data['attempts']);
            if (isset($data['locked']) && $data['locked']) {
                $attacks[$ip]['locked'] = true;
            }
            if (isset($data['blacklisted']) && $data['blacklisted']) {
                $attacks[$ip]['blacklisted'] = true;
            }
            if (strtotime($data['lastSeen']) > strtotime($attacks[$ip]['lastSeen'])) {
                $attacks[$ip]['lastSeen'] = $data['lastSeen'];
            }
            if (strtotime($data['firstSeen']) < strtotime($attacks[$ip]['firstSeen'])) {
                $attacks[$ip]['firstSeen'] = $data['firstSeen'];
            }
        }
    }

    foreach ($pluginData['portStats'] as $port => $count) {
        if (!isset($portStats[$port])) {
            $portStats[$port] = 0;
        }
        $portStats[$port] += $count;
    }

    echo "Found " . count($attacks) . " unique IPs\n";

    // Sort and geolocate top 100
    echo "Geolocating top 100 IPs...\n";
    $sortedAttacks = $attacks;
    uasort($sortedAttacks, function($a, $b) {
        return $b['count'] - $a['count'];
    });

    $geolocatedAttacks = [];
    $count = 0;

    foreach ($sortedAttacks as $ip => $data) {
        if ($count >= 100) break;

        if ($count % 10 == 0) {
            echo "  Progress: $count/100\n";
        }

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
                'firstSeen' => $data['firstSeen'],
                'service' => $data['service'] ?? 'ssh',
                'locked' => $data['locked'] ?? false,
                'blacklisted' => $data['blacklisted'] ?? false
            ];
        } else {
            $geolocatedAttacks[] = [
                'ip' => $ip,
                'count' => $data['count'],
                'lat' => null,
                'lon' => null,
                'country' => 'Unknown',
                'countryCode' => 'XX',
                'city' => 'Unknown',
                'lastSeen' => $data['lastSeen'],
                'firstSeen' => $data['firstSeen'],
                'service' => $data['service'] ?? 'ssh',
                'locked' => $data['locked'] ?? false,
                'blacklisted' => $data['blacklisted'] ?? false
            ];
        }

        $count++;
    }

    echo "Calculating statistics...\n";
    $countryCounts = [];
    foreach ($geolocatedAttacks as $attack) {
        $country = $attack['countryCode'];
        if (!isset($countryCounts[$country])) {
            $countryCounts[$country] = 0;
        }
        $countryCounts[$country] += $attack['count'];
    }

    $currentYear = date('Y');
    $recentAttacksList = [];

    foreach ($geolocatedAttacks as $attack) {
        $timeWithYear = $attack['lastSeen'] . ' ' . $currentYear;
        $timestamp = strtotime($timeWithYear);

        if ($timestamp > time()) {
            $timestamp = strtotime($attack['lastSeen'] . ' ' . ($currentYear - 1));
        }

        $recentAttacksList[] = [
            'ip' => $attack['ip'],
            'time' => $attack['lastSeen'],
            'timestamp' => $timestamp,
            'location' => $attack['city'] . ', ' . $attack['country'],
            'count' => $attack['count'],
            'service' => $attack['service'] ?? 'ssh',
            'locked' => $attack['locked'] ?? false,
            'blacklisted' => $attack['blacklisted'] ?? false
        ];
    }

    usort($recentAttacksList, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });

    $recentAttacks = array_slice($recentAttacksList, 0, 20);

    echo "Getting fail2ban status...\n";
    $bannedIpCount = getFail2banBannedCount();

    $wpBlacklist = get_option('limit_login_blacklist', array());
    $wordpressBannedCount = is_array($wpBlacklist) ? count($wpBlacklist) : 0;
    $totalBannedIps = $bannedIpCount + $wordpressBannedCount;

    arsort($portStats);

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
        'bannedIps' => $totalBannedIps,
        'countryCounts' => $countryCounts,
        'portStats' => $portData,
        'recentAttacks' => $recentAttacks,
        'lastUpdate' => date('Y-m-d H:i:s')
    ];

    echo "Writing cache file...\n";
    file_put_contents($cacheFile, json_encode($result));
    chmod($cacheFile, 0644);

    echo "[" . date('Y-m-d H:i:s') . "] Complete! Generated data for " . count($attacks) . " IPs\n";
    echo "Total attempts: " . $result['totalAttempts'] . "\n";
    echo "Countries: " . $result['countryCount'] . "\n";
    echo "Banned IPs: " . $result['bannedIps'] . "\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}
