<?php
/*
Plugin Name: WooCommerce - SiftScience
Plugin URI: https://github.com/Fermiac/woocommerce-siftscience
Description: Get a handle on fraud with SiftScience - a modern approach to fraud prevention that uses machine learning.
Author: Lukas Svec, Nabeel Sulieman
Version: 0.0.1
Author URI: https://github.com/Fermiac/woocommerce-siftscience/wiki
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
	&& ! class_exists( 'WCSiftScience' ) ) :

	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-options.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-comm.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-backfill.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-hooks-admin.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-hooks-orders.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-hooks-events.php' );

	class WC_SiftScience_Plugin {
		/**
		 * Runs all the needed code that sets up the hooks
		 */
		public function run() {
			$settings = new WC_SiftScience_Options();
			$comm = new WC_SiftScience_Comm();
			$logger = new WC_SiftScience_Logger();
			$backfill = new WC_SiftScience_Backfill();

			(new WC_SiftScience_Hooks_Admin)->run();
			(new WC_SiftScience_Hooks_Orders)->run();
			(new WC_SiftScience_Hooks_Events( $settings, $comm, $logger, $backfill ) )->run();
		}
	}

	( new WC_SiftScience_Plugin )->run();

endif;
