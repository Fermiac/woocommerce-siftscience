=== Sift for WooCommerce ===
Contributors: nabsul, ramico
Tags: sift science, woocommerce, fraud, fermiac
Requires at least: 4.7.1
Tested up to: 5.4.2
Stable tag: 1.1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin integrates Sift fraud detection with your WooCommerce store.
Plugin is tested with WordPress (5.4.2) and WooCommerce (4.3.1).

== Description ==

Add this plugin to your WooCommerce shop to get Sift Science fraud detection. Features:

* Sending login, logout, cart actions event data to Sift 
* Sending order details to Sift automatically or via the orders page
* Fetch and display Sift fraud score in orders list and order view
* Flagging users as good/bad to train Sift and improve accuracy

== Installation ==

1. Install Fermiac Sift from the WordPress Store
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the WooCommerce->Settings->Sift screen to configure the plugin

Please help us improve the plugin by enabling [anonymous statistics and error collection](https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection).

== Changelog ==

= 1.1.0 =
* 2020-08-02
* Welcomed @ramico to the development team
* Tested with latest versions of WordPress and WooCommerce
* Retested and fixed several API issues
* Fixed all warnings of deprecated function usage
* Add a minimum order value feature for auto-send
* Converted UI controls from React to VueJS

= 1.0.2 =
* 2018-10-15
* Redeploy this time for real
* See 1.0.1 for changes

= 1.0.1 =
* 2018-09-28
* Added docker development environment
* Fixed reported issue: https://github.com/Fermiac/woocommerce-siftscience/issues/104

= 1.0.0 =
* 2017-03-11
* Added support for WooCommerce Stripe Payment Gateway
* Including plugin version in stats
* Performance improvement: Move all API calls to end of runtime
* Various code refactoring improvements

= 0.9.1 =
* 2017-01-17
* Fixed mismatch in release versions

= 0.9.0 =
* 2017-01-17
* Fix: Can't back-fill more than one order per user
* Fix: Transaction details sent multiple times
* Fix: Correct the mapping of WooCommerce order status to Sift Science transaction status
* Added [stats collector](https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection) for reporting issues directly to us

= 0.4.3 Beta =
* 2016-11-19
* Add better logging of errors and a debug tab in settings

= 0.4.2 Beta =
* 2016-11-15
* Add JavaScript version to avoid caching issues
* Send out transaction information only on order status change (reduces # in SS attempts field)
* Refactored some code for better readability

= 0.4.1 Beta =
* 2016-11-14
* Switch to WordPress's built in Ajax handler

= 0.4.0 Beta =
* 2016-11-13
* Plug in Sift Science $transaction event
* Improve error handling for React-API interface

= 0.3.8 Beta =
* 2016-11-12
* Bug fix: Score colors should be red for high value and green for low value
* Add settings fields to make score colors customizable to different levels
* Move call to session_start() so that it doesn't get triggered too late

= 0.3.7 Beta =
* 2016-10-16
* Added the logout event
* Fixed a bug where WC_Order_Refund was causing the API to crash

= 0.3.6 Beta =
* 2016-10-09
* Implement a logout method (not plugged into wpcom though)
* Improve webpack to make a smaller React App
* Add prefix to user_name field (support multiple stores for one Sift Science account)
* Add more filters and actions
* Disable $update_order and $order_status if order is not backfilled

= 0.3.5 Beta =
* Fix calls to plugins_url() to get the right path to images and js scripts

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