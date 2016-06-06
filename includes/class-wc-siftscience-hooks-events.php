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

	include_once( 'class-wc-siftscience-options.php' );
	include_once( 'class-wc-siftscience-eventdata.php' );
	include_once( 'class-wc-siftscience-comm.php' );
	include_once( 'class-wc-siftscience-backfill.php' );
	include_once( 'class-wc-siftscience-nonce.php' );

	class WC_SiftScience_Hooks_Events {
		private $posts = null;
		private $comm;
		private $logger;
		private $backfill;
		private $options;

		public function __construct( WC_SiftScience_Comm $comm, WC_SiftScience_Logger $logger, WC_SiftScience_Backfill $backfill, WC_SiftScience_Options $options ) {
			$this->comm = $comm;
			$this->logger = $logger;
			$this->backfill = $backfill;
			$this->options = $options;
		}

		public function run() {
			add_action( 'wp_enqueue_scripts', array( $this, 'add_script' ) );
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'create_order' ), 10, 1 );   //new order created

			add_action( 'wp_login', array( $this, 'login_success' ), 10, 2 );
			add_action( 'wp_login_failed', array( $this, 'login_failure' ) );
			add_action( 'user_register', array( $this, 'create_account' ) );
			add_action( 'profile_update', array( $this, 'update_account' ), 10, 2 );

			add_action( 'woocommerce_add_to_cart', array( $this, 'add_to_cart' ) );
			add_action( 'woocommerce_cart_item_removed', array( $this, 'remove_from_cart' ) );

			if ( $this->options->send_on_create_enabled() ) {
				add_action( 'woocommerce_new_order', array( $this, 'woocommerce_new_order' ) );
			}

			add_action( 'woocommerce_new_order', array( $this, 'add_session_info' ) );
		}

		public function woocommerce_new_order( $order_id ) {
			$this->backfill->backfill( $order_id );
		}

		public function add_session_info( $order_id ) {
			$order = wc_get_order( $order_id );
			$post_id = $order->post->ID;
			$meta_key = $this->options->get_session_meta_key();
			$session_id = $this->options->get_session_id();
			update_post_meta( $post_id, $meta_key, $session_id );
		}

		public function add_script() {
			$options = $this->options;
			WC_SiftScience_Html::enqueue_script( 'wc-siftsci-js', array(
					'session_id' => $options->get_session_id(),
					'user_id'    => $options->get_user_id(),
					'js_key'     => $options->get_js_key() )
			);
		}

		public function login_success( $username, $user ) {
			$this->add_api_callback( array(
				'event'        => '$login',
				'user_id'      => $user->ID,
				'login_status' => '$success',
			) );

			$this->link_session_to_user( $user->ID );
		}

		public function login_failure( $username ) {
			$user = get_user_by( 'login', $username );
			if ( $user !== false ) {
				$this->add_api_callback( array(
					'event'        => '$login',
					'user_id'      => $user->ID,
					'login_status' => '$failure',
				) );
			}
		}

		public function create_account( $user_id ) {
			$this->add_api_callback( array(
				'event'   => '$create_account',
				'user_id' => $user_id
			) );
		}

		public function update_account( $user_id, $old_user_data ) {
			$this->add_api_callback( array(
				'event'            => '$update_account',
				'user_id'          => $user_id,
				'changed_password' => $this->is_password_changed( $user_id, $old_user_data )
			) );
		}

		private function is_password_changed( $user_id, $old_user_data ) {
			$user = get_user_by( 'id', $user_id );
			if ( $user === null || $user === false || $old_user_data === null )
				return false;
			return ( isset( $old_user_data->user_pass ) && $user->user_pass !== $old_user_data->user_pass );
		}

		public function create_order( $order_id ) {
			$this->add_api_callback( array(
				'event'    => '$create_order',
				'order_id' => $order_id
			) );
		}

		private function add_api_callback( $data ) {
			$jsData = $data;
			$jsData['nonce'] = wp_create_nonce( WC_SiftScience_Nonce::action( $data ) );
			$jsData['url'] = plugins_url( "woocommerce-siftscience/wc-siftscience-event.php", dirname( __FILE__ ) );

			if ( $this->posts === null ) {
				$this->posts = array();
				WC_SiftScience_Html::enqueue_script( 'wc-siftsci-events' );
			}

			$this->posts[] = $jsData;

			// need to access the script class directly to override added script data.
			global $wp_scripts;
			$code = wp_json_encode( array( 'posts' => $this->posts ) );
			$cmd = "var _wc_siftsci_events_input_data = $code;";
			$wp_scripts->add_data( 'wc_siftsci_events', 'data', $cmd );
		}

		public function add_to_cart( $cart_item_key ) {
			$this->add_api_callback( array(
				'event'   => '$add_item_to_cart',
				'item_id' => $cart_item_key,
			) );
		}

		public function remove_from_cart( $cart_item_key, $cart ) {
			$this->add_api_callback( array(
				'event'   => '$remove_item_from_cart',
				'item_id' => $cart_item_key,
			) );
		}

		public function link_session_to_user( $userId ) {
			$this->add_api_callback( array(
				'event'   => '$link_session_to_user',
				'user_id' => $userId,
			) );
		}

	}

endif;
