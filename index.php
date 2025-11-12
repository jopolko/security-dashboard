<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- SEO Meta Tags -->
    <title>Security Threat Intelligence Dashboard | Real-Time Attack Monitoring</title>
    <meta name="description" content="Monitor real-time security threats with our comprehensive threat intelligence dashboard. Track SSH attacks, failed login attempts, banned IPs, and geographic attack patterns with fail2ban integration.">
    <meta name="keywords" content="security threat intelligence, SSH attack monitoring, fail2ban dashboard, server security, intrusion detection, IP ban monitoring, cybersecurity dashboard, attack visualization, network security, real-time threat monitoring">
    <meta name="author" content="Security Threat Intelligence">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Security Threat Intelligence Dashboard">
    <meta property="og:description" content="Real-time monitoring of security threats, SSH attacks, and banned IPs with geographic visualization.">
    <meta property="og:url" content="">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Security Threat Intelligence Dashboard">
    <meta name="twitter:description" content="Monitor real-time security threats with comprehensive attack visualization and fail2ban integration.">

    <!-- Additional SEO -->
    <link rel="canonical" href="">
    <meta name="theme-color" content="#FAF7F0">

    <!-- Resource Hints for Performance -->
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    <link rel="dns-prefetch" href="https://unpkg.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" as="style">
    <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" as="script">

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-1GZN9MX2P4"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-1GZN9MX2P4');
    </script>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --cream-bg: #FAF7F0;
            --cream-dark: #E8DCC4;
            --cream-darker: #D4C5A9;
            --accent-primary: #B8956A;
            --accent-secondary: #8B7355;
            --accent-danger: #C67B5C;
            --text-primary: #2C2416;
            --text-secondary: #5C5040;
            --border-color: #D4C5A9;
            --shadow: rgba(44, 36, 22, 0.08);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, var(--cream-bg) 0%, var(--cream-dark) 100%);
            color: var(--text-primary);
            overflow: hidden;
            position: relative;
        }

        /* Subtle background pattern */
        .grid-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(var(--cream-darker) 1px, transparent 1px),
                linear-gradient(90deg, var(--cream-darker) 1px, transparent 1px);
            background-size: 60px 60px;
            opacity: 0.3;
            pointer-events: none;
            z-index: 0;
        }

        .container {
            position: relative;
            z-index: 1;
            height: 100vh;
            display: grid;
            grid-template-rows: auto 1fr;
            padding: 20px;
            gap: 20px;
            max-width: 1920px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 22px 36px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 8px 32px var(--shadow);
            backdrop-filter: blur(10px);
            gap: 24px;
        }

        .title {
            font-size: 1.75em;
            font-weight: 600;
            color: var(--text-primary);
            letter-spacing: -0.5px;
            grid-column: 1;
            justify-self: start;
        }

        .title a {
            transition: opacity 0.2s ease;
        }

        .title a:hover {
            opacity: 0.7;
        }

        .subtitle {
            font-size: 0.7em;
            font-weight: 400;
            color: var(--text-secondary);
            margin-top: 4px;
            opacity: 0.8;
        }

        .status {
            display: flex;
            gap: 24px;
            font-size: 0.9em;
            grid-column: 2;
            justify-self: center;
        }

        .status-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px 24px;
            background: linear-gradient(135deg, rgba(184, 149, 106, 0.1), rgba(139, 115, 85, 0.05));
            border: 1px solid var(--border-color);
            border-radius: 12px;
            min-width: 120px;
            transition: all 0.3s ease;
        }

        .status-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px var(--shadow);
        }

        .status-label {
            color: var(--text-secondary);
            font-size: 0.75em;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .status-value {
            font-size: 2em;
            font-weight: 700;
            color: var(--accent-primary);
            line-height: 1;
        }

        /* Main content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 20px;
            overflow: hidden;
        }

        /* Map container */
        .map-container {
            position: relative;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px var(--shadow);
            background: white;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        .map-legend {
            position: absolute;
            bottom: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.98);
            padding: 16px 20px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 16px var(--shadow);
            z-index: 1000;
        }

        .legend-title {
            font-size: 0.85em;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
            font-size: 0.8em;
        }

        .legend-circle {
            border-radius: 50%;
            border: 2px solid var(--accent-secondary);
            background: var(--accent-danger);
            opacity: 0.7;
        }

        /* Sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
            padding-right: 8px;
            max-height: 100%;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: var(--cream-dark);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 3px;
        }

        /* Panel */
        .panel {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 8px 32px var(--shadow);
            backdrop-filter: blur(10px);
        }

        .panel-title {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            letter-spacing: -0.3px;
        }

        .panel-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: linear-gradient(180deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 2px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--cream-darker);
            animation: fadeIn 0.5s ease-in;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9em;
            font-weight: 500;
        }

        .stat-value {
            color: var(--accent-danger);
            font-weight: 700;
            font-size: 0.95em;
        }

        /* Attack log */
        .attack-log {
            /* Static list - no scrolling, fits exactly 4 entries */
        }

        .log-entry {
            padding: 14px;
            margin-bottom: 12px;
            background: linear-gradient(135deg, rgba(198, 123, 92, 0.08), rgba(184, 149, 106, 0.08));
            border-left: 3px solid var(--accent-danger);
            border-radius: 8px;
            font-size: 0.85em;
            animation: slideIn 0.4s ease-out;
            transition: all 0.2s ease;
        }

        .log-entry:hover {
            transform: translateX(4px);
            box-shadow: 0 2px 8px var(--shadow);
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .log-time {
            color: var(--accent-secondary);
            font-size: 0.9em;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .log-ip {
            color: var(--accent-danger);
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }

        .log-location {
            color: var(--text-secondary);
            font-size: 0.9em;
            margin-top: 4px;
        }

        .log-count {
            display: inline-block;
            background: var(--accent-danger);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: 600;
            margin-left: 8px;
        }

        /* Top countries */
        #topCountries {
            max-height: 340px;
            overflow-y: auto;
        }

        #topCountries::-webkit-scrollbar {
            width: 6px;
        }

        #topCountries::-webkit-scrollbar-track {
            background: var(--cream-dark);
            border-radius: 3px;
        }

        #topCountries::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 3px;
        }

        .country-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid var(--cream-darker);
        }

        .country-item:last-child {
            border-bottom: none;
        }

        .country-name {
            min-width: 100px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.9em;
        }

        .country-bar {
            flex: 1;
            height: 28px;
            background: var(--cream-dark);
            margin: 0 16px;
            border-radius: 14px;
            position: relative;
            overflow: hidden;
        }

        .country-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--accent-primary), var(--accent-secondary));
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(184, 149, 106, 0.3);
            position: relative;
            overflow: hidden;
        }

        .country-bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .country-count {
            min-width: 60px;
            text-align: right;
            color: var(--accent-danger);
            font-weight: 700;
            font-size: 0.95em;
        }

        .loading {
            text-align: center;
            padding: 40px;
            font-size: 1em;
            color: var(--text-secondary);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }


        /* Custom Leaflet popup styling */
        .leaflet-popup-content-wrapper {
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 8px 24px var(--shadow);
        }

        .leaflet-popup-content {
            margin: 16px;
            font-family: 'Inter', sans-serif;
            color: var(--text-primary);
        }

        .leaflet-popup-tip {
            background: rgba(255, 255, 255, 0.98);
            border: 1px solid var(--border-color);
        }

        /* Mobile Optimization */
        @media (max-width: 1024px) {
            .container {
                padding: 16px;
                gap: 16px;
            }

            .main-content {
                grid-template-columns: 1fr;
                grid-template-rows: 400px 1fr;
            }

            .sidebar {
                max-height: none;
            }

            .header {
                display: flex;
                flex-direction: column;
                padding: 20px 24px;
                align-items: center;
                gap: 16px;
            }

            .title {
                font-size: 1.3em;
                text-align: center;
            }

            .subtitle {
                font-size: 0.65em;
            }

            .status {
                gap: 12px;
                justify-content: center;
            }

            .status-item {
                padding: 10px 16px;
                min-width: 90px;
            }

            .status-value {
                font-size: 1.6em;
            }

            .map-legend {
                bottom: 10px;
                right: 10px;
                padding: 12px 16px;
                font-size: 0.9em;
            }

            .legend-item {
                font-size: 0.75em;
            }
        }

        @media (max-width: 768px) {
            body {
                overflow-y: auto;
            }

            .container {
                height: auto;
                min-height: 100vh;
                padding: 12px;
                gap: 12px;
            }

            .header {
                display: flex;
                flex-direction: column;
                gap: 16px;
                padding: 16px 20px;
                align-items: center;
            }

            .title {
                font-size: 1.1em;
                text-align: center;
                justify-self: center;
            }

            .subtitle {
                font-size: 0.6em;
            }

            .status {
                flex-direction: row;
                justify-content: center;
                gap: 8px;
                justify-self: center;
            }

            .status-item {
                padding: 12px 8px;
                min-width: 80px;
                flex: 1;
            }

            .status-label {
                font-size: 0.65em;
            }

            .status-value {
                font-size: 1.4em;
            }

            .main-content {
                grid-template-rows: 350px auto;
                gap: 12px;
            }

            .map-container {
                height: 350px;
                touch-action: pan-y; /* Allow vertical scrolling through the map */
            }

            #map {
                touch-action: pan-y; /* Allow vertical scrolling through the map */
            }

            .map-legend {
                display: none; /* Hide on mobile to save space */
            }

            .panel {
                padding: 16px;
            }

            .panel-title {
                font-size: 1em;
            }

            .sidebar {
                gap: 12px;
            }

            /* Larger touch targets for mobile */
            .log-entry {
                padding: 12px;
                margin-bottom: 10px;
            }

            .country-item {
                padding: 12px 0;
            }

            .country-bar {
                height: 24px;
                margin: 0 12px;
            }

            /* Adjust scrollbar for mobile */
            .sidebar::-webkit-scrollbar {
                width: 4px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 8px;
                gap: 8px;
            }

            .header {
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 12px 16px;
                gap: 12px;
            }

            .title {
                font-size: 0.95em;
                text-align: center;
            }

            .subtitle {
                font-size: 0.55em;
            }

            .status {
                gap: 6px;
                justify-content: center;
            }

            .status-item {
                padding: 10px 6px;
                min-width: 70px;
            }

            .status-label {
                font-size: 0.6em;
            }

            .status-value {
                font-size: 1.2em;
            }

            .main-content {
                grid-template-rows: 300px auto;
                gap: 8px;
            }

            .map-container {
                height: 300px;
            }

            .panel {
                padding: 12px;
            }

            .panel-title {
                font-size: 0.95em;
                margin-bottom: 12px;
            }

            .log-entry {
                padding: 10px;
                font-size: 0.8em;
            }

            .country-item {
                padding: 10px 0;
            }

            .country-name {
                min-width: 70px;
                font-size: 0.85em;
            }

            .country-bar {
                margin: 0 10px;
                height: 20px;
            }

            .country-count {
                font-size: 0.85em;
                min-width: 50px;
            }

            .log-count {
                padding: 1px 6px;
                font-size: 0.75em;
            }
        }

        /* Landscape mode on mobile */
        @media (max-width: 896px) and (orientation: landscape) {
            .container {
                height: auto;
                min-height: 100vh;
            }

            .main-content {
                grid-template-columns: 1fr 1fr;
                grid-template-rows: auto;
            }

            .map-container {
                height: 70vh;
            }

            .sidebar {
                max-height: 70vh;
                overflow-y: auto;
            }
        }

        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            .status-item:hover,
            .log-entry:hover {
                transform: none;
            }

            /* Larger tap targets */
            .log-entry {
                min-height: 44px;
            }

            /* Prevent text selection on touch */
            .status-value,
            .stat-value,
            .country-count {
                user-select: none;
                -webkit-user-select: none;
            }
        }
    </style>
