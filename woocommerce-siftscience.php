<?php
/*
Plugin Name: Sift Science for WooCommerce
Plugin URI: https://github.com/Fermiac/woocommerce-siftscience
Description: Get a handle on fraud with Sift Science - a modern approach to fraud prevention that uses machine learning.
Author: Nabeel Sulieman, Lukas Svec
Version: 1.0.0
Author URI: https://github.com/Fermiac/woocommerce-siftscience/wiki
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
	&& ! class_exists( 'WCSiftScience' ) ) :

	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-options.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-logger.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-stats.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-instrumentation.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-comm.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-api.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-events.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-admin.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-orders.php' );

	class WC_SiftScience_Plugin {
		/**
		 * Initialize all the classes and hook into everything
		 */
		public function run() {
			$options = new WC_SiftScience_Options( '1.0.0' );
			$logger = new WC_SiftScience_Logger( $options );
			$stats = new WC_SiftScience_Stats( $options, $logger );
			$comm = new WC_SiftScience_Comm( $options, $logger );

			$events = new WC_SiftScience_Events( $comm, $options );
			$order = new WC_SiftScience_Orders( $options );
			$admin = new WC_SiftScience_Admin( $options, $comm, $logger, $stats );
			$api = new WC_SiftScience_Api( $comm, $events, $options, $logger, $stats );

			// wrap all the classes in error catcher
			$events = new WC_SiftScience_Instrumentation( $events, 'events', $logger, $stats );
			$order = new WC_SiftScience_Instrumentation( $order, 'order', $logger, $stats );
			$admin = new WC_SiftScience_Instrumentation( $admin, 'admin', $logger, $stats );
			$api = new WC_SiftScience_Instrumentation( $api, 'api', $logger, $stats );

			// admin hooks
			add_filter( 'woocommerce_settings_tabs_array', array( $admin, 'add_settings_page' ), 30 );
			add_filter( 'woocommerce_sections_siftsci', array( $admin, 'get_sections' ) );
			add_action( 'woocommerce_settings_siftsci', array( $admin, 'output_settings_fields' ) );
			add_action( 'woocommerce_settings_save_siftsci', array( $admin, 'save_settings' ) );
			add_action( 'admin_notices', array( $admin, 'settings_notice' ) );

			// order hooks
			add_filter( 'manage_edit-shop_order_columns', array( $order, 'create_header' ), 100 );
			add_action( 'manage_shop_order_posts_custom_column', array( $order, 'create_row' ), 11 );
			add_action( 'add_meta_boxes', array( $order, 'add_meta_box' ) );

			// events hooks
			add_action( 'wp_enqueue_scripts', array( $events, 'add_script' ) );
			add_action( 'login_enqueue_scripts', array( $events, 'add_script' ) );
			add_action( 'wp_logout', array( $events, 'logout' ), 10, 2 );
			add_action( 'wp_login', array( $events, 'login_success' ), 10, 2 );
			add_action( 'wp_login_failed', array( $events, 'login_failure' ) );
			add_action( 'user_register', array( $events, 'create_account' ) );
			add_action( 'profile_update', array( $events, 'update_account' ), 10, 2 );
			add_action( 'woocommerce_add_to_cart', array( $events, 'add_to_cart' ) );
			add_action( 'woocommerce_remove_cart_item', array( $events, 'remove_from_cart' ) );
			if ( $options->send_on_create_enabled() ) {
				add_action( 'woocommerce_new_order', array( $events, 'create_order' ) );
			}
			add_action( 'woocommerce_new_order', array( $events, 'add_session_info' ) );
			add_action( 'woocommerce_order_status_changed', array( $events, 'update_order_status' ) );
			add_action( 'post_updated', array( $events, 'update_order' ) );

			// Ajax API hook
			add_action( 'wp_ajax_wc_siftscience_action', array( $api, 'handle_ajax' ) );
		}
	}

	$wc_siftscience_plugin = new WC_SiftScience_Plugin();
	add_action( 'init', array( $wc_siftscience_plugin, 'run' ) );

	// make sure session is started as soon as possible
	if ( session_status() != PHP_SESSION_ACTIVE ) {
		session_start();
	}

endif;
