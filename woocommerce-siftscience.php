<?php
/*
Plugin Name: Sift for WooCommerce
Plugin URI: https://github.com/Fermiac/woocommerce-siftscience
Description: Get a handle on fraud with Sift - a modern approach to fraud prevention that uses machine learning.
Author: Nabeel Sulieman, Rami Jamleh, Lukas Svec
Version: 1.1.0
Author URI: https://github.com/Fermiac/woocommerce-siftscience/wiki
License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
	&& ! class_exists( 'WCSiftScience' ) ) :

	// make sure session is started as soon as possible
	if ( ! headers_sent() && session_status() !== PHP_SESSION_ACTIVE ) {
		session_start();
	}

	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-options.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-logger.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-stats.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-instrumentation.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-comm.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-api.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-events.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-admin.php' );
	require_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-orders.php' );
	require_once( dirname( __FILE__ ) . '/includes/third-party/class-wc-siftscience-stripe.php' );

	class WC_SiftScience_Plugin {
		/**
		 * Initialize all the classes and hook into everything
		 */
		public function run() {
			$options = new WC_SiftScience_Options( '1.1.0' );
			$logger = new WC_SiftScience_Logger( $options );
			$stats = new WC_SiftScience_Stats( $options, $logger );
			$comm = new WC_SiftScience_Comm( $options, $logger );

			$events = new WC_SiftScience_Events( $comm, $options, $logger );
			$order = new WC_SiftScience_Orders( $options );
			$admin = new WC_SiftScience_Admin( $options, $comm, $logger, $stats );
			$api = new WC_SiftScience_Api( $comm, $events, $options, $logger, $stats );
			$stripe = new WC_SiftScience_Stripe( $events, $logger, $stats );

			// wrap all the classes in error catcher
			$events_wrapped = new WC_SiftScience_Instrumentation( $events, 'events', $logger, $stats );
			$order_wrapped = new WC_SiftScience_Instrumentation( $order, 'order', $logger, $stats );
			$admin_wrapped = new WC_SiftScience_Instrumentation( $admin, 'admin', $logger, $stats );
			$api_wrapped = new WC_SiftScience_Instrumentation( $api, 'api', $logger, $stats );
			$stripe_wrapped = new WC_SiftScience_Instrumentation( $stripe, 'stripe', $logger, $stats );

			// admin hooks
			add_filter( 'woocommerce_settings_tabs_array', array( $admin_wrapped, 'add_settings_page' ), 30 );
			add_filter( 'woocommerce_sections_siftsci', array( $admin_wrapped, 'get_sections' ) );
			add_action( 'woocommerce_settings_siftsci', array( $admin_wrapped, 'output_settings_fields' ) );
			add_action( 'woocommerce_settings_save_siftsci', array( $admin_wrapped, 'save_settings' ) );
			add_action( 'admin_notices', array( $admin_wrapped, 'settings_notice' ) );

			// order hooks
			add_filter( 'manage_edit-shop_order_columns', array( $order_wrapped, 'create_header' ), 100 );
			add_action( 'manage_shop_order_posts_custom_column', array( $order_wrapped, 'create_row' ), 11 );
			add_action( 'add_meta_boxes', array( $order_wrapped, 'add_meta_box' ) );

			// events hooks
			add_action( 'wp_enqueue_scripts', array( $events_wrapped, 'add_script' ) );
			add_action( 'login_enqueue_scripts', array( $events_wrapped, 'add_script' ) );
			add_action( 'wp_logout', array( $events_wrapped, 'logout' ), 100, 2 );
			add_action( 'wp_login', array( $events_wrapped, 'login_success' ), 100, 2 );
			add_action( 'wp_login_failed', array( $events_wrapped, 'login_failure' ), 100 );
			add_action( 'user_register', array( $events_wrapped, 'create_account' ), 100 );
			add_action( 'profile_update', array( $events_wrapped, 'update_account' ), 100, 2 );
			add_action( 'woocommerce_add_to_cart', array( $events_wrapped, 'add_to_cart' ), 100 );
			add_action( 'woocommerce_remove_cart_item', array( $events_wrapped, 'remove_from_cart' ), 100 );
			if ( $options->auto_send_enabled() ) {
				add_action( 'woocommerce_checkout_order_processed', array( $events_wrapped, 'create_order' ), 100 );
			}
			add_action( 'woocommerce_new_order', array( $events_wrapped, 'add_session_info' ), 100 );
			add_action( 'woocommerce_order_status_changed', array( $events_wrapped, 'update_order_status' ), 100 );
			add_action( 'post_updated', array( $events_wrapped, 'update_order' ), 100 );
			add_action( 'shutdown', array( $events_wrapped, 'shutdown' ) );

			// Ajax API hook
			add_action( 'wp_ajax_wc_siftscience_action', array( $api_wrapped, 'handle_ajax' ), 100 );

			// Run stats update at shutdown
			add_action( 'shutdown', array( $stats, 'shutdown' ) );

			// Stripe
			add_action( 'wc_gateway_stripe_process_payment', array( $stripe_wrapped, 'stripe_payment' ), 10, 2 );
			add_filter( 'wc_siftscience_order_payment_method', array( $stripe_wrapped, 'order_payment_method' ), 10, 2 );
		}
	}

	$wc_siftscience_plugin = new WC_SiftScience_Plugin();
	add_action( 'init', array( $wc_siftscience_plugin, 'run' ) );

endif;