</head>
<body>
    <div class="grid-background"></div>

    <div class="container">
        <div class="header">
            <div class="title">
                <a href="/security/" style="color: inherit; text-decoration: none;">Security Threat Intelligence Dashboard</a>
                <div class="subtitle">Last 7 Days</div>
            </div>
            <div class="status">
                <div class="status-item">
                    <div class="status-label">Total Attempts</div>
                    <div class="status-value" id="totalAttempts">0</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Unique IPs</div>
                    <div class="status-value" id="uniqueIps">0</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Banned IPs</div>
                    <div class="status-value" id="bannedIps">0</div>
                </div>
                <div class="status-item">
                    <div class="status-label">Countries</div>
                    <div class="status-value" id="countries">0</div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="map-container">
                <div id="map"></div>
                <div class="map-legend">
                    <div class="legend-title">Threat Severity</div>
                    <div class="legend-item">
                        <div class="legend-circle" style="width: 30px; height: 30px;"></div>
                        <span>High (200+ attempts)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-circle" style="width: 20px; height: 20px;"></div>
                        <span>Medium (50-200 attempts)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-circle" style="width: 12px; height: 12px;"></div>
                        <span>Low (&lt;50 attempts)</span>
                    </div>
                </div>
            </div>

            <div class="sidebar">
                <div class="panel">
                    <div class="panel-title">Countries</div>
                    <div id="topCountries">
                        <div class="loading">Loading threat data...</div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title">Recent</div>
                    <div class="attack-log" id="attackLog">
                        <div class="loading">Analyzing security events...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let markers = [];

        // Initialize map
        function initMap() {
            // Detect mobile device and orientation
            const isMobile = window.innerWidth <= 768;
            const isPortrait = window.innerHeight > window.innerWidth;

            // Use lower zoom for portrait mode to fit all content
            const mobileZoom = (isMobile && isPortrait) ? 0.6 : (isMobile ? 0.8 : 2);

            map = L.map('map', {
                center: [25, 15],  // Adjusted center for better global coverage
                zoom: mobileZoom,
                zoomControl: true,
                minZoom: 0.5,  // Allow zooming out further
                maxZoom: 10,
                tap: true, // Enable tap for mobile
                tapTolerance: 15, // Larger tap tolerance for mobile
                touchZoom: true,
                dragging: !isMobile, // Disable dragging on mobile to allow page scrolling
                scrollWheelZoom: false // Disable scroll zoom to prevent conflicts with page scrolling
            });

            // Light themed tile layer
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);

            // Re-center and adjust zoom when window is resized (orientation changes)
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    const isMobile = window.innerWidth <= 768;
                    const isPortrait = window.innerHeight > window.innerWidth;
                    const mobileZoom = (isMobile && isPortrait) ? 0.6 : (isMobile ? 0.8 : 2);

                    map.invalidateSize();
                    map.setZoom(mobileZoom);
                }, 250);
            });
        }

        // Load attack data with instant cache display
        async function loadData(useCache = true) {
            try {
                // No localStorage cache - always fetch fresh data for real-time updates
                const response = await fetch('get_attacks.php');
                const data = await response.json();

                if (data.error) {
                    console.error(data.error);
                    return;
                }

                updateStats(data);
                updateMap(data.attacks);
                updateTopCountries(data.countryCounts);
                updateAttackLog(data.recentAttacks);
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }

        // Update statistics
        function updateStats(data) {
            document.getElementById('totalAttempts').textContent = data.totalAttempts.toLocaleString();
            document.getElementById('uniqueIps').textContent = data.uniqueIps.toLocaleString();
            document.getElementById('bannedIps').textContent = data.bannedIps.toLocaleString();
            document.getElementById('countries').textContent = data.countryCount;
        }

        // Calculate node size based on attack count
        function getNodeSize(count) {
            if (count >= 200) {
                return 30;
            } else if (count >= 100) {
                return 22;
            } else if (count >= 50) {
                return 16;
            } else if (count >= 20) {
                return 12;
            } else {
                return 8;
            }
        }

        // Get color intensity based on attack count
        function getNodeColor(count) {
            if (count >= 200) {
                return { fill: '#C67B5C', stroke: '#8B4513' };
            } else if (count >= 100) {
                return { fill: '#D89A7B', stroke: '#A0623D' };
            } else if (count >= 50) {
                return { fill: '#E8B89A', stroke: '#B8956A' };
            } else {
                return { fill: '#F5D5B8', stroke: '#D4C5A9' };
            }
        }

        // Update map markers with batched rendering
        function updateMap(attacks) {
            // Clear existing markers
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            // Limit to top 100 attacks for comprehensive 7-day view
            const topAttacks = attacks.slice(0, 100);

            // Use Leaflet's featureGroup for better performance
            const markerGroup = L.featureGroup();

            // Add new markers with variable sizes
            topAttacks.forEach(attack => {
                if (attack.lat && attack.lon) {
                    const size = getNodeSize(attack.count);
                    const colors = getNodeColor(attack.count);

                    const marker = L.circleMarker([attack.lat, attack.lon], {
                        radius: size,
                        fillColor: colors.fill,
                        color: colors.stroke,
                        weight: 2,
                        opacity: 0.9,
                        fillOpacity: 0.7
                    });

                    // Parse the lastSeen timestamp for local conversion
                    const lastSeenLocal = (() => {
                        const currentYear = new Date().getFullYear();
                        const timeWithYear = attack.lastSeen + ' ' + currentYear;
                        const timestamp = new Date(timeWithYear).getTime() / 1000;
                        return formatLocalTime(timestamp);
                    })();

                    const serviceIcon = attack.service === 'wordpress' ? '🔐' : '🖥️';
                    const serviceName = attack.service === 'wordpress' ? 'WordPress' : 'SSH';
                    const statusBadge = attack.blacklisted ? '<span style="background: #dc3545; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.85em; font-weight: bold; display: inline-block; margin-top: 6px;">⛔ BLACKLISTED</span>' :
                                       attack.locked ? '<span style="background: #f56e28; color: white; padding: 2px 8px; border-radius: 3px; font-size: 0.85em; font-weight: bold; display: inline-block; margin-top: 6px;">🔒 LOCKED</span>' : '';

                    marker.bindPopup(`
                        <div style="font-family: 'Inter', sans-serif; min-width: 200px;">
                            <div style="font-size: 1.1em; font-weight: 700; color: #C67B5C; margin-bottom: 8px; font-family: 'Courier New', monospace;">
                                ${attack.ip}
                            </div>
                            <div style="color: #2C2416; margin-bottom: 4px;">
                                <strong>Target:</strong> ${serviceIcon} ${serviceName}
                            </div>
                            <div style="color: #2C2416; margin-bottom: 4px;">
                                <strong>Location:</strong> ${attack.city || 'Unknown'}, ${attack.country || 'Unknown'}
                            </div>
                            <div style="color: #2C2416; margin-bottom: 4px;">
                                <strong>Attempts:</strong> <span style="color: #C67B5C; font-weight: 700;">${attack.count}</span>
                            </div>
                            <div style="color: #5C5040; font-size: 0.9em;">
                                <strong>Last Seen:</strong> ${lastSeenLocal}
                            </div>
                            ${statusBadge}
                        </div>
                    `);

                    // Add pulsing effect for high-threat nodes
                    if (attack.count >= 200) {
                        marker.on('add', function() {
                            const element = this._path;
                            if (element) {
                                element.style.animation = 'pulse 2s infinite';
                            }
                        });
                    }

                    markerGroup.addLayer(marker);
                    markers.push(marker);
                }
            });

            // Add all markers at once for better performance
            markerGroup.addTo(map);
        }

        // Update top countries list
        function updateTopCountries(countryCounts) {
            const container = document.getElementById('topCountries');
            const maxCount = Math.max(...Object.values(countryCounts));

            const sortedCountries = Object.entries(countryCounts)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 3);

            if (sortedCountries.length === 0) {
                container.innerHTML = '<div class="loading">No data available</div>';
                return;
            }

            container.innerHTML = sortedCountries.map(([country, count]) => `
                <div class="country-item">
                    <span class="country-name">${country}</span>
                    <div class="country-bar">
                        <div class="country-bar-fill" style="width: ${(count / maxCount) * 100}%"></div>
                    </div>
                    <span class="country-count">${count.toLocaleString()}</span>
                </div>
            `).join('');
        }

        // Format timestamp to user's local timezone
        function formatLocalTime(timestamp) {
            const date = new Date(timestamp * 1000); // Convert Unix timestamp to milliseconds

            // Format: "Oct 24, 2:14 PM EST"
            const options = {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                timeZoneName: 'short'
            };

            return date.toLocaleString('en-US', options);
        }

        // Update attack log
        function updateAttackLog(recentAttacks) {
            const container = document.getElementById('attackLog');

            if (recentAttacks.length === 0) {
                container.innerHTML = '<div class="loading">No recent attacks</div>';
                return;
            }

            container.innerHTML = recentAttacks.slice(0, 4).map(attack => {
                const localTime = attack.timestamp ? formatLocalTime(attack.timestamp) : attack.time;
                const serviceIcon = attack.service === 'wordpress' ? '🔐' : '🖥️';
                const serviceName = attack.service === 'wordpress' ? 'WordPress' : 'SSH';
                const statusBadge = attack.blacklisted ? ' <span style="background: #dc3545; color: white; padding: 1px 6px; border-radius: 2px; font-size: 0.75em; font-weight: bold;">⛔ BANNED</span>' :
                                   attack.locked ? ' <span style="background: #f56e28; color: white; padding: 1px 6px; border-radius: 2px; font-size: 0.75em; font-weight: bold;">🔒 LOCKED</span>' : '';
                return `
                    <div class="log-entry">
                        <div class="log-time">⏱ ${localTime} ${serviceIcon} ${serviceName}</div>
                        <div>
                            IP: <span class="log-ip">${attack.ip}</span>
                            <span class="log-count">${attack.count}</span>${statusBadge}
                        </div>
                        <div class="log-location">📍 ${attack.location}</div>
                    </div>
                `;
            }).join('');
        }

        // Initialize on load
        window.addEventListener('load', () => {
            initMap();
            loadData();
        });
    </script>
</body>
</html>
