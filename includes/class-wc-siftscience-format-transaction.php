<?php
/**
 * This class format woocommerce transaction events into the Sift format
 *
 * @author Nabeel Sulieman
 * @license GPL2
 * @package sift-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Format_Transaction' ) ) :

	require_once 'class-wc-siftscience-options.php';

	/**
	 * Class WC_SiftScience_Format_Transaction
	 */
	class WC_SiftScience_Format_Transaction {
		/**
		 * Options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * WC_SiftScience_Format_Transaction constructor.
		 *
		 * @param WC_SiftScience_Options $options Options service.
		 */
		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		/**
		 * Create transaction object for sift
		 *
		 * @link https://sift.com/developers/docs/v204/curl/events-api/reserved-events/transaction
		 * @param string $order_id The order id.
		 *
		 * @return array
		 */
		public function create_transaction( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( false === $order ) {
				return null;
			}

			$data = array(
				'$type'               => '$transaction',
				'$user_id'            => $this->options->get_user_id( $order ),
				'$order_id'           => $order->get_order_number(),
				'$amount'             => $order->get_total() * 1000000,
				'$currency_code'      => $order->get_currency(),
				'$transaction_type'   => $this->get_transaction_type( $order ),
				'$transaction_status' => $this->get_transaction_status( $order ),
				'$payment_method'     => $this->get_payment_method( $order ),
			);

			// only add session id if it exists.
			$session_id = $this->options->get_order_session_id( $order );
			if ( '' !== $session_id ) {
				$data['$session_id'] = $session_id;
			}

			return apply_filters( 'wc_siftscience_send_transaction', $data, $order );
		}

		/**
		 * Map for converting transaction types
		 *
		 * @var string[]
		 */
		private static $transaction_type_map = array(
			'completed'  => '$sale',
			'cancelled'  => '$sale',
			'on-hold'    => '$sale',
			'refunded'   => '$refund',
			'processing' => '$sale',
			'pending'    => '$sale',
			'failed'     => '$sale',
		);

		/**
		 * Converts the transaction type to sift's spec
		 *
		 * @param WC_Order $order The order to get the transaction type from.
		 *
		 * @return string
		 */
		private function get_transaction_type( WC_Order $order ) {
			$lookup = apply_filters( 'wc_siftscience_transaction_type_lookup', self::$transaction_type_map, $order );

			$wc_status = $order->get_status();

			$type = '$sale';
			if ( isset( $lookup[ $wc_status ] ) ) {
				$type = $lookup[ $wc_status ];
			}

			return apply_filters( 'wc_siftscience_transaction_type', $type, $order );
		}

		/**
		 * Map for converting transactions status
		 *
		 * @var string[]
		 */
		private static $transaction_status_map = array(
			'completed'  => '$success',
			'cancelled'  => '$failure',
			'on-hold'    => '$pending',
			'refunded'   => '$success',
			'processing' => '$pending',
			'pending'    => '$pending',
			'failed'     => '$failure',
		);

		/**
		 * Gets the transactions status for sift
		 *
		 * @param WC_Order $order The order.
		 *
		 * @return string
		 */
		private function get_transaction_status( WC_Order $order ) {
			$lookup = apply_filters( 'wc_siftscience_transaction_status_lookup', self::$transaction_status_map, $order );

			$wc_status = $order->get_status();
			$status    = null;
			if ( isset( $lookup[ $wc_status ] ) ) {
				$status = $lookup[ $wc_status ];
			}

			return apply_filters( 'wc_siftscience_transaction_status', $status, $order );
		}

		/**
		 * Map for converting payment methods
		 *
		 * @var string[][]
		 */
		private static $payment_method_map = array(
			'cod'    => array( '$payment_type' => '$cash' ),
			'bacs'   => array( '$payment_type' => '$electronic_fund_transfer' ),
			'cheque' => array( '$payment_type' => '$check' ),
			'paypal' => array(
				'$payment_type' => '$third_party_processor',
				'$'             => '$paypal',
			),
		);

		/**
		 * Gets the payment method for sift
		 *
		 * @param WC_Order $order The order.
		 *
		 * @return array
		 */
		public function get_payment_method( WC_Order $order ) {
			$method = apply_filters( 'wc_siftscience_order_payment_method', null, $order );
			if ( null !== $method ) {
				return $method;
			}

			$payment_method_id = $order->get_payment_method();

			$lookup = apply_filters( 'wc_siftscience_order_payment_method_lookup', self::$payment_method_map, $order );

			return isset( $lookup[ $payment_method_id ] ) ? $lookup[ $payment_method_id ] : null;
		}
	}

endif;
