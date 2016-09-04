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
		private $comm;
		private $backfill;
		private $options;

		public function __construct( WC_SiftScience_Comm $comm, WC_SiftScience_Backfill $backfill, WC_SiftScience_Options $options ) {
			$this->comm = $comm;
			$this->backfill = $backfill;
			$this->options = $options;
		}

		public function run() {
			add_action( 'wp_enqueue_scripts', array( $this, 'add_script' ) );
			add_action( 'login_enqueue_scripts', array( $this, 'add_script' ) );
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'create_order' ), 10, 1 );   //new order created

			add_action( 'wp_login', array( $this, 'login_success' ), 10, 2 );
			add_action( 'wp_login_failed', array( $this, 'login_failure' ) );
			add_action( 'user_register', array( $this, 'create_account' ) );
			add_action( 'profile_update', array( $this, 'update_account' ), 10, 2 );

			add_action( 'woocommerce_add_to_cart', array( $this, 'add_to_cart' ) );
			add_action( 'woocommerce_remove_cart_item', array( $this, 'remove_from_cart' ) );

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

			$this->comm->post_event( $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/create-account
		public function create_account( $user_id ) {

			$data = array(
				// Required Fields
				'$type'       => '$create_account',
				'$user_id'    => $user_id,

				// Supported Fields
				'$session_id'       => $this->options->get_session_id(),
				//'$user_email'       => 'bill@gmail.com',
				//'$name'             => 'Bill Jones',
				//'$phone'            => '1-415-555-6040',
				//'$referrer_user_id' => 'janejane101',
				//'$payment_methods'  => array(
					//array(
						//'$payment_type'    => '$credit_card',
						//'$card_bin'        => '542486',
						//'$card_last4'      => '4444'
					//)
				//),
				//'$billing_address'  => array(
					//'$name'         => 'Bill Jones',
					//'$phone'        => '1-415-555-6040',
					//'$address_1'    => '2100 Main Street',
					//'$address_2'    => 'Apt 3B',
					//'$city'         => 'New London',
					//'$region'       => 'New Hampshire',
					//'$country'      => 'US',
					//'$zipcode'      => '03257'
				//),
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
			
			$this->comm->post_event( $data );
			$this->link_session_to_user( $user->ID );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/update-account
		public function update_account( $user_id, $old_user_data ) {
			$data = array(
				// Required Fields
				'$type'       => '$update_account',
				'$user_id'    => $user_id,

				// Supported Fields
				'$changed_password' => $this->is_password_changed( $user_id, $old_user_data ),
				//'$user_email'       => 'bill@gmail.com',
				//'$name'             => 'Bill Jones',
				//'$phone'            => '1-415-555-6040',
				//'$referrer_user_id' => 'janejane102',
				//'$payment_methods'  => array(
					//array(
						//'$payment_type'    => '$credit_card',
						//'$card_bin'        => '542486',
						//'$card_last4'      => '4444'
					//)
				//),
				//'$billing_address'  =>
					//array(
						//'$name'         => 'Bill Jones',
						//'$phone'        => '1-415-555-6041',
						//'$address_1'    => '2100 Main Street',
						//'$address_2'    => 'Apt 3B',
						//'$city'         => 'New London',
						//'$region'       => 'New Hampshire',
						//'$country'      => 'US',
						//'$zipcode'      => '03257'
					//),
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

			$this->comm->post_event( $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/create-order
		public function create_order( $order_id ) {
			$data = array(
				// Required Fields
				'$type'             => '$create_order',
				//'$user_id'          => 'billy_jones_301',

				// Supported Fields
				'$session_id'       => $this->options->get_session_id(),
				'$order_id'         => $order_id,
				//'$user_email'       => 'bill@gmail.com',
				//'$amount'           => 115940000, // $115.94
				//'$currency_code'    => 'USD',
				//'$billing_address'  => array(
					//'$name'         => 'Bill Jones',
					//'$phone'        => '1-415-555-6041',
					//'$address_1'    => '2100 Main Street',
					//'$address_2'    => 'Apt 3B',
					//'$city'         => 'New London',
					//'$region'       => 'New Hampshire',
					//'$country'      => 'US',
					//'$zipcode'      => '03257'
				//),
				//'$payment_methods'  => array(
					//array(
						//'$payment_type'    => '$credit_card',
						//'$payment_gateway' => '$braintree',
						//'$card_bin'        => '542486',
						//'$card_last4'      => '4444'
					//)
				//),
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
				//'$expedited_shipping' => true,
				//'$shipping_method'    => '$physical',
				//'$items'             => array(
					//array(
						//'$item_id'        => '12344321',
						//'$product_title'  => 'Microwavable Kettle Corn=> Original Flavor',
						//'$price'          => 4990000, // $4.99
						//'$upc'            => '097564307560',
						//'$sku'            => '03586005',
						//'$brand'          => 'Peters Kettle Corn',
						//'$manufacturer'   => 'Peters Kettle Corn',
						//'$category'       => 'Food and Grocery',
						//'$tags'           => array('Popcorn', 'Snacks', 'On Sale'),
						//'$quantity'       => 4
					//),
					//array(
						//'$item_id'        => 'B004834GQO',
						//'$product_title'  => 'The Slanket Blanket-Texas Tea',
						//'$price'          => 39990000, // $39.99
						//'$upc'            => '67862114510011',
						//'$sku'            => '004834GQ',
						//'$brand'          => 'Slanket',
						//'$manufacturer'   => 'Slanket',
						//'$category'       => 'Blankets & Throws',
						//'$tags'           => array('Awesome', 'Wintertime specials'),
						//'$color'          => 'Texas Tea',
						//'$quantity'       => 2
					//)
				//),

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

			$this->comm->post_event( $data );
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

			$this->comm->post_event( $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/link-session-to-user
		public function link_session_to_user( $user_id ) {
			$data = array (
				'$type'       => '$link_session_to_user',
				'$user_id'    => $user_id,
				'$session_id' => $this->options->get_session_id(),
			);

			$this->comm->post_event( $data );
		}

		private function is_password_changed( $user_id, $old_user_data ) {
			$user = get_user_by( 'id', $user_id );
			if ( $user === null || $user === false || $old_user_data === null )
				return false;
			return ( isset( $old_user_data->user_pass ) && $user->user_pass !== $old_user_data->user_pass );
		}
	}

endif;
