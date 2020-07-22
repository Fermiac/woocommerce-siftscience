<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class format woocommerce order events into the SiftScience format.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Format_Order' ) ) :

	require_once( 'class-wc-siftscience-options.php' );
	require_once( 'class-wc-siftscience-logger.php' );
	require_once( 'class-wc-siftscience-format-items.php' );
	require_once( 'class-wc-siftscience-format-transaction.php' );

	class WC_SiftScience_Format_Order {
		private $options;
		private $items;
		private $transaction;
		private $logger;

		public function __construct( WC_SiftScience_Format_Items $items, WC_SiftScience_Format_Transaction $transaction, WC_SiftScience_Options $options, WC_SiftScience_Logger $logger ) {
			$this->options = $options;
			$this->items = $items;
			$this->transaction = $transaction;
			$this->logger = $logger;
		}

		public function create_order( $order_id, $type = 'create' ) {
			$order = wc_get_order( $order_id );
			if ( false === $order ) {
				return null;
			}

			$type = 'create' === $type ? 'create' : 'update';
			$payment_method = $this->transaction->get_payment_method( $order );
			$data = array(
				'$type'             => 'create' === $type ? '$create_order' : '$update_order',
				'$user_id'          => $this->options->get_user_id( $order ),
				'$session_id'       => $this->options->get_order_session_id( $order ),
				'$order_id'         => $order->get_order_number(),
				'$user_email'       => $order->get_billing_email(),
				'$amount'           => $order->get_total() * 1000000,
				'$currency_code'    => $order->get_currency(),
				'$billing_address'  => $this->create_address( $order, 'billing' ),
				'$shipping_address' => $this->create_address( $order, 'shipping' ),
				'$items'            => $this->items->get_order_items( $order ),
				'$ip'               => $order->customer_ip_address,
				'$payment_methods'  => $payment_method ? array( $payment_method ) : null,
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

			if ( 'create' === $type ) {
				return apply_filters( "wc_siftscience_create_order", $data, $order );
			} else {
				return apply_filters( "wc_siftscience_update_order", $data, $order );
			}
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/order-status
		public function update_order_status( $order_id ) {
			$order = new WC_Order( $order_id );
			$data = array(
				'$type'             => '$order_status',
				'$user_id'          => $this->options->get_user_id( $order ),
				'$session_id'       => $this->options->get_order_session_id( $order ),
				'$order_id'         => $order->get_order_number(),
				'$description'      => 'woo status: ' . $order->get_status(),
			);

			$data[ '$order_status' ] = $this->convert_order_status( $order );
			$data = apply_filters( 'wc_siftscience_update_order_status', $data );
			if ( null === $data[ '$order_status' ] ) {
				$this->logger->log_warning( 'Unknown conversion for order status: ' . $order->get_status() );
				return null;
			}
			return $data;
		}

		private static $order_status_map = array(
			'completed' => '$fulfilled',
			'cancelled' => '$canceled',
			'on-hold' => '$held',
			'refunded' => '$returned',
			'processing' => '$approved',
			'pending' => '$held',
			'failed' => '$canceled',
		);

		private function convert_order_status( WC_Order $order ) {
			$status = $order->get_status();
			$lookup = apply_filters( 'wc_siftscience_order_status_lookup', self::$order_status_map, $order );
			if ( ! isset( $lookup[ $status ] ) ) {
				return null;
			}

			$status = $lookup[ $status ];
			return apply_filters( 'wc_siftscience_order_status', $status, $order );
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

		//this method is doing naming variables -not secured-
		private function create_address( WC_Order $order, $type = 'shipping' ) {
			if( $type == 'billing' ){
				$address_object = array(
				'$name'      => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'$phone'     => $order->get_billing_phone(),
				'$address_1' => $order->get_billing_address_1(),
				'$address_2' => $order->get_billing_address_2(),
				'$city'      => $this->get_order_param( $order, $type, '_city' ),
				'$region'    => $this->get_order_param( $order, $type, '_state' ),
				'$country'   => $this->get_order_param( $order, $type, '_country' ),
				'$zipcode'   => $this->get_order_param( $order, $type, '_postcode' ),
				);

			} elseif($type == 'shipping'){
				$address_object = array(
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
			
			$address_object = apply_filters( 'wc_siftscience_create_address', $address_object, $order, $type );
			return $address_object;
		}

		//this method needs to be deleted 
		private function get_order_param( $order, $type, $param ) {
			$key = $type . $param;
			return $order->$key;
		}
	}

endif;