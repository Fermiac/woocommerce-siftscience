<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class registers all hooks related to events that are reported to SiftScience events.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Hooks_Events' ) ) :

	include_once( 'class-wc-siftscience-events.php' );
	include_once( 'class-wc-siftscience-options.php' );

	class WC_SiftScience_Hooks_Events {
		private $events;
		private $options;

		public function __construct( WC_SiftScience_Events $events, WC_SiftScience_Options $options ) {
			$this->events = $events;
			$this->options = $options;
		}

		public function run() {
			add_action( 'wp_enqueue_scripts', array( $this->events, 'add_script' ) );
			add_action( 'login_enqueue_scripts', array( $this->events, 'add_script' ) );

			add_action( 'wp_logout', array( $this->events, 'logout' ), 10, 2 );
			add_action( 'wp_login', array( $this->events, 'login_success' ), 10, 2 );
			add_action( 'wp_login_failed', array( $this->events, 'login_failure' ) );
			add_action( 'user_register', array( $this->events, 'create_account' ) );
			add_action( 'profile_update', array( $this->events, 'update_account' ), 10, 2 );

			add_action( 'woocommerce_add_to_cart', array( $this->events, 'add_to_cart' ) );
			add_action( 'woocommerce_remove_cart_item', array( $this->events, 'remove_from_cart' ) );

			if ( $this->options->send_on_create_enabled() ) {
				add_action( 'woocommerce_new_order', array( $this->events, 'create_order' ) );
			}

			add_action( 'woocommerce_new_order', array( $this->events, 'add_session_info' ) );
			add_action( 'woocommerce_order_status_changed', array( $this->events, 'update_order_status' ) );
			add_action( 'post_updated', array( $this->events, 'update_order' ) );
		}
	}

endif;
