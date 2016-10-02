=== Plugin Name ===
Contributors: nabsul
Tags: sift science, woocommerce, fraud
Requires at least: 4.6.1
Tested up to: 4.6.1
Stable tag: 0.3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrates Sift Science fraud detection with your WooCommerce store.

== Description ==

WARNING: This code is currently in beta. It's close to being production-ready, but testers are needed.

Add this plugin to your WooCommerce shop to get Sift Science fraud detection. Features:

* Sending login, logout, cart actions event data to Sift Science
* Sending order details to Sift Science via the orders page
* Flagging users as good/bad to train Sift Science and improve accuracy

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/woocommerce-siftscience` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the WooCommerce->Settings->SiftScience screen to configure the plugin

== Changelog ==

= 0.3.4 Beta =
* Added update_order and order_status events
* Added method for sending transaction data
* Removed error_log messages
* Fixed some unix line endings

= 0.3.3 Beta =
* Fixed open-in-siftscience action
* Switched to latest Sift Science API endpoints
* Add IP address to create order event

= 0.3 Beta =
* Added essential filters on event data

= 0.2 Beta =
* Order UI fully functioning
* Events cleaned up and working correctly

= 0.1 Alpha =
* Initial version with very limited functionality
