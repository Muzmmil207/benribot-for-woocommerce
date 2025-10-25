# BenriBot WooCommerce Plugin Implementation Guide

## Overview

This document describes the complete implementation of the BenriBot WooCommerce plugin with a modern React-based admin interface.

## Architecture

### Frontend (React + WordPress Scripts)

The plugin uses React 19 with @wordpress/element for building the admin interface:

- **AdminApp.jsx**: Main component managing connection state and API calls
- **ConnectCard.jsx**: UI component displaying the connection card with toggle
- **style.css**: Custom styles following BenriBot brand guidelines

### Backend (PHP + WordPress REST API)

The plugin exposes three REST API endpoints:

1. `GET /wp-json/benribot/v1/status` - Check connection status
2. `POST /wp-json/benribot/v1/connect` - Generate consumer keys and redirect URL
3. `POST /wp-json/benribot/v1/widget-toggle` - Toggle widget embedding

## Key Features Implemented

### 1. One-Click Connection Flow

When user clicks "Connect Account":

1. Plugin generates WooCommerce consumer keys programmatically
2. Creates a secure signature using SHA256 hashing
3. Redirects to: `https://app.benribot.com/onboarding/woocommerce` with params:
   - `store_url`: Encoded site URL
   - `consumer_key`: Generated consumer key
   - `consumer_secret`: Generated consumer secret
   - `user_email`: Current admin email
   - `store_name`: Blog name
   - `state`: Random 32-character state token
   - `signature`: SHA256 hash for verification

### 2. Widget Toggle

The toggle switch controls whether the BenriBot widget is embedded on the frontend:

- When ON: Widget script is enqueued with client key
- When OFF: Script is not loaded
- State is persisted in WordPress options

### 3. Status Management

The plugin tracks three states:

- `benribot_connected`: Boolean for connection status
- `benribot_client_key`: The client key from BenriBot
- `benribot_widget_embedded`: Whether widget should be shown

### 4. Security Features

- WordPress nonces for all API requests
- SHA256 signature verification for onboarding URLs
- OAuth state token for CSRF protection
- Consumer keys stored securely in database

## File Structure

```
benribot-for-woocommerce/
├── src/
│   ├── components/
│   │   ├── AdminApp.jsx          # Main app logic & API calls
│   │   └── ConnectCard.jsx       # UI card with all elements
│   ├── index.js                  # Entry point
│   └── style.css                 # All styles
├── assets/
│   ├── logo.svg                  # BenriBot logo
│   └── logo.png                  # Placeholder
├── build/                        # Compiled assets
│   ├── index.js                  # Bundled JS
│   ├── style-index.css          # Bundled CSS
│   └── index.asset.php          # Dependency info
├── benribot-for-woocommerce.php  # Main plugin file
├── package.json                  # Dependencies
├── .gitignore
├── .pnpmrc
└── README.md
```

## Styling

The plugin uses BenriBot brand guidelines:

- **Primary Color**: `#16a34a ` (BenriBot green)
- **Font**: Inter (with fallbacks)
- **Design**: Rounded corners (8-16px), subtle shadows, clean spacing
- **Responsive**: Mobile-friendly with media queries

Key UI elements:

- Card: 32px padding, 16px border-radius, box-shadow
- Buttons: 8px border-radius, hover effects with transform
- Toggle: 48px width, animated slider with green active state
- Status indicators: Green checkmark, orange warning icon

## Building

```bash
# Install dependencies
pnpm install

# Build for production
pnpm run build

# Development mode (with hot reload)
pnpm run start
```

## Testing the Plugin

1. **Activate the plugin** in WordPress admin
2. Navigate to **BenriBot** menu item
3. You should see the React admin interface
4. Click "Connect Account" to test the connection flow
5. Toggle the widget switch to test enable/disable

## API Flow

### Connection Flow

```
User clicks "Connect Account"
    ↓
POST /benribot/v1/connect
    ↓
Generate consumer keys
    ↓
Create signature hash
    ↓
Return redirect URL
    ↓
Redirect to app.benribot.com/onboarding
    ↓
BenriBot completes setup
    ↓
Returns to plugin with client_key
    ↓
Save client_key
    ↓
Widget is enabled
```

### Widget Toggle Flow

```
User toggles switch
    ↓
POST /benribot/v1/widget-toggle { enabled: true/false }
    ↓
Update benribot_widget_embedded option
    ↓
If enabled: enqueue script in wp_footer
If disabled: don't enqueue
```

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Future Enhancements

Potential features for future versions:

1. **Analytics Dashboard**: Show widget performance metrics
2. **Custom Styling**: Allow users to customize widget appearance
3. **Chat History**: Display recent conversations in admin
4. **A/B Testing**: Test different bot configurations
5. **Multi-language Support**: i18n for admin interface
6. **Webhook Support**: Receive events from BenriBot platform

## Troubleshooting

### Build Issues

If you encounter build errors:

```bash
# Clear node_modules and reinstall
rm -rf node_modules pnpm-lock.yaml
pnpm install
pnpm run build
```

### Plugin Not Loading

1. Check browser console for JavaScript errors
2. Verify build files exist in `/build/` directory
3. Check WordPress debug log for PHP errors
4. Verify nonces are working (check Network tab)

### Widget Not Showing

1. Verify `benribot_widget_embedded` option is set to `true`
2. Check that `benribot_client_key` has a value
3. Inspect page source to see if script is enqueued
4. Check browser console for widget errors

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Support

For issues or questions:

- GitHub Issues: https://github.com/benribot/benribot-for-woocommerce/issues
- Email: support@benribot.com
- Docs: https://docs.benribot.com
