<?php
/**
 * This class format woocommerce order events into the Sift format
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Api_Order' ) ) :

	require_once 'class-wc-siftscience-options.php';
	require_once 'class-wc-siftscience-logger.php';
	require_once 'class-wc-siftscience-api-items.php';
	require_once 'class-wc-siftscience-api-transaction.php';

	/**
	 * Class WC_SiftScience_Api_Order
	 */
	class WC_SiftScience_Api_Order {
		/**
		 * Options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * Items formatter
		 *
		 * @var WC_SiftScience_Api_Items
		 */
		private $items;

		/**
		 * Transaction formatter
		 *
		 * @var WC_SiftScience_Api_Transaction
		 */
		private $transaction;

		/**
		 * Logging service
		 *
		 * @var WC_SiftScience_Logger
		 */
		private $logger;

		/**
		 * WC_SiftScience_Api_Order constructor.
		 *
		 * @param WC_SiftScience_Api_Items       $items Items formatter.
		 * @param WC_SiftScience_Api_Transaction $transaction Transaction formatter.
		 * @param WC_SiftScience_Options         $options Options service.
		 * @param WC_SiftScience_Logger          $logger Logging service.
		 */
		public function __construct(
				WC_SiftScience_Api_Items $items,
				WC_SiftScience_Api_Transaction $transaction,
				WC_SiftScience_Options $options,
				WC_SiftScience_Logger $logger ) {
			$this->options     = $options;
			$this->items       = $items;
			$this->transaction = $transaction;
			$this->logger      = $logger;
		}

		/**
		 * Create order event
		 *
		 * @param string $order_id Order id.
		 * @param string $type Event type.
		 *
		 * @return array
		 */
		public function create_order( $order_id, $type = 'create' ) {
			$order = wc_get_order( $order_id );
			if ( false === $order ) {
				return null;
			}

			$type           = 'create' === $type ? 'create' : 'update';
			$payment_method = $this->transaction->get_payment_method( $order );

			$data = array(
				'$type'             => 'create' === $type ? '$create_order' : '$update_order',
				'$user_id'          => $this->options->get_user_id( $order ),
				'$order_id'         => $order->get_order_number(),
				'$user_email'       => $order->get_billing_email(),
				'$amount'           => $order->get_total() * 1000000,
				'$currency_code'    => $order->get_currency(),
				'$billing_address'  => $this->create_billing_address( $order ),
				'$shipping_address' => $this->create_shipping_address( $order ),
				'$items'            => $this->items->get_order_items( $order ),
				'$ip'               => $order->get_customer_ip_address(),
				'$payment_methods'  => $payment_method ? array( $payment_method ) : null,
			);

			// only add session id if it exists.
			$session_id = $this->options->get_order_session_id( $order );
			if ( '' !== $session_id ) {
				$data['$session_id'] = $session_id;
			}

			if ( 'create' === $type ) {
				return apply_filters( 'wc_siftscience_create_order', $data, $order );
			} else {
				return apply_filters( 'wc_siftscience_update_order', $data, $order );
			}
		}

		/**
		 * Create formatted object for order status
		 *
		 * @link https://sift.com/developers/docs/v204/curl/events-api/reserved-events/order-status
		 * @param string $order_id The order ID.
		 *
		 * @return array
		 */
		public function update_order_status( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( false === $order ) {
				return null;
			}

			$data = array(
				'$type'        => '$order_status',
				'$user_id'     => $this->options->get_user_id( $order ),
				'$session_id'  => $this->options->get_order_session_id( $order ),
				'$order_id'    => $order->get_order_number(),
				'$description' => 'woo status: ' . $order->get_status(),
			);

			if ( '' === $data['$session_id'] ) {
				unset( $data['$session_id'] );
			}

			$data['$order_status'] = $this->convert_order_status( $order );

			$data = apply_filters( 'wc_siftscience_update_order_status', $data );
			if ( null === $data['$order_status'] ) {
				$this->logger->log_warning( 'Unknown conversion for order status: ' . $order->get_status() );
				return null;
			}
			return $data;
		}

		/**
		 * Map for converting order status
		 *
		 * @var string[]
		 */
		private static $order_status_map = array(
			'completed'  => '$fulfilled',
			'cancelled'  => '$canceled',
			'on-hold'    => '$held',
			'refunded'   => '$returned',
			'processing' => '$approved',
			'pending'    => '$held',
			'failed'     => '$canceled',
		);

		/**
		 * Convert order stats
		 *
		 * @param WC_Order $order The order.
		 *
		 * @return string
		 */
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
		 * Create shipping address formatted data
		 *
		 * @param WC_Order $order Order to get address from.
		 *
		 * @return array
		 */
		private function create_shipping_address( WC_Order $order ) {
			$shipping_address = array(
				'$name'      => $order->get_formatted_shipping_full_name(),
				'$address_1' => $order->get_shipping_address_1(),
				'$address_2' => $order->get_shipping_address_2(),
				'$city'      => $order->get_shipping_city(),
				'$region'    => $order->get_shipping_state(),
				'$country'   => $order->get_shipping_country(),
				'$zipcode'   => $order->get_shipping_postcode(),
			);
			return apply_filters( 'wc_siftscience_create_address', $shipping_address, $order, 'shipping' );
		}

		/**
		 * Create fomatted billing address data
		 *
		 * @param WC_Order $order The order.
		 *
		 * @return array
		 */
		private function create_billing_address( WC_Order $order ) {
			$billing_address = array(
				'$name'      => $order->get_formatted_billing_full_name(),
				'$phone'     => $order->get_billing_phone(),
				'$address_1' => $order->get_billing_address_1(),
				'$address_2' => $order->get_billing_address_2(),
				'$city'      => $order->get_billing_city(),
				'$region'    => $order->get_billing_state(),
				'$country'   => $order->get_billing_country(),
				'$zipcode'   => $order->get_billing_postcode(),
			);
			return apply_filters( 'wc_siftscience_create_address', $billing_address, $order, 'billing' );
		}
	}
endif;
