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

BenriBot is your AI agent for eCommerce. This plugin allows you to easily embed the BenriBot chat widget into your WooCommerce store. Simply add your Client Key from your BenriBot dashboard to get started. The widget will appear in your store's footer, ready to assist your customers.

== Installation ==

1. Upload the `benribot-for-woocommerce` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the "BenriBot" settings page in your WordPress admin dashboard.
4. Add your Client Key and save the settings.

== Frequently Asked Questions ==

= Where can I find my Client Key? =

You can find your Client Key in your BenriBot dashboard after signing up.

= Does this plugin work without WooCommerce? =

While it is named for WooCommerce, the plugin simply injects a script into your site's footer and does not have a hard dependency on WooCommerce. However, BenriBot itself is optimized for eCommerce platforms.

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
* Updated branding with BenriBot design guidelines

= 1.0.2 =
* Security: Removed arbitrary embed code functionality to comply with WordPress.org security guidelines.
* Users now only need to provide their Client Key - the script is generated programmatically.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release of the plugin.
