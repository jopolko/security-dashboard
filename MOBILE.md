# Mobile Optimization Guide

## Overview

The Security Threat Intelligence Dashboard is fully optimized for mobile devices with responsive design, touch-friendly interactions, and performance enhancements.

## Responsive Design Breakpoints

### Desktop (>1024px)
```
┌─────────────────────────────────────┐
│  Header with Stats & Refresh        │
├──────────────────┬──────────────────┤
│                  │                  │
│   Global Map     │   Top Countries  │
│   (with legend)  │                  │
│                  │   Recent Attacks │
│                  │                  │
└──────────────────┴──────────────────┘
```

### Tablet (768px-1024px)
```
┌─────────────────────────────────────┐
│  Header (stacked)                   │
│  Stats | Stats | Stats              │
│  [Refresh Button - Full Width]      │
├─────────────────────────────────────┤
│                                     │
│       Global Map (400px)            │
│                                     │
├─────────────────────────────────────┤
│       Top Countries                 │
├─────────────────────────────────────┤
│       Recent Attacks                │
└─────────────────────────────────────┘
```

### Mobile Portrait (480px-768px)
```
┌─────────────────┐
│ Header          │
│ Stats Row       │
│ [Refresh]       │
├─────────────────┤
│                 │
│  Map (350px)    │
│                 │
├─────────────────┤
│ Top Countries   │
├─────────────────┤
│ Recent Attacks  │
└─────────────────┘
```

### Small Mobile (<480px)
```
┌──────────────┐
│ Compact Hdr  │
│ Stats        │
│ [Refresh]    │
├──────────────┤
│   Map 300px  │
├──────────────┤
│ Countries    │
├──────────────┤
│ Attacks      │
└──────────────┘
```

### Landscape Mode
```
┌────────────────┬──────────────┐
│ Header & Stats │              │
├────────────────┤              │
│                │  Countries   │
│   Map (70vh)   │  &           │
│                │  Attacks     │
│                │              │
└────────────────┴──────────────┘
```

## Touch Optimizations

### Tap Targets
- **Minimum size**: 44px x 44px (iOS/Android accessibility standards)
- **Refresh button**: Full width on mobile, min 48px height
- **Log entries**: Min 44px height for easy tapping
- **Map markers**: 15px tap tolerance (larger hit area)

### Touch Gestures
- ✅ Tap to select markers
- ✅ Pinch to zoom on map
- ✅ Pan/drag to move map
- ✅ Scroll panels vertically
- ❌ No scroll-wheel zoom (prevents accidental zooming)

### Disabled Hover Effects
On touch devices (detected via `@media (hover: none)`):
- Removed transform animations on hover
- Disabled box-shadow changes
- Prevents "sticky" hover states

## Visual Adaptations

### Font Sizes
| Element | Desktop | Tablet | Mobile | Small Mobile |
|---------|---------|--------|--------|--------------|
| Title | 1.75em | 1.3em | 1.1em | 0.95em |
| Status Values | 2em | 1.6em | 1.4em | 1.2em |
| Status Labels | 0.75em | 0.75em | 0.65em | 0.6em |
| Panel Text | 1em | 1em | 1em | 0.95em |
| Log Text | 0.85em | 0.85em | 0.85em | 0.8em |

### Spacing
| Element | Desktop | Tablet | Mobile | Small Mobile |
|---------|---------|--------|--------|--------------|
| Container Padding | 24px | 16px | 12px | 8px |
| Panel Padding | 24px | 24px | 16px | 12px |
| Gap Between Elements | 24px | 16px | 12px | 8px |

### Map Configuration
```javascript
Desktop: zoom: 2, scroll-wheel: enabled
Mobile:  zoom: 1, scroll-wheel: disabled, tap: enabled
```

## Performance Features

### Efficient Rendering
- Map invalidates size on window resize (debounced 250ms)
- Markers cleared before re-rendering
- Smooth CSS transitions with GPU acceleration

### Reduced Visual Complexity
- Legend hidden on mobile (<768px) to save space
- Thinner scrollbars (4px vs 6px)
- Reduced shadow effects on small screens

### Loading States
```javascript
Button disabled during fetch
→ Opacity: 0.6
→ Icon spinning animation
→ Prevents multiple requests
```

## Accessibility

### Touch Device Detection
```css
@media (hover: none) and (pointer: coarse) {
    /* Touch-specific styles */
}
```

### Text Selection
- Disabled on numeric values (prevents accidental selection)
- Enabled for IP addresses and location names

### Visual Feedback
- Button press states
- Loading animations
- Smooth transitions
- High contrast text

## Testing Checklist

### Portrait Mode
- [ ] Header stacks vertically
- [ ] Stats display in row
- [ ] Refresh button full width
- [ ] Map displays at 300-350px
- [ ] Panels scroll independently
- [ ] Tap markers work correctly

### Landscape Mode
- [ ] Map and sidebar side-by-side
- [ ] Map fills 70% viewport height
- [ ] Sidebar scrolls if needed
- [ ] Touch interactions smooth

### Interactions
- [ ] Pinch zoom works on map
- [ ] Tap tolerance appropriate
- [ ] No accidental scroll-zoom
- [ ] Refresh button shows loading
- [ ] Markers clickable/tappable

### Performance
- [ ] Loads under 3 seconds on 4G
- [ ] Smooth scrolling
- [ ] No layout shifts
- [ ] Animations run at 60fps

## Browser Testing

### iOS
- Safari 14+ (iPhone, iPad)
- Chrome iOS 90+
- Firefox iOS 88+

### Android
- Chrome 90+
- Samsung Internet 14+
- Firefox 88+
- Edge 90+

## Known Mobile Limitations

1. **Map Legend**: Hidden on screens <768px to conserve space
2. **Auto-refresh**: 30-second interval may drain battery (consider increasing)
3. **Geolocation Cache**: Uses local storage (check browser limits)
4. **Large Datasets**: May slow on very old devices (recommend pagination)

## Future Mobile Enhancements

- [ ] Pull-to-refresh gesture
- [ ] Offline mode with service workers
- [ ] Progressive Web App (PWA) support
- [ ] Dark mode auto-detection
- [ ] Haptic feedback on tap
- [ ] Share functionality
- [ ] Export data feature
- [ ] Notification support

## Debugging Mobile Issues

### Chrome DevTools
```
1. Open Developer Tools (F12)
2. Click "Toggle Device Toolbar" (Ctrl+Shift+M)
3. Select device: iPhone 12, Galaxy S20, etc.
4. Test responsive breakpoints
```

### Real Device Testing
```bash
# Access from mobile device on same network
http://your-server-ip/sec/

# Check console for errors
# Test in portrait and landscape
# Verify touch interactions
```

### Common Issues

**Map not loading?**
- Check Leaflet.js CDN access
- Verify map tiles load from CARTO
- Check console for CORS errors

**Touch not working?**
- Verify `tap: true` in map config
- Check tap tolerance setting
- Test on different browsers

**Layout breaks?**
- Clear browser cache
- Check viewport meta tag
- Verify CSS media queries

## Credits

Responsive design follows:
- iOS Human Interface Guidelines
- Material Design (Google)
- Web Content Accessibility Guidelines (WCAG)
- Progressive Web App standards
