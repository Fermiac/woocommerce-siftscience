<?php
/*
Plugin Name: Sift Science for WooCommerce
Plugin URI: https://github.com/Fermiac/woocommerce-siftscience
Description: Get a handle on fraud with Sift Science - a modern approach to fraud prevention that uses machine learning.
Author: Nabeel Sulieman, Lukas Svec
Version: 0.3.8
Author URI: https://github.com/Fermiac/woocommerce-siftscience/wiki
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
	&& ! class_exists( 'WCSiftScience' ) ) :

	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-logger.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-options.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-comm.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-events.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-hooks-admin.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-hooks-orders.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-hooks-events.php' );

	class WC_SiftScience_Plugin {
		/**
		 * Runs all the needed code that sets up the hooks
		 */
		public function run() {
			$logger = new WC_SiftScience_Logger();
			$options = new WC_SiftScience_Options();
			$comm = new WC_SiftScience_Comm( $options, $logger );
			$events = new WC_SiftScience_Events( $comm, $options );

			$admin = new WC_SiftScience_Hooks_Admin( $options, $comm );
			$order = new WC_SiftScience_Hooks_Orders( $options );
			$events = new WC_SiftScience_Hooks_Events( $events, $options );

			$admin->run();
			$order->run();
			$events->run();
		}
	}

	$wc_siftscience_plugin = new WC_SiftScience_Plugin();
	add_action( 'init', array( $wc_siftscience_plugin, 'run' ) );

	// make sure session is started as soon as possible
	if ( session_status() != PHP_SESSION_ACTIVE ) {
		session_start();
	}

endif;
