=== BenriBot for WooCommerce ===
Contributors: benribotai
Tags: woocommerce, chat, chatbot, ai, ecommerce
Requires at least: 5.0
Tested up to: 6.8
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integrates the BenriBot AI chat widget into your WooCommerce store with a modern React-based admin interface.

== Description ==

BenriBot is your AI agent for eCommerce. This plugin now provides a one‑click connection flow and a modern React admin UI. Connect your store to BenriBot from the BenriBot settings page; the plugin will securely exchange keys and enable the chat widget. You can toggle the widget on/off anytime.

== Installation ==

1. Upload the `benribot-for-woocommerce` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the "BenriBot" settings page in your WordPress admin dashboard.
4. Click "Connect Account" to start the one‑click onboarding. Once connected, use the switch to enable/disable the widget.

== Frequently Asked Questions ==

= How does the connection work? =

The plugin uses a one-click secure connection to link your WooCommerce store with your BenriBot account. When you click “Connect Account,” your store’s API keys are created and verified automatically—no need to copy or paste anything.

= Is the chatbot customizable? =

Yes, you can fully customize the chatbot’s appearance, tone, and automation behavior from your BenriBot dashboard.

= What information is shared with BenriBot? =

The plugin securely shares your store’s public details and API credentials to enable real-time syncing of products, categories, and customer messages. No sensitive customer payment data is ever shared.

= Does this plugin work with all WooCommerce themes? =

Yes. The BenriBot chatbot is lightweight and theme-independent. It works smoothly with all WooCommerce-compatible themes.

= Can I disconnect my store from BenriBot? =

Yes, you can disconnect anytime from the BenriBot plugin settings or your BenriBot dashboard. This will immediately stop all data syncing.

= Will this plugin slow down my website? =

No. The BenriBot chatbot loads asynchronously through a lightweight script, ensuring your site speed and performance remain unaffected.

== External services ==

This plugin connects to the BenriBot CDN service to load the chat widget functionality. This connection is required for the plugin to function properly.

**What data is sent and when:**
- The plugin loads the widget script from https://cdn.benribot.com/v1/widget.js and passes your Client Key to initialize the widget. No personal user data is transmitted.
- Customer interactions with the chat widget (messages typed, responses received)

**Service provider:**
This service is provided by BenriBot. For more information, please review their:
- Terms of Service: https://benribot.com/terms-of-service
- Privacy Policy: https://benribot.com/privacy-policy

== Changelog ==

= 2.0.0 =
* Complete redesign with modern React-based admin interface
* Added one-click account connection flow
* Added widget toggle control for easy enable/disable
* Implemented secure REST API endpoints for connection management
* Improved security with signature verification
* Better mobile responsiveness and UX

= 1.0.2 =
* Security: Removed arbitrary embed code functionality to comply with WordPress.org security guidelines.
* Users now only need to provide their Client Key - the script is generated programmatically.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release of the plugin.
