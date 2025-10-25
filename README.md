# BenriBot for WooCommerce

A modern React-based WordPress plugin that integrates the BenriBot AI chat widget into your WooCommerce store with a one-click connection experience.

## Features

- 🎨 **Modern React Admin Interface** - Clean, intuitive UI built with React and WordPress scripts
- 🚀 **One-Click Account Connection** - Seamless integration with BenriBot's onboarding flow
- 🎛️ **Widget Toggle Control** - Easily enable/disable the chat widget on your store
- 🔒 **Secure REST API** - Safe data handling with WordPress nonces
- 📱 **Responsive Design** - Works beautifully on all devices
- ⚡ **Fast & Lightweight** - Optimized bundle with minimal dependencies

## Requirements

- WordPress 5.0 or higher
- WooCommerce 3.0 or higher
- PHP 7.4 or higher
- Node.js 16+ (for development)

## Installation

### For End Users

1. Download the plugin zip file
2. Upload it to your WordPress site via Plugins > Add New > Upload
3. Activate the plugin
4. Navigate to the "BenriBot" menu in your WordPress admin
5. Click "Connect Account" to integrate your store

### For Developers

1. Clone the repository:

```bash
git clone https://github.com/benribot/benribot-for-woocommerce.git
cd benribot-for-woocommerce
```

2. Install dependencies:

```bash
pnpm install
```

3. Build the plugin:

```bash
pnpm run build
```

4. Activate the plugin through WordPress admin

## Development

### Available Scripts

- `pnpm run build` - Build the production bundle
- `pnpm run start` - Start development mode with hot reloading
- `pnpm run packages-update` - Update WordPress packages

### Project Structure

```
benribot-for-woocommerce/
├── src/
│   ├── components/
│   │   ├── AdminApp.jsx      # Main React app component
│   │   └── ConnectCard.jsx   # UI card component
│   ├── index.js             # Entry point
│   └── style.css            # Styles
├── assets/
│   └── logo.png             # Plugin logo
├── build/                   # Compiled assets (generated)
├── benribot-for-woocommerce.php  # Main plugin file
└── package.json
```

## API Endpoints

The plugin exposes the following REST API endpoints:

### GET `/wp-json/benribot/v1/status`

Returns the current connection status and widget state.

**Response:**

```json
{
  "connected": true,
  "client_key": "your_client_key",
  "widget_embedded": true
}
```

### POST `/wp-json/benribot/v1/connect`

Generates consumer keys and returns the onboarding redirect URL.

**Response:**

```json
{
  "success": true,
  "redirect_url": "https://app.benribot.com/onboarding/woocommerce?..."
}
```

### POST `/wp-json/benribot/v1/widget-toggle`

Toggles the widget embed status.

**Request:**

```json
{
  "enabled": true
}
```

**Response:**

```json
{
  "success": true,
  "enabled": true
}
```

## How It Works

1. **Connection Flow:**

   - User clicks "Connect Account"
   - Plugin generates WooCommerce consumer keys
   - User is redirected to BenriBot onboarding with encrypted parameters
   - After completion, the client key is saved and the widget is enabled

2. **Widget Embedding:**

   - When enabled, the plugin injects the BenriBot script into the site footer
   - The script is loaded asynchronously with the client key
   - The widget appears on all frontend pages

3. **Security:**
   - All API requests are protected with WordPress nonces
   - Consumer keys are securely stored in the database
   - Connections are verified with signature hashing

## Branding

The plugin uses the BenriBot brand guidelines:

- **Primary Color:** `#16a34a ` (BenriBot green)
- **Font:** Inter
- **Design:** Clean, modern UI with rounded corners and subtle shadows

## Support

For support, please visit [BenriBot Support](https://benribot.com/support) or open an issue on GitHub.

## License

GPLv2 or later

## Changelog

### 2.0.0

- Complete redesign with React-based admin interface
- Added one-click connection flow
- Added widget toggle control
- Improved security with signature verification
- Better mobile responsiveness

### 1.0.2

- Security improvements
- Removed arbitrary embed code functionality

### 1.0.0

- Initial release
