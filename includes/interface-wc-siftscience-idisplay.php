<?php
/**
 * Additional functionality related to the WooCommerce Stripe Gateway plugin
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! interface_exists( 'WC_SiftScience_IDisplay' ) ) :
	interface WC_SiftScience_IDisplay {
		/**
		 * This function displayes sections in a bar separated list in regards of the current section
		 *
		 * @param Array  $sections     the sections to be displayed.
		 * @param String $admin_id     The admin Id.
		 */
		public function display_sections( $sections, $admin_id );

		/**
		 * This function displayes a bootstrap notice for improveing plugin.
		 *
		 * @param stting $enabled_link the enabled url.
		 * @param string $disabled_link the disabled url.
		 */
		public function display_improve_message( $enabled_link, $disabled_link );

		/**
		 * This function is to cdisplay a notice so the user shoulr update their plugin
		 *
		 * @param string $settings_url the link to update plugin.
		 */
		public function disply_update_notice( $settings_url );

		/**
		 * This function displays the stats of time and method calls.
		 *
		 * @param Array  $stats the data stored in  WC_SiftScience_Options::STATS.
		 * @param String $url   this url is used to clear stats.
		 */
		public function display_stats_tables( $stats, $url );

		/**
		 * This function displays debugging info for ssl and logs in HTML format
		 *
		 * @param Mixed  $ssl_data get_transient from admin returns this data.
		 * @param String $ssl_url  an action button to check ssl vertion and this is it's url.
		 * @param String $log_url  an action button to clear logs this is it's url.
		 * @param String $logs     the logs retrieved gtom debug DOT log file.
		 */
		public function display_debugging_info( $ssl_data, $ssl_url, $log_url, $logs );
	}
endif;
