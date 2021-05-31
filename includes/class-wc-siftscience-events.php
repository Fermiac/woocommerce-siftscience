<?php
/**
 * This class registers all hooks related to events that are reported to Sift events.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Events' ) ) :

	require_once 'class-wc-siftscience-options.php';
	require_once 'class-wc-siftscience-logger.php';
	require_once 'class-wc-siftscience-comm.php';
	require_once 'class-wc-siftscience-api-account.php';
	require_once 'class-wc-siftscience-api-cart.php';
	require_once 'class-wc-siftscience-api-items.php';
	require_once 'class-wc-siftscience-api-login.php';
	require_once 'class-wc-siftscience-api-order.php';
	require_once 'class-wc-siftscience-api-transaction.php';

	/**
	 * Class WC_SiftScience_Events
	 */
	class WC_SiftScience_Events {
		/**
		 * Request formatting service
		 *
		 * @var WC_SiftScience_Api_Account
		 */
		private $account;

		/**
		 * Request formatting service
		 *
		 * @var WC_SiftScience_Api_Cart
		 */
		private $cart;

		/**
		 * Request formatting service
		 *
		 * @var WC_SiftScience_Api_Login
		 */
		private $login;

		/**
		 * Request formatting service
		 *
		 * @var WC_SiftScience_Api_Order
		 */
		private $order;

		/**
		 * Request formatting service
		 *
		 * @var WC_SiftScience_Api_Transaction
		 */
		private $transaction;

		/**
		 * Communication service
		 *
		 * @var WC_SiftScience_Comm
		 */
		private $comm;

		/**
		 * Communication service
		 *
		 * @var WC_SiftScience_Order_Status
		 */
		private $order_status;

		/**
		 * Options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * The current user id
		 *
		 * @var int
		 */
		private $saved_user_id;

		/**
		 * Cache of orders
		 *
		 * @var array
		 */
		private $order_map;

		/**
		 * Cache of events to be sent out
		 *
		 * @var array
		 */
		private $events;

		/**
		 * Cache of orders to check for status changes
		 *
		 * @var array
		 */
		private $status_checks;

		/**
		 * WC_SiftScience_Events constructor.
		 *
		 * @param WC_SiftScience_Comm            $comm         Communications service.
		 * @param WC_SiftScience_Options         $options      Options service.
		 * @param WC_SiftScience_Api_Account     $account      Account request formatting service.
		 * @param WC_SiftScience_Api_Cart        $cart         Cart request formatting service.
		 * @param WC_SiftScience_Api_Login       $login        Login request formatting service.
		 * @param WC_SiftScience_Api_Order       $order        Order request formatting service.
		 * @param WC_SiftScience_Api_Transaction $transaction  Transaction request formatting service.
		 * @param WC_SiftScience_Order_Status    $order_status Transaction request formatting service.
		 */
		public function __construct(
				WC_SiftScience_Comm $comm,
				WC_SiftScience_Options $options,
				WC_SiftScience_Api_Account $account,
				WC_SiftScience_Api_Cart $cart,
				WC_SiftScience_Api_Login $login,
				WC_SiftScience_Api_Order $order,
				WC_SiftScience_Api_Transaction $transaction,
				WC_SiftScience_Order_Status $order_status ) {
			$this->account      = $account;
			$this->cart         = $cart;
			$this->login        = $login;
			$this->order        = $order;
			$this->transaction  = $transaction;
			$this->comm         = $comm;
			$this->options      = $options;
			$this->order_status = $order_status;

			$this->saved_user_id = get_current_user_id();
			$this->order_map     = array();
			$this->events        = array();
			$this->status_checks = array();
		}

		/**
		 * Checks if the order has already been backfilled
		 *
		 * @param int $post_id The Post ID to check.
		 *
		 * @return bool
		 */
		public function is_backfilled( $post_id ) {
			$is_backfilled = get_post_meta( $post_id, $this->options->get_backfill_meta_key(), true );
			return '1' === $is_backfilled;
		}

		/**
		 * Sets the backfill state of the order
		 *
		 * @param int $post_id The Post ID to check.
		 */
		public function set_backfill( $post_id ) {
			update_post_meta( $post_id, $this->options->get_backfill_meta_key(), '1' );
		}

		/**
		 * Removes the backfilled state of the order
		 *
		 * @param int $post_id The Post id.
		 */
		public function unset_backfill( $post_id ) {
			delete_post_meta( $post_id, $this->options->get_backfill_meta_key() );
		}

		/**
		 * Adds session info to the order.
		 *
		 * @param int $order_id ID of the order.
		 */
		public function add_session_info( $order_id ) {
			$order      = wc_get_order( $order_id );
			$post_id    = $order->get_id();
			$meta_key   = $this->options->get_session_meta_key();
			$session_id = $this->options->get_session_id();

			do_action( 'wp_siftscience_save_session_info', $post_id, $session_id );
			update_post_meta( $post_id, $meta_key, $session_id );
		}

		/**
		 * Enqueues the Sift script
		 */
		public function add_script() {
			$data = array(
				'session_id' => $this->options->get_session_id(),
				'js_key'     => $this->options->get_js_key(),
			);

			$user_id = $this->options->get_current_user_id();
			if ( null !== $user_id ) {
				$data['user_id'] = $this->options->get_sift_user_id( $user_id );
			}

			$name = 'wc-siftsci';
			$path = plugin_dir_url( __DIR__ ) . 'dist/js/wc-siftsci.js';
			$v    = $this->options->get_version();
			$key  = '_wc_siftsci_js_input_data';
			$data = apply_filters( 'wc_siftscience_js_script_data', $data );

			wp_enqueue_script( $name, $path, array( 'jquery' ), $v, true );
			wp_localize_script( $name, $key, $data );
		}

		/**
		 * Logic to run when the class is destroyed
		 */
		public function shutdown() {
			$this->send_queued_data();
			$this->try_update_order_statuses();
		}

		/**
		 * Adds the login success event
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/login
		 * @param string $username Name of the user.
		 * @param object $user User object.
		 */
		public function login_success( $username, $user ) {
			$data           = $this->login->login_success( $user );
			$this->events[] = $data;
			$this->link_session_to_user( $user->ID );
		}

		/**
		 * Adds the login failure event
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/login
		 * @param object $username User object.
		 */
		public function login_failure( $username ) {
			$this->eventss[] = $this->login->login_failure( $username );
		}

		/**
		 * Adds logout event
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/logout
		 */
		public function logout() {
			$data           = $this->login->logout( $this->saved_user_id );
			$this->events[] = $data;
		}

		/**
		 * Adds account creation event
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/create-account
		 * @param string $user_id User ID.
		 */
		public function create_account( $user_id ) {
			$user           = get_userdata( $user_id );
			$data           = $this->account->create_account( $user_id, $user );
			$this->events[] = $data;
			$this->link_session_to_user( $user->ID );
		}

		/**
		 * Adds event for an account getting updated
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/update-account
		 * @param string $user_id User's ID.
		 * @param array  $old_user_data Old data before change.
		 */
		public function update_account( $user_id, $old_user_data ) {
			$data           = $this->account->update_account( $user_id, $old_user_data );
			$this->events[] = $data;
		}

		/**
		 * Adds event for order creation
		 *
		 * @param string $order_id Order id.
		 */
		public function create_order( $order_id ) {
			if ( ! $this->is_auto_send( $order_id ) ) {
				return;
			}

			$this->order_map[ $order_id ]     = 'create';
			$this->status_checks[ $order_id ] = 1;
		}

		/**
		 * Adds event for order update
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/update-order
		 * @param string $order_id Order ID.
		 */
		public function update_order( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( false === $order ) {
				return;
			}

			if ( ! $this->is_auto_send( $order_id ) ) {
				return;
			}

			if ( ! isset( $this->order_map[ $order_id ] ) ) {
				$this->order_map[ $order_id ]     = 'update';
				$this->status_checks[ $order_id ] = 1;
			}
		}

		/**
		 * Adds the event for the order status update
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/order-status
		 * @param string $order_id Order ID.
		 */
		public function change_order_status( $order_id ) {
			if ( ! $this->is_auto_send( $order_id ) ) {
				return;
			}

			$data = $this->order->update_order_status( $order_id );
			if ( null === $data ) {
				return;
			}

			$this->events[] = $data;
			$this->send_transaction( $order_id );
			$this->status_checks[ $order_id ] = 1;
		}

		/**
		 * Checks if we should auto-send the data of this order
		 *
		 * @param string $order_id Order to check.
		 *
		 * @return bool
		 */
		private function is_auto_send( $order_id ) {
			if ( $this->is_backfilled( $order_id ) ) {
				return true;
			}

			if ( ! $this->options->auto_send_enabled() ) {
				return false;
			}

			$min_value = (float) ( $this->options->get_min_order_value() );

			if ( 0.0 === $min_value ) {
				return true;
			}

			$order = wc_get_order( $order_id );
			if ( false === $order ) {
				return false;
			}

			$order_amount = (float) ( $order->get_total() );
			return $order_amount >= $min_value;
		}

		/**
		 * Sends transaction data to sift
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/transaction
		 * @param string $order_id The Order ID.
		 */
		private function send_transaction( $order_id ) {
			$data           = $this->transaction->create_transaction( $order_id );
			$this->events[] = $data;
		}

		/**
		 * Adds event for item added to cart
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/add-item-to-cart
		 * @param string $cart_item_key The Cart Key.
		 */
		public function add_to_cart( $cart_item_key ) {
			$data           = $this->cart->add_to_cart( $cart_item_key );
			$this->events[] = $data;
		}

		/**
		 * Adds event for item removed from cart
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/remove-item-from-cart
		 * @param string $cart_item_key The key of the cart item.
		 */
		public function remove_from_cart( $cart_item_key ) {
			$data           = $this->cart->remove_from_cart( $cart_item_key );
			$this->events[] = $data;
		}

		/**
		 * Adds the event for when user is link to session id
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/link-session-to-user
		 * @param string $user_id ID of the user.
		 */
		public function link_session_to_user( $user_id ) {
			$data           = $this->account->link_session_to_user( $user_id );
			$this->events[] = $data;
		}

		/**
		 * Sends all queued events
		 */
		public function send_queued_data() {
			foreach ( $this->order_map as $order_id => $type ) {
				$this->send_order_event( $order_id, $type );
			}

			foreach ( $this->events as $event ) {
				if ( null !== $event ) {
					$this->comm->post_event( $event );
				}
			}

			$this->order_map = array();
			$this->events    = array();
		}

		/**
		 * Sends an order event
		 *
		 * @param string $order_id The order ID.
		 * @param string $type The type of event to send.
		 */
		private function send_order_event( string $order_id, string $type = 'create' ) {
			$data = $this->order->create_order( $order_id, $type );
			if ( null !== $data ) {
				$this->events[] = $data;
				$this->send_transaction( $order_id );
				$this->set_backfill( $order_id );
				$this->status_checks[ $order_id ] = 1;
			}
		}

		/**
		 * Check if any orders need their status updated
		 */
		private function try_update_order_statuses() {
			$settings = $this->options->get_order_auto_update_settings();
			$good_to  = $settings['good_to'];
			$bad_to   = $settings['bad_to'];

			// Abort if there are no actions configure.
			if ( ! in_array( 'none', array( $good_to, $bad_to ), true ) ) {
				return;
			}

			foreach ( $this->status_checks as $order_id => $value ) {
				$this->order_status->try_update_order_status( $order_id );
			}
		}
	}

endif;
