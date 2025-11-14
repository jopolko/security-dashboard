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

        html {
            scroll-behavior: smooth;
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
            overflow-y: auto;
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
            min-height: 100vh;
            display: grid;
            grid-template-rows: auto auto;
            padding: 12px;
            gap: 12px;
            max-width: 1920px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 14px 24px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(250, 247, 240, 0.95) 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 2px 12px var(--shadow), 0 1px 3px rgba(44, 36, 22, 0.06);
            backdrop-filter: blur(10px);
            gap: 20px;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent-primary), transparent);
            border-radius: 12px 12px 0 0;
            opacity: 0.4;
        }

        .title {
            font-size: 1.5em;
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
            gap: 12px;
            font-size: 0.85em;
            grid-column: 2;
            justify-self: center;
        }

        .status-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 8px 16px;
            background: linear-gradient(135deg, rgba(184, 149, 106, 0.1), rgba(139, 115, 85, 0.05));
            border: 1px solid var(--border-color);
            border-radius: 10px;
            min-width: 100px;
            transition: all 0.3s ease;
        }

        .status-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px var(--shadow);
        }

        .status-label {
            color: var(--text-secondary);
            font-size: 0.7em;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .status-value {
            font-size: 1.8em;
            font-weight: 700;
            color: var(--accent-primary);
            line-height: 1;
        }

        /* Main content */
        .main-content {
            display: grid;
            gap: 12px;
        }

        .top-row {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 12px;
            align-items: start;
        }

        .bottom-panels {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 12px;
        }

        /* For wider screens, limit to 3 columns max */
        @media (min-width: 1400px) {
            .bottom-panels {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        /* Map container */
        .map-container {
            position: relative;
            height: 480px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px var(--shadow);
            background: white;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        .map-legend {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(255, 255, 255, 0.98);
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 16px var(--shadow);
            z-index: 1000;
        }

        .legend-title {
            font-size: 0.75em;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 6px;
            font-size: 0.75em;
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
            gap: 12px;
        }

        /* Panel */
        .panel {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 2px 12px var(--shadow), 0 1px 3px rgba(44, 36, 22, 0.06);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            position: relative;
        }

        .panel:hover {
            box-shadow: 0 4px 20px var(--shadow), 0 2px 8px rgba(44, 36, 22, 0.1);
            transform: translateY(-2px);
        }

        .panel-title {
            font-size: 1em;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 6px;
            letter-spacing: -0.3px;
        }

        .panel-title::before {
            content: '';
            width: 3px;
            height: 16px;
            background: linear-gradient(180deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 2px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
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
            font-size: 0.85em;
            font-weight: 500;
        }

        .stat-value {
            color: var(--accent-danger);
            font-weight: 700;
            font-size: 0.9em;
        }

        /* Attack log */
        .attack-log {
            /* Static list - no scrolling, fits exactly 4 entries */
        }

        .log-entry {
            padding: 10px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, rgba(198, 123, 92, 0.08), rgba(184, 149, 106, 0.08));
            border-left: 3px solid var(--accent-danger);
            border-radius: 8px;
            font-size: 0.8em;
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
            font-size: 0.85em;
            font-weight: 500;
            margin-bottom: 3px;
        }

        .log-ip {
            color: var(--accent-danger);
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }

        .log-location {
            color: var(--text-secondary);
            font-size: 0.85em;
            margin-top: 3px;
        }

        .log-count {
            display: inline-block;
            background: var(--accent-danger);
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.75em;
            font-weight: 600;
            margin-left: 6px;
        }

        /* Top countries */
        #topCountries {
            max-height: 280px;
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
            padding: 7px 0;
            border-bottom: 1px solid var(--cream-darker);
        }

        .country-item:last-child {
            border-bottom: none;
        }

        .country-name {
            min-width: 85px;
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.85em;
        }

        .country-bar {
            flex: 1;
            height: 22px;
            background: var(--cream-dark);
            margin: 0 12px;
            border-radius: 11px;
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
            min-width: 55px;
            text-align: right;
            color: var(--accent-danger);
            font-weight: 700;
            font-size: 0.85em;
        }

        .loading {
            text-align: center;
            padding: 30px;
            font-size: 0.9em;
            color: var(--text-secondary);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }

        /* New Visualizations */

        /* Service Breakdown */
        .service-breakdown {
            display: grid;
            gap: 8px;
            margin-top: 8px;
        }

        .service-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid var(--cream-darker);
        }

        .service-item:last-child {
            border-bottom: none;
        }

        .service-name {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 500;
            color: var(--text-primary);
            font-size: 0.85em;
        }

        .service-icon {
            font-size: 1.1em;
        }

        .service-count {
            color: var(--accent-danger);
            font-weight: 700;
            font-size: 0.85em;
        }

        /* Hourly Timeline Bar Chart */
        .hourly-heatmap {
            padding: 16px;
            position: relative;
            min-height: 200px;
        }

        .timeline-container {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(250, 247, 240, 0.9) 100%);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 16px;
            box-shadow:
                0 4px 20px rgba(184, 149, 106, 0.15),
                inset 0 1px 3px rgba(255, 255, 255, 0.8);
        }

        .timeline-title {
            font-size: 0.75em;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            text-align: center;
        }

        .timeline-chart {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            height: 120px;
            margin-bottom: 8px;
            padding: 0 2px;
            background: linear-gradient(
                to right,
                rgba(139, 115, 85, 0.05) 0%,
                rgba(184, 149, 106, 0.05) 25%,
                rgba(198, 123, 92, 0.1) 50%,
                rgba(184, 149, 106, 0.05) 75%,
                rgba(139, 115, 85, 0.05) 100%
            );
            border-radius: 6px;
            position: relative;
        }

        .timeline-bar {
            flex: 1;
            max-width: 12px;
            margin: 0 1px;
            border-radius: 3px 3px 0 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            background: linear-gradient(
                180deg,
                var(--bar-color-top) 0%,
                var(--bar-color-bottom) 100%
            );
            box-shadow:
                0 1px 3px rgba(184, 149, 106, 0.2),
                inset 0 1px 1px rgba(255, 255, 255, 0.3);
        }

        .timeline-bar:hover {
            transform: translateY(-2px) scale(1.1);
            box-shadow:
                0 4px 12px rgba(198, 123, 92, 0.3),
                0 2px 6px rgba(184, 149, 106, 0.2),
                inset 0 1px 2px rgba(255, 255, 255, 0.5);
            z-index: 10;
        }

        .timeline-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                180deg,
                rgba(255, 255, 255, 0.4) 0%,
                transparent 50%
            );
            border-radius: 3px 3px 0 0;
            pointer-events: none;
        }

        .timeline-labels {
            display: flex;
            justify-content: space-between;
            margin-top: 6px;
            padding: 0 2px;
        }

        .timeline-label {
            flex: 1;
            text-align: center;
            font-size: 0.65em;
            font-weight: 600;
            color: var(--text-secondary);
            opacity: 0.8;
            max-width: 12px;
            margin: 0 1px;
        }

        .timeline-label.major {
            opacity: 1;
            color: var(--text-primary);
            font-weight: 700;
        }

        .timeline-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid var(--cream-darker);
        }

        .timeline-stat {
            text-align: center;
            font-size: 0.7em;
        }

        .timeline-stat-label {
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 2px;
        }

        .timeline-stat-value {
            font-weight: 700;
            color: var(--accent-primary);
            font-size: 1.1em;
        }

        .timeline-tooltip {
            position: absolute;
            background: rgba(44, 36, 22, 0.95);
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.75em;
            font-weight: 600;
            pointer-events: none;
            z-index: 20;
            backdrop-filter: blur(4px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
            opacity: 0;
            transition: opacity 0.2s ease;
            white-space: nowrap;
        }

        .timeline-tooltip.show {
            opacity: 1;
        }

        /* Top Usernames */
        .top-usernames {
            max-height: 240px;
            overflow-y: auto;
        }

        .username-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            border-bottom: 1px solid var(--cream-darker);
        }

        .username-item:last-child {
            border-bottom: none;
        }

        .username-name {
            font-family: 'Courier New', monospace;
            color: #1a1410;
            font-weight: 700;
            font-size: 0.85em;
        }

        .username-count {
            color: var(--accent-danger);
            font-weight: 700;
            font-size: 0.85em;
            min-width: 45px;
            text-align: right;
        }

        /* Top Attackers Table */
        .top-attackers {
            max-height: 320px;
            overflow-y: auto;
        }

        .attacker-item {
            padding: 10px;
            margin-bottom: 8px;
            background: linear-gradient(135deg, rgba(198, 123, 92, 0.08), rgba(184, 149, 106, 0.08));
            border-left: 3px solid var(--accent-danger);
            border-radius: 8px;
            font-size: 0.8em;
            transition: all 0.2s ease;
        }

        .attacker-item:hover {
            transform: translateX(4px);
            box-shadow: 0 2px 8px var(--shadow);
        }

        .attacker-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .attacker-ip {
            font-family: 'Courier New', monospace;
            color: var(--accent-danger);
            font-weight: 700;
            font-size: 0.95em;
        }

        .attacker-count {
            background: var(--accent-danger);
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .attacker-details {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            font-size: 0.85em;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .attacker-detail {
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .persistence-badge {
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            color: white;
            padding: 2px 5px;
            border-radius: 6px;
            font-size: 0.75em;
            font-weight: 600;
        }

        /* Threat Level Colors */
        .threat-low {
            color: #4CAF50 !important;
        }

        .threat-medium {
            color: #FF9800 !important;
        }

        .threat-high {
            color: #FF5722 !important;
        }

        .threat-critical {
            color: #D32F2F !important;
            animation: pulse-glow 2s infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Scrollbar styles for new panels */
        .top-usernames::-webkit-scrollbar,
        .top-attackers::-webkit-scrollbar {
            width: 6px;
        }

        .top-usernames::-webkit-scrollbar-track,
        .top-attackers::-webkit-scrollbar-track {
            background: var(--cream-dark);
            border-radius: 3px;
        }

        .top-usernames::-webkit-scrollbar-thumb,
        .top-attackers::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 3px;
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
                padding: 10px;
                gap: 10px;
            }

            .top-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .bottom-panels {
                grid-template-columns: 1fr;
            }

            .header {
                display: flex;
                flex-direction: column;
                padding: 12px 18px;
                align-items: center;
                gap: 12px;
            }

            .title {
                font-size: 1.2em;
                text-align: center;
            }

            .subtitle {
                font-size: 0.6em;
            }

            .status {
                gap: 8px;
                justify-content: center;
            }

            .status-item {
                padding: 8px 12px;
                min-width: 80px;
            }

            .status-value {
                font-size: 1.5em;
            }

            .map-container {
                height: 360px;
            }

            .map-legend {
                bottom: 8px;
                right: 8px;
                padding: 8px 12px;
                font-size: 0.85em;
            }

            .legend-item {
                font-size: 0.7em;
            }
        }

        @media (max-width: 768px) {
            body {
                overflow-y: auto;
            }

            .container {
                height: auto;
                min-height: 100vh;
                padding: 8px;
                gap: 8px;
            }

            .header {
                display: flex;
                flex-direction: column;
                gap: 10px;
                padding: 12px 16px;
                align-items: center;
            }

            .title {
                font-size: 1.05em;
                text-align: center;
                justify-self: center;
            }

            .subtitle {
                font-size: 0.55em;
            }

            .status {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 6px;
                width: 100%;
                justify-self: center;
            }

            .status-item {
                padding: 8px 4px;
                min-width: auto;
            }

            .status-label {
                font-size: 0.55em;
            }

            .status-value {
                font-size: 1.1em;
            }

            /* Make timeline chart more compact on mobile */
            .hourly-heatmap {
                padding: 12px;
                min-height: 180px;
            }

            .timeline-container {
                padding: 12px;
            }

            .timeline-chart {
                height: 80px;
            }

            .timeline-bar {
                max-width: 8px;
            }

            .timeline-label {
                font-size: 0.6em;
                max-width: 8px;
            }

            .timeline-stats {
                margin-top: 8px;
                padding-top: 6px;
            }

            .timeline-stat {
                font-size: 0.65em;
            }

            /* Adjust new panels for mobile */
            .attacker-item {
                font-size: 0.75em;
                padding: 8px;
            }

            .username-item,
            .service-item {
                font-size: 0.8em;
            }

            .top-row {
                grid-template-columns: 1fr;
            }

            .bottom-panels {
                grid-template-columns: 1fr;
            }

            .map-container {
                height: 320px;
                touch-action: pan-y; /* Allow vertical scrolling through the map */
                pointer-events: none; /* Completely disable touch interactions on mobile */
            }

            #map {
                touch-action: pan-y; /* Allow vertical scrolling through the map */
                pointer-events: none; /* Disable all map interactions on mobile */
            }

            /* Re-enable pointer events on markers so they can still be tapped */
            .leaflet-marker-icon,
            .leaflet-popup {
                pointer-events: auto !important;
            }

            .map-legend {
                display: none; /* Hide on mobile to save space */
            }

            .panel {
                padding: 12px;
            }

            .panel-title {
                font-size: 0.95em;
            }

            .sidebar {
                gap: 8px;
            }

            /* Larger touch targets for mobile */
            .log-entry {
                padding: 10px;
                margin-bottom: 8px;
            }

            .country-item {
                padding: 8px 0;
            }

            .country-bar {
                height: 20px;
                margin: 0 10px;
            }

            /* Adjust scrollbar for mobile */
            .sidebar::-webkit-scrollbar {
                width: 4px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 6px;
                gap: 6px;
            }

            .header {
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 10px 14px;
                gap: 8px;
            }

            .title {
                font-size: 0.9em;
                text-align: center;
            }

            .subtitle {
                font-size: 0.5em;
            }

            .status {
                gap: 4px;
                justify-content: center;
            }

            .status-item {
                padding: 6px 4px;
                min-width: 65px;
            }

            .status-label {
                font-size: 0.55em;
            }

            .status-value {
                font-size: 1.1em;
            }

            .top-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .bottom-panels {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .map-container {
                height: 280px;
            }

            .panel {
                padding: 10px;
            }

            .panel-title {
                font-size: 0.9em;
                margin-bottom: 10px;
            }

            .log-entry {
                padding: 8px;
                font-size: 0.75em;
            }

            .country-item {
                padding: 6px 0;
            }

            .country-name {
                min-width: 65px;
                font-size: 0.8em;
            }

            .country-bar {
                margin: 0 8px;
                height: 18px;
            }

            .country-count {
                font-size: 0.8em;
                min-width: 45px;
            }

            .log-count {
                padding: 1px 5px;
                font-size: 0.7em;
            }
        }

        /* Landscape mode on mobile */
        @media (max-width: 896px) and (orientation: landscape) {
            body {
                overflow-y: auto !important;
            }

            .container {
                height: auto;
                min-height: 100vh;
                padding: 8px;
                gap: 8px;
            }

            .top-row {
                grid-template-columns: 1fr;
            }

            .bottom-panels {
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }

            .map-container {
                height: 65vh;
                pointer-events: none;
            }

            .map-legend {
                display: none;
            }

            #map {
                pointer-events: none;
            }

            /* Re-enable pointer events on markers so they can still be tapped */
            .leaflet-marker-icon,
            .leaflet-popup {
                pointer-events: auto !important;
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
                    <div class="status-label">Velocity</div>
                    <div class="status-value" id="attackVelocity">0</div>
                    <div class="status-sublabel" style="font-size: 0.65em; opacity: 0.7; margin-top: 2px;">per hour</div>
                </div>
                <div class="status-item" id="threatLevelCard">
                    <div class="status-label">Threat Level</div>
                    <div class="status-value" id="threatLevel">Low</div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <!-- Top Row: Map + Quick Stats -->
            <div class="top-row">
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

            <!-- Bottom Panels: Multi-column grid -->
            <div class="bottom-panels">
                <div class="panel">
                    <div class="panel-title">Defense Status</div>
                    <div id="defenseStatus">
                        <div class="stat-item">
                            <span class="stat-label">Ban Effectiveness</span>
                            <span class="stat-value" id="banEffectiveness">0%</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">SSH Attempts</span>
                            <span class="stat-value" id="sshAttempts">0</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">WordPress Attempts</span>
                            <span class="stat-value" id="wpAttempts">0</span>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title">Attack Heatmap</div>
                    <div id="hourlyHeatmap" class="hourly-heatmap"></div>
                </div>

                <div class="panel">
                    <div class="panel-title">Top Targeted Usernames</div>
                    <div id="topUsernames" class="top-usernames">
                        <div class="loading">Loading username data...</div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-title">Most Aggressive Attackers</div>
                    <div id="topAttackers" class="top-attackers">
                        <div class="loading">Analyzing threat actors...</div>
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
            const isLandscape = window.innerWidth > window.innerHeight;
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
                touchZoom: !isMobile, // Disable touch zoom on mobile to prevent map interaction
                dragging: !isMobile, // Disable dragging on mobile to allow page scrolling
                scrollWheelZoom: !isMobile, // Disable scroll zoom on mobile
                doubleClickZoom: !isMobile // Disable double-click zoom on mobile
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
                updateHourlyHeatmap(data.hourStats);
                updateTopUsernames(data.topUsernames);
                updateTopAttackers(data.topAttackers);
                updateDefenseStatus(data);
            } catch (error) {
                console.error('Error loading data:', error);
            }
        }

        // Update statistics
        function updateStats(data) {
            document.getElementById('totalAttempts').textContent = data.totalAttempts.toLocaleString();
            document.getElementById('uniqueIps').textContent = data.uniqueIps.toLocaleString();
            document.getElementById('bannedIps').textContent = data.bannedIps.toLocaleString();
            document.getElementById('attackVelocity').textContent = data.attackVelocity || '0';

            // Update threat level with color coding
            const threatLevel = data.threatLevel || 'Low';
            const threatElement = document.getElementById('threatLevel');
            const threatCard = document.getElementById('threatLevelCard');
            threatElement.textContent = threatLevel;

            // Remove all threat classes
            threatElement.classList.remove('threat-low', 'threat-medium', 'threat-high', 'threat-critical');

            // Add appropriate class
            if (threatLevel === 'Critical') {
                threatElement.classList.add('threat-critical');
            } else if (threatLevel === 'High') {
                threatElement.classList.add('threat-high');
            } else if (threatLevel === 'Medium') {
                threatElement.classList.add('threat-medium');
            } else {
                threatElement.classList.add('threat-low');
            }
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

            container.innerHTML = recentAttacks.slice(0, 3).map(attack => {
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


        // Update hourly timeline bar chart
        function updateHourlyHeatmap(hourStats) {
            const container = document.getElementById('hourlyHeatmap');

            if (!hourStats || hourStats.length === 0) {
                container.innerHTML = '<div class="loading">No hourly data</div>';
                return;
            }

            const maxCount = Math.max(...hourStats.map(h => h.count));
            const totalAttacks = hourStats.reduce((sum, h) => sum + h.count, 0);
            const avgAttacks = Math.round(totalAttacks / (7 * 24)); // 7-day average

            // Find peak hour
            const peakHour = hourStats.reduce((max, current) =>
                current.count > max.count ? current : max
            );

            // Create timeline container
            let timelineHTML = `
                <div class="timeline-container">
                    <div class="timeline-title">Attack Distribution by Hour</div>
                    <div class="timeline-chart">
            `;

            // Create bars for each hour
            hourStats.forEach(hourData => {
                const height = maxCount > 0 ? (hourData.count / maxCount) * 100 : 0;
                const intensity = maxCount > 0 ? hourData.count / maxCount : 0;

                // Generate color based on intensity
                let topColor, bottomColor;
                if (intensity === 0) {
                    topColor = '#FAF7F0';
                    bottomColor = '#E8DCC4';
                } else if (intensity < 0.25) {
                    topColor = '#F5D5B8';
                    bottomColor = '#E8DCC4';
                } else if (intensity < 0.5) {
                    topColor = '#E8B89A';
                    bottomColor = '#D89A7B';
                } else if (intensity < 0.75) {
                    topColor = '#D89A7B';
                    bottomColor = '#C67B5C';
                } else {
                    topColor = '#C67B5C';
                    bottomColor = '#B8664A';
                }

                timelineHTML += `
                    <div class="timeline-bar"
                         style="
                             height: ${height}%;
                             --bar-color-top: ${topColor};
                             --bar-color-bottom: ${bottomColor};
                         "
                         data-hour="${hourData.hour}"
                         data-count="${hourData.count}"
                         onmouseover="showTimelineTooltip(event, ${hourData.hour}, ${hourData.count})"
                         onmouseout="hideTimelineTooltip()">
                    </div>
                `;
            });

            timelineHTML += '</div>';

            // Add hour labels
            timelineHTML += '<div class="timeline-labels">';
            hourStats.forEach(hourData => {
                const isMajor = hourData.hour % 6 === 0; // Highlight every 6 hours
                const displayHour = hourData.hour === 0 ? '12a' :
                                   hourData.hour < 12 ? hourData.hour + 'a' :
                                   hourData.hour === 12 ? '12p' :
                                   (hourData.hour - 12) + 'p';

                timelineHTML += `<div class="timeline-label ${isMajor ? 'major' : ''}">${displayHour}</div>`;
            });
            timelineHTML += '</div>';

            // Add stats
            timelineHTML += `
                <div class="timeline-stats">
                    <div class="timeline-stat">
                        <div class="timeline-stat-label">Peak Hour</div>
                        <div class="timeline-stat-value">${peakHour.hour}:00</div>
                    </div>
                    <div class="timeline-stat">
                        <div class="timeline-stat-label">Peak Attacks</div>
                        <div class="timeline-stat-value">${peakHour.count}</div>
                    </div>
                </div>
            `;

            timelineHTML += '</div>';

            // Add tooltip
            timelineHTML += '<div class="timeline-tooltip" id="timelineTooltip"></div>';

            container.innerHTML = timelineHTML;
        }

        // Tooltip functions for timeline chart
        function showTimelineTooltip(event, hour, count) {
            const tooltip = document.getElementById('timelineTooltip');
            if (!tooltip) return;

            const displayHour = hour === 0 ? '12:00 AM' :
                               hour < 12 ? `${hour}:00 AM` :
                               hour === 12 ? '12:00 PM' :
                               `${hour - 12}:00 PM`;

            tooltip.innerHTML = `<strong>${displayHour}</strong><br>${count} attacks`;

            const rect = event.target.getBoundingClientRect();
            const container = document.getElementById('hourlyHeatmap').getBoundingClientRect();

            tooltip.style.left = (rect.left - container.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
            tooltip.style.top = (rect.top - container.top - tooltip.offsetHeight - 8) + 'px';
            tooltip.classList.add('show');
        }

        function hideTimelineTooltip() {
            const tooltip = document.getElementById('timelineTooltip');
            if (tooltip) {
                tooltip.classList.remove('show');
            }
        }

        // Get heatmap color based on intensity
        function getHeatmapColor(intensity) {
            if (intensity === 0) return '#FAF7F0';
            if (intensity < 0.25) return '#F5D5B8';
            if (intensity < 0.5) return '#E8B89A';
            if (intensity < 0.75) return '#D89A7B';
            return '#C67B5C';
        }

        // Update top usernames
        function updateTopUsernames(topUsernames) {
            const container = document.getElementById('topUsernames');

            if (!topUsernames || Object.keys(topUsernames).length === 0) {
                container.innerHTML = '<div class="loading">No username data</div>';
                return;
            }

            container.innerHTML = Object.entries(topUsernames).map(([username, count]) => `
                <div class="username-item">
                    <div class="username-name">${username}</div>
                    <div class="username-count">${count}</div>
                </div>
            `).join('');
        }

        // Update top attackers
        function updateTopAttackers(topAttackers) {
            const container = document.getElementById('topAttackers');

            if (!topAttackers || topAttackers.length === 0) {
                container.innerHTML = '<div class="loading">No attacker data</div>';
                return;
            }

            container.innerHTML = topAttackers.map((attacker, index) => {
                const serviceIcon = attacker.service === 'wordpress' ? '🔐' : '🖥️';
                const serviceName = attacker.service === 'wordpress' ? 'WP' : 'SSH';
                const statusBadge = attacker.blacklisted ? '⛔ BANNED' :
                                   attacker.locked ? '🔒 LOCKED' : '';

                return `
                    <div class="attacker-item">
                        <div class="attacker-header">
                            <div class="attacker-ip">#${index + 1} ${attacker.ip}</div>
                            <div class="attacker-count">${attacker.count}</div>
                        </div>
                        <div class="attacker-details">
                            <div class="attacker-detail">📍 ${attacker.city}, ${attacker.countryCode}</div>
                            <div class="attacker-detail">${serviceIcon} ${serviceName}</div>
                            <div class="attacker-detail">
                                <span class="persistence-badge">⏱ ${attacker.persistenceDuration || 'Unknown'}</span>
                            </div>
                            ${statusBadge ? `<div class="attacker-detail" style="color: var(--accent-danger); font-weight: 700;">${statusBadge}</div>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Update defense status
        function updateDefenseStatus(data) {
            document.getElementById('banEffectiveness').textContent = (data.banEffectiveness || 0) + '%';
            document.getElementById('sshAttempts').textContent = (data.sshAttempts || 0).toLocaleString();
            document.getElementById('wpAttempts').textContent = (data.wpAttempts || 0).toLocaleString();
        }

        // Initialize on load
        window.addEventListener('load', () => {
            initMap();
            loadData();
        });
    </script>
</body>
</html>
