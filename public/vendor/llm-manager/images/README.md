# System Notifications - Icon Files

This directory contains icon images used for system (OS-level) notifications.

## Required Files

The following icon files are expected by the system:

1. **logo.png**
   - Main notification icon
   - **Recommended Size:** 192x192 pixels
   - **Format:** PNG with transparency
   - **Purpose:** Displayed as the main icon in the notification
   - Used in: `Notification()` constructor as `icon` parameter

2. **badge.png**
   - Notification badge icon
   - **Recommended Size:** 96x96 pixels
   - **Format:** PNG with transparency
   - **Purpose:** Smaller badge overlay on the notification
   - Used in: `Notification()` constructor as `badge` parameter

## File Requirements

### logo.png
- Minimum: 96x96px (recommended 192x192px for high-DPI displays)
- Maximum: 512x512px
- Format: PNG-24 with alpha channel (transparency supported)
- Background: Transparent or solid color
- Design: Simple, recognizable icon representing LLM Manager
- Example: AI brain icon, chat bubble with sparkles, etc.

### badge.png
- Minimum: 72x72px (recommended 96x96px)
- Maximum: 256x256px
- Format: PNG-24 with alpha channel
- Background: Transparent
- Design: Simplified version of logo or distinct badge
- Example: Small checkmark, star, or notification bell

## Browser Support

Different browsers handle notification icons differently:

- **Chrome/Edge:** Displays both `icon` and `badge`
- **Firefox:** Displays `icon` only
- **Safari:** May display icon depending on macOS notification settings
- **Mobile Browsers:** Limited or no support for custom icons

## Design Guidelines

1. **High Contrast:** Ensure icon is visible on both light and dark backgrounds
2. **Simple Design:** Avoid complex details (icons are displayed small)
3. **Brand Consistency:** Use colors/style consistent with your app
4. **Transparency:** Use PNG with alpha channel for clean edges
5. **Center Focus:** Main subject should be centered in the canvas

## Creating Icons

You can create icons using:

- **Figma/Sketch:** Vector design tools (export as PNG)
- **GIMP/Photoshop:** Raster image editors
- **Online Generators:**
  - Favicon Generator: https://favicon.io/
  - Icon Generator: https://www.iconsgenerator.com/
  - RealFaviconGenerator: https://realfavicongenerator.net/

## Free Icon Resources

Find free icons at:

- **Flaticon:** https://www.flaticon.com/search?word=ai+assistant
- **Icons8:** https://icons8.com/icons/set/artificial-intelligence
- **Font Awesome:** https://fontawesome.com/ (convert to PNG)
- **Material Icons:** https://fonts.google.com/icons

## Testing Icons

After adding icon files:

1. Go to Chat → Settings → UX Enhancements
2. Enable "System Notifications"
3. Click "Request Notification Permission" (grant permission)
4. Send a message and switch to another tab/app
5. Verify the notification appears with your custom icon

## Current Status

⚠️ **Icon files not included** - This directory is a placeholder.

You must add `logo.png` and `badge.png` manually. Until then:
- Notifications will still work
- Icons may fall back to browser defaults (site favicon or generic icon)
- No errors will occur (graceful degradation)

## Implementation Details

- Icons are referenced by absolute path: `/vendor/llm-manager/images/{filename}`
- If files are missing, browsers will handle gracefully (no console errors)
- Icons are only used when system notifications are enabled and permitted
- Path structure follows Laravel public directory conventions

## Example Paths

```javascript
// In Notification API call
new Notification('LLM Manager', {
    body: 'Your response is ready!',
    icon: '/vendor/llm-manager/images/logo.png',     // 192x192px
    badge: '/vendor/llm-manager/images/badge.png',   // 96x96px
    tag: 'llm-response-123'
});
```
