# Intrusion Detection Matrix Dashboard

A modern, cyberpunk-styled dashboard for visualizing SSH login attempts and security threats in real-time.

## Features

- 🗺️ **Global Attack Map** - Visualize attack origins on an interactive world map with variable-sized nodes
- 📊 **Real-time Statistics** - Track total attempts, unique IPs, and country distribution
- 🎯 **Top Attackers** - See which countries are launching the most attacks
- 📝 **Attack Log** - Monitor recent login attempts with timestamps and locations
- 🎨 **Professional Design** - Clean cream palette with modern UI elements
- ⚡ **Auto-refresh** - Updates every 30 seconds automatically
- 📱 **Mobile Responsive** - Optimized for all screen sizes

## Setup

### 1. Grant Log Access

The web server needs permission to read the auth.log file:

```bash
# Option 1: Add www-data to adm group (recommended)
sudo usermod -a -G adm www-data

# Option 2: Create a readable copy with a cron job
sudo cp /var/log/auth.log /var/www/html/sec/auth.log
sudo chmod 644 /var/www/html/sec/auth.log

# Add to crontab for automatic updates:
*/5 * * * * cp /var/log/auth.log /var/www/html/sec/auth.log && chmod 644 /var/www/html/sec/auth.log
```

### 2. Create Cache Directory

```bash
sudo mkdir -p /tmp/geo_cache
sudo chmod 777 /tmp/geo_cache
sudo chmod 777 /tmp
```

### 3. Access the Dashboard

Open your browser and navigate to:
```
http://your-server-ip/sec/
```

## How It Works

### Backend (`get_attacks.php`)

- Parses `/var/log/auth.log` for failed SSH login attempts
- Extracts IP addresses and timestamps
- Uses ip-api.com for free IP geolocation (no API key required)
- Caches results for 5 minutes to improve performance
- Implements rate limiting to respect API limits (45 req/min)

### Frontend (`index.php`)

- Interactive Leaflet.js map with dark theme
- Real-time statistics display
- Animated UI elements with cyberpunk aesthetic
- Auto-refresh every 30 seconds
- Responsive design

## Attack Patterns Detected

The dashboard detects the following authentication failure patterns:

- Failed password attempts
- Invalid user login attempts
- Connection resets
- Protocol violations
- Disconnected authentication sessions

## Performance

- **Caching**: Geolocation data cached for 24 hours
- **Attack data**: Cached for 5 minutes
- **Rate limiting**: Built-in delays to respect free API limits
- **Optimization**: Processes top 100 attacking IPs by frequency

## Privacy & Security

- Only monitors **failed** login attempts (security events)
- No successful login tracking
- All data stays on your server
- Uses free, public IP geolocation service
- Local caching minimizes external API calls

## Customization

### Update Colors

Edit the CSS variables in `index.php`:
- Primary: `#00ff41` (Matrix green)
- Secondary: `#00d4ff` (Cyan)
- Accent: `#ff00ff` (Magenta)

### Change Refresh Rate

Modify the interval in JavaScript (default: 30 seconds):
```javascript
setInterval(loadData, 30000); // Change 30000 to desired milliseconds
```

### Adjust Cache Duration

Edit `get_attacks.php`:
```php
$cacheTime = 300; // Change from 5 minutes to desired seconds
```

## Troubleshooting

### No Data Showing

1. Check if auth.log is readable:
   ```bash
   sudo ls -la /var/log/auth.log
   ```

2. Verify PHP can access the file:
   ```bash
   sudo -u www-data cat /var/log/auth.log
   ```

3. Check PHP error log:
   ```bash
   sudo tail -f /var/log/apache2/error.log
   # or
   sudo tail -f /var/log/nginx/error.log
   ```

### Map Not Loading

- Ensure internet connection (requires Leaflet.js and map tiles)
- Check browser console for JavaScript errors
- Verify CDN resources are accessible

### Geolocation Failing

- Check rate limits (45 requests/minute for ip-api.com)
- Verify internet connectivity
- Check `/tmp/geo_cache/` permissions

## Mobile Optimization

The dashboard is fully responsive and optimized for mobile devices:

### Responsive Breakpoints

- **Desktop** (>1024px): Full side-by-side layout with legend
- **Tablet** (768px-1024px): Stacked layout with full-height map
- **Mobile** (480px-768px): Compact vertical layout, hidden legend
- **Small Mobile** (<480px): Ultra-compact with reduced text sizes
- **Landscape Mode**: Split view with map and sidebar side-by-side

### Mobile Features

✅ **Touch-Optimized**
- Larger tap targets (minimum 44px for accessibility)
- Touch-friendly map interactions
- Disabled scroll-wheel zoom (prevents accidental zooming)
- Larger tap tolerance (15px) for easier marker selection

✅ **Adaptive Layout**
- Header stacks vertically on mobile
- Statistics display in horizontal row
- Full-width refresh button
- Map resizes to 300-350px height on mobile
- Panels and text scale appropriately

✅ **Performance**
- Map auto-adjusts zoom level for mobile (zoom level 1 vs 2)
- Thinner scrollbars (4px) on mobile
- Legend hidden on small screens to save space
- Disabled hover effects on touch devices

✅ **Visual Feedback**
- Loading spinner on refresh button
- Button opacity changes during data load
- Smooth animations and transitions

### Testing on Mobile

1. **Open on mobile browser**: http://your-server-ip/sec/
2. **Portrait mode**: Vertical scrolling layout
3. **Landscape mode**: Side-by-side view
4. **Pinch to zoom**: Works on map
5. **Tap markers**: View attack details

## Browser Compatibility

### Desktop
- Chrome/Edge 90+
- Firefox 88+
- Safari 14+

### Mobile
- iOS Safari 14+
- Chrome Mobile 90+
- Samsung Internet 14+
- Firefox Mobile 88+

## License

Free to use for security monitoring and educational purposes.

## Credits

- Map: Leaflet.js with CARTO Dark Matter tiles
- Geolocation: ip-api.com (free tier)
- Design: Cyberpunk/Matrix inspired aesthetic
