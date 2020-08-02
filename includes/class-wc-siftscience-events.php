<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class registers all hooks related to events that are reported to Sift events.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Events' ) ) :

	require_once( 'class-wc-siftscience-options.php' );
	require_once( 'class-wc-siftscience-logger.php' );
	require_once( 'class-wc-siftscience-comm.php' );
	require_once( 'class-wc-siftscience-format.php' );

	class WC_SiftScience_Events {
		private $format;
		private $comm;
		private $options;
		private $logger;
		private $saved_user_id = null;

		public function __construct( WC_SiftScience_Comm $comm, WC_SiftScience_Options $options, WC_SiftScience_Logger $logger ) {
			$this->format = new WC_SiftScience_Format( $options, $logger );
			$this->comm = $comm;
			$this->options = $options;
			$this->logger = $logger;
			$this->saved_user_id = get_current_user_id();
		}

		public function is_backfilled( $post_id ) {
			$is_backfilled = get_post_meta( $post_id, $this->options->get_backfill_meta_key(), true );
			return $is_backfilled === '1';
		}

		public function set_backfill( $post_id ) {
			update_post_meta( $post_id, $this->options->get_backfill_meta_key(), '1' );
		}

		public function unset_backfill( $post_id ) {
			delete_post_meta( $post_id, $this->options->get_backfill_meta_key() );
		}

		public function add_session_info( $order_id ) {
			$order = wc_get_order( $order_id );
			$post_id = $order->get_id();
			$meta_key = $this->options->get_session_meta_key();
			$session_id = $this->options->get_session_id();
			do_action( 'wp_siftscience_save_session_info', $post_id, $session_id );
			update_post_meta( $post_id, $meta_key, $session_id );
		}

		public function add_script() {
			$data = array(
				'session_id' => $this->options->get_session_id(),
				'js_key'     => $this->options->get_js_key(),
			);

			$user_id = $this->options->get_current_user_id();
			if ( null !== $user_id ) {
				$data[ 'user_id' ] = $this->options->get_user_id_from_user_id( $user_id );
			}

			$name = 'wc-siftsci';
			$path = plugins_url( "dist/wc-siftsci.js", dirname( __FILE__ ) );
			$v = $this->options->get_version();
			$key = '_wc_siftsci_js_input_data';
			$data = apply_filters( 'wc_siftscience_js_script_data', $data );

			wp_enqueue_script( $name, $path, array( 'jquery' ), $v, true );
			wp_localize_script( $name, $key, $data );
		}

		public function shutdown() {
			$this->send_queued_data();
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/login
		public function login_success( $username, $user ) {
			$data = $this->format->login->login_success( $user );
			$this->events[] = $data;
			$this->link_session_to_user( $user->ID );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/login
		public function login_failure( $username ) {
			$this->events[] = $this->format->login->login_failure( $username );
		}

		//https://siftscience.com/developers/docs/curl/events-api/reserved-events/logout
		public function logout() {
			$data = $this->format->login->logout( $this->saved_user_id );
			$this->events[] = $data;
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/create-account
		public function create_account( $user_id ) {
			$user = get_userdata( $user_id );
			$data = $this->format->account->create_account( $user_id, $user );
			$this->events[] = $data;
			$this->link_session_to_user( $user->ID );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/update-account
		public function update_account( $user_id, $old_user_data ) {
			$data = $this->format->account->update_account( $user_id, $old_user_data );
			$this->events[] = $data;
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/create-order
		public function create_order( $order_id ) {
			if ( ! $this->is_auto_send( $order_id ) ) {
				return;
			}
			$this->order_map[ $order_id ] = 'create';
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/update-order
		public function update_order( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order === false ) {
				return;
			}

			if ( ! $this->is_auto_send( $order_id ) ) {
				return;
			}

			if ( ! isset( $this->order_map[ $order_id ] ) ) {
				$this->order_map[ $order_id ] = 'update';
			}
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/order-status
		public function update_order_status( $order_id ) {
			if ( ! $this->is_auto_send( $order_id ) ) {
				return;
			}

			$data = $this->format->order->update_order_status( $order_id );
			if ( null === $data ) {
				return;
			}

			$this->events[] = $data;
			$this->send_transaction( $order_id );
		}

		private function is_auto_send( $order_id ) {
			if ( $this->is_backfilled( $order_id ) ) {
				return true;
			}

			if ( ! $this->options->auto_send_enabled() ) {
				return false;
			}

			$min_value = ( float ) ( $this->options->get_min_order_value() );

			if ( $min_value === 0 ) {
				return true;
			}

			$order = wc_get_order( $order_id );
			if ( $order === false ) {
				return false;
			}

			$order_amount = ( float )( $order->get_total() );
			return $order_amount >= $min_value;
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/transaction
		public function send_transaction( $order_id ) {
			$data = $this->format->transactions->create_transaction( $order_id );
			$this->events[] = $data;
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/add-item-to-cart
		public function add_to_cart( $cart_item_key ) {
			$data = $this->format->cart->add_to_cart( $cart_item_key );
			$this->events[] = $data;
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/remove-item-from-cart
		public function remove_from_cart( $cart_item_key ) {
			$data = $this->format->cart->remove_from_cart( $cart_item_key );
			$this->events[] = $data;
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/link-session-to-user
		public function link_session_to_user( $user_id ) {
			$data = $this->format->account->link_session_to_user( $user_id );
			$this->events[] = $data;
		}

		private $order_map = array();
		private $events = array();

		public function send_queued_data() {
			foreach( $this->order_map as $order_id => $type ) {
				$this->send_order_event( $order_id, $type );
			}

			foreach ( $this->events as $event ) {
				if ( null != $event ) {
					$this->comm->post_event( $event );
				}
			}

			$this->order_map = array();
			$this->events = array();
		}

		private function send_order_event( $order_id, $type = 'create' ) {
			$data = $this->format->order->create_order( $order_id, $type );
			if ( null !== $data ) {
				$this->events[] = $data;
				$this->send_transaction( $order_id );
				$this->set_backfill( $order_id );
			}
		}
	}

endif;
