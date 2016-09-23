<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class registers all hooks related to events that are reported to SiftScience events.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Events' ) ) :

	include_once( 'class-wc-siftscience-options.php' );
	include_once( 'class-wc-siftscience-comm.php' );

	class WC_SiftScience_Events {
		private $comm;
		private $options;

		public function __construct( WC_SiftScience_Comm $comm, WC_SiftScience_Options $options ) {
			$this->comm = $comm;
			$this->options = $options;
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
			$post_id = $order->post->ID;
			$meta_key = $this->options->get_session_meta_key();
			$session_id = $this->options->get_session_id();
			update_post_meta( $post_id, $meta_key, $session_id );
		}

		public function add_script() {
			$data = array(
				'session_id' => $this->options->get_session_id(),
				'user_id'    => $this->options->get_user_id(),
				'js_key'     => $this->options->get_js_key(),
			);
			error_log( 'WC_SiftScience_Hooks_Events::add_script()' );
			WC_SiftScience_Html::enqueue_script( 'wc-siftsci-js', $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/login
		public function login_success( $username, $user ) {
			$data = array(
				'$type'         => '$login',
				'$user_id'      => $user->ID,
				'$login_status' => '$success'
			);

			$data = apply_filters( 'wc_siftscience_login_success', $data );
			$this->comm->post_event( $data );
			$this->link_session_to_user( $user->ID );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/login
		public function login_failure( $username ) {
			$data = array(
				'$type'         => '$login',
				'$login_status' => '$failure',
			);

			$user = get_user_by( 'login', $username );
			if ( false !== $user ) {
				$data[ '$user_id' ] = $user->ID;
			} else {
				$data[ '$session_id' ] = $this->options->get_session_id();
			}

			$data = apply_filters( 'wc_siftscience_login_failure', $data );
			$this->comm->post_event( $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/create-account
		public function create_account( $user_id ) {
			$user = get_userdata( $user_id );
			$data = array(
				// Required Fields
				'$type'       => '$create_account',
				'$user_id'    => $user_id,

				// Supported Fields
				'$session_id'       => $this->options->get_session_id(),
				'$user_email'       => $user->billing_email,
				'$name'             => $user->billing_first_name . ' ' . $user->billing_last_name,
				'$phone'            => $user->billing_phone,
				//'$referrer_user_id' => 'janejane101',
				//'$payment_methods'  => array(
				//array(
				//'$payment_type'    => '$credit_card',
				//'$card_bin'        => '542486',
				//'$card_last4'      => '4444'
				//)
				//),
				'$billing_address'  => $this->create_address( $user, 'billing' ),
				//'$shipping_address'  => array(
				//'$name'          => 'Bill Jones',
				//'$phone'         => '1-415-555-6041',
				//'$address_1'     => '2100 Main Street',
				//'$address_2'     => 'Apt 3B',
				//'$city'          => 'New London',
				//'$region'        => 'New Hampshire',
				//'$country'       => 'US',
				//'$zipcode'       => '03257'
				//),
				//'$promotions'       => array(
				//array(
				//'$promotion_id'     => 'FriendReferral',
				//'$status'           => '$success',
				//'$referrer_user_id' => 'janejane102',
				//'$credit_point'     => array(
				//'$amount'             => 100,
				//'$credit_point_type'  => 'account karma'
				//)
				//)
				//),

				// '$social_sign_on_type'   => '$twitter',

				// Suggested Custom Fields
				// 'twitter_handle'          => 'billyjones',
				// 'work_phone'              => '1-347-555-5921',
				// 'location'                => 'New London, NH',
				// 'referral_code'           => 'MIKEFRIENDS',
				// 'email_confirmed_status'  => '$pending',
				// 'phone_confirmed_status'  => '$pending'
			);

			$data = apply_filters( 'wc_siftscience_create_account', $data );
			$this->comm->post_event( $data );
			$this->link_session_to_user( $user->ID );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/update-account
		public function update_account( $user_id, $old_user_data ) {
			$user = get_userdata( $user_id );
			$data = array(
				// Required Fields
				'$type'       => '$update_account',
				'$user_id'    => $user_id,

				// Supported Fields
				'$changed_password' => $this->is_password_changed( $user_id, $old_user_data ),
				'$user_email'       => $user->billing_email,
				'$name'             => $user->billing_first_name . ' ' . $user->billing_last_name,
				'$phone'            => $user->billing_phone,
				//'$referrer_user_id' => 'janejane102',
				//'$payment_methods'  => array(
				//array(
				//'$payment_type'    => '$credit_card',
				//'$card_bin'        => '542486',
				//'$card_last4'      => '4444'
				//)
				//),
				'$billing_address'  => $this->create_address( $user, 'billing' ),
				//'$shipping_address' => array(
				//'$name'         => 'Bill Jones',
				//'$phone'        => '1-415-555-6041',
				//'$address_1'    => '2100 Main Street',
				//'$address_2'    => 'Apt 3B',
				//'$city'         => 'New London',
				//'$region'       => 'New Hampshire',
				//'$country'      => 'US',
				//'$zipcode'      => '03257'
				//),

				//'$social_sign_on_type'   => '$twitter',

				// Suggested Custom Fields
				//'email_confirmed_status'   => '$success',
				//'phone_confirmed_status'   => '$success'
			);

			$data = apply_filters( 'wc_siftscience_update_account', $data );
			$this->comm->post_event( $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/create-order
		public function create_order( $order_id ) {
			$order = new WC_Order( $order_id );

			$data = array(
				'$type'             => '$create_order',
				'$user_id'          => $this->get_user_id( $order ),
				'$session_id'       => $this->get_session_id( $order ),
				'$order_id'         => $order->get_order_number(),
				'$user_email'       => $order->billing_email,
				'$amount'           => $order->get_total() * 1000000,
				'$currency_code'    => $order->get_order_currency(),
				'$billing_address'  => $this->create_address( $order, 'billing' ),
				'$shipping_address' => $this->create_address( $order, 'shipping' ),
				'$items'            => $this->create_item_array( $order ),
				'$ip'               => $order->customer_ip_address,
				//'$payment_methods'  => array(
				//array(
				//'$payment_type'    => '$credit_card',
				//'$payment_gateway' => '$braintree',
				//'$card_bin'        => '542486',
				//'$card_last4'      => '4444'
				//)
				//),
				//'$expedited_shipping' => true,
				//'$shipping_method'    => '$physical',
				// For marketplaces, use $seller_user_id to identify the seller
				//'$seller_user_id'     => 'slinkys_emporium',
				//'$promotions'         => array(
				//array(
				//'$promotion_id' => 'FirstTimeBuyer',
				//'$status'       => '$success',
				//'$description'  => '$5 off',
				//'$discount'     => array(
				//'$amount'                   => 5000000,  // $5.00
				//'$currency_code'            => 'USD',
				//'$minimum_purchase_amount'  => 25000000  // $25.00
				//)
				//)
				//),
				// Sample Custom Fields
				//'digital_wallet'      => 'apple_pay', // 'google_wallet', etc.
				//'coupon_code'         => 'dollarMadness',
				//'shipping_choice'     => 'FedEx Ground Courier',
				//'is_first_time_buyer' => false
			);

			$data = apply_filters( 'wc_siftscience_create_order', $data );
			$this->comm->post_event( $data );
			$this->set_backfill( $order_id );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/add-item-to-cart
		public function add_to_cart( $cart_item_key ) {
			$data = array(
				'$type'       =>  '$add_item_to_cart',
				'$session_id' => $this->options->get_session_id(),
				'$user_id'    => '',
				'$item'       => array(
					'$item_id'        => $cart_item_key,
					//'$product_title'  => 'The Slanket Blanket-Texas Tea',
					//'$price'          => 39990000, // $39.99
					//'$currency_code'  => 'USD',
					//'$upc'            => '67862114510011',
					//'$sku'            => '004834GQ',
					//'$brand'          => 'Slanket',
					//'$manufacturer'   => 'Slanket',
					//'$category'       => 'Blankets & Throws',
					//'$tags'           => ['Awesome', 'Wintertime specials'],
					//'$color'          => 'Texas Tea',
					//'$quantity'       => 16,
				)
			);

			$data = apply_filters( 'wc_siftscience_add_to_cart', $data );
			$this->comm->post_event( $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/remove-item-from-cart
		public function remove_from_cart( $cart_item_key ) {
			$data = array(
				'$type'       => '$remove_item_from_cart',
				//'$user_id'    => 'billy_jones_301',
				// Supported Fields
				'$session_id' => $this->options->get_session_id(),
				'$item'       => array(
					'$item_id'        => $cart_item_key,
					//'$product_title'  => 'The Slanket Blanket-Texas Tea',
					//'$price'          => 39990000, // $39.99
					//'$currency_code'  => 'USD',
					//'$quantity'       => 2,
					//'$upc'            => '67862114510011',
					//'$sku'            => '004834GQ',
					//'$brand'          => 'Slanket',
					//'$manufacturer'   => 'Slanket',
					//'$category'       => 'Blankets & Throws',
					//'$tags'           => ['Awesome', 'Wintertime specials'],
					//'$color'          => 'Texas Tea'
				)
			);

			$data = apply_filters( 'wc_siftscience_remove_from_cart', $data );
			$this->comm->post_event( $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/link-session-to-user
		public function link_session_to_user( $user_id ) {
			$data = array (
				'$type'       => '$link_session_to_user',
				'$user_id'    => $user_id,
				'$session_id' => $this->options->get_session_id(),
			);

			$data = apply_filters( 'wc_siftscience_link_session_to_user', $data );
			$this->comm->post_event( $data );
		}

		private function is_password_changed( $user_id, $old_user_data ) {
			$user = get_user_by( 'id', $user_id );
			if ( $user === null || $user === false || $old_user_data === null )
				return false;
			return ( isset( $old_user_data->user_pass ) && $user->user_pass !== $old_user_data->user_pass );
		}

		private function get_session_id( $order ) {
			$session_id = get_post_meta( $order->post->ID, $this->options->get_session_meta_key(), true );
			return false === $session_id ? $this->options->get_session_id() : $session_id;
		}

		private function get_user_id( WC_Order $order ) {
			if ( $order->get_user_id() === 0 ) {
				return 'SINGLE_ORDER_' . $order->post->ID;
			}

			return 'REGISTERED_USER_' . $order->get_user_id();
		}

		/**
		 * @param $order
		 * @param string $type
		 * '$address'  => array(
		 *		'$name'         => 'Bill Jones',
		 *		'$phone'        => '1-415-555-6041',
		 *		'$address_1'    => '2100 Main Street',
		 *		'$address_2'    => 'Apt 3B',
		 *		'$city'         => 'New London',
		 *		'$region'       => 'New Hampshire',
		 *		'$country'      => 'US',
		 *		'$zipcode'      => '03257'
		 *	),
		 * @return array
		 */
		private function create_address( $order, $type = 'shipping' ) {
			return array(
				'$name'      => $this->get_order_param( $order, $type, '_first_name' )
				                . ' ' . $this->get_order_param( $order, $type, '_last_name' ),
				'$phone'     => $this->get_order_param( $order, $type, '_phone' ),
				'$address_1' => $this->get_order_param( $order, $type, '_address_1' ),
				'$address_2' => $this->get_order_param( $order, $type, '_address_2' ),
				'$city'      => $this->get_order_param( $order, $type, '_city' ),
				'$region'    => $this->get_order_param( $order, $type, '_state' ),
				'$country'   => $this->get_order_param( $order, $type, '_country' ),
				'$zipcode'   => $this->get_order_param( $order, $type, '_postcode' ),
			);
		}

		private function get_order_param( $order, $type, $param ) {
			$key = $type . $param;
			return $order->$key;
		}

		private function create_item_array( $order ) {
			$item_arr = array();
			foreach ( $order->get_items() as $wc_item ) {
				array_push( $item_arr, $this->create_item( $wc_item ) );
			}
			return $item_arr;
		}

		/**
		 *	array(
		 * 		'$item_id'        => 'B004834GQO',
		 *		'$product_title'  => 'The Slanket Blanket-Texas Tea',
		 *		'$price'          => 39990000, // $39.99
		 *		'$upc'            => '67862114510011',
		 *		'$sku'            => '004834GQ',
		 *		'$brand'          => 'Slanket',
		 *		'$manufacturer'   => 'Slanket',
		 *		'$category'       => 'Blankets & Throws',
		 *		'$tags'           => array('Awesome', 'Wintertime specials'),
		 *		'$color'          => 'Texas Tea',
		 *		'$quantity'       => 2
		 *	)
		 *
		 * @param $wc_item
		 *
		 * @return array
		 */
		private function create_item( $wc_item ) {
			return array(
				'$item_id'       => $wc_item['product_id'],
				'$product_title' => $wc_item['name'],
				'$price'         => $wc_item['line_subtotal'],
				'$quantity'      => $wc_item['qty'],
			);
		}

	}

endif;