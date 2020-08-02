<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class format woocommerce transaction events into the Sift format.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Format_Transaction' ) ) :

	require_once 'class-wc-siftscience-options.php';

	class WC_SiftScience_Format_Transaction {
		private $options;

		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/transaction
		public function create_transaction( $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order === false ) {
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

			// only add session id if it exists
			$session_id = $this->options->get_order_session_id( $order );
			if ( $session_id !== '' ) {
				$data[ '$session_id' ] = $session_id;
			}

			return apply_filters( 'wc_siftscience_send_transaction', $data, $order );
		}

		private static $transaction_type_map = array(
			'completed' => '$sale',
			'cancelled' => '$sale',
			'on-hold' => '$sale',
			'refunded' => '$refund',
			'processing' => '$sale',
			'pending' => '$sale',
			'failed' => '$sale',
		);

		private function get_transaction_type( WC_Order $order ) {
			$lookup = apply_filters( 'wc_siftscience_transaction_type_lookup', self::$transaction_type_map, $order );
			$wc_status = $order->get_status();
			$type = '$sale';
			if ( isset( $lookup[ $wc_status ] ) ) {
				$type = $lookup[ $wc_status ];
			}

			return apply_filters( 'wc_siftscience_transaction_type', $type, $order );
		}

		private static $transaction_status_map = array(
			'completed' => '$success',
			'cancelled' => '$failure',
			'on-hold' => '$pending',
			'refunded' => '$success',
			'processing' => '$pending',
			'pending' => '$pending',
			'failed' => '$failure',
		);

		private function get_transaction_status( WC_Order $order ) {
			$lookup = apply_filters( 'wc_siftscience_transaction_status_lookup', self::$transaction_status_map, $order );
			$wc_status = $order->get_status();
			$status = null;
			if ( isset( $lookup[ $wc_status ] ) ) {
				$status = $lookup[ $wc_status ];
			}

			return apply_filters( 'wc_siftscience_transaction_status', $status, $order );
		}

		private static $payment_method_map = array(
			'cod' => array( '$payment_type' => '$cash' ),
			'bacs' => array( '$payment_type' => '$electronic_fund_transfer' ),
			'cheque' => array( '$payment_type' => '$check' ),
			'paypal' => array( '$payment_type' => '$third_party_processor', '$' => '$paypal' ),
		);

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