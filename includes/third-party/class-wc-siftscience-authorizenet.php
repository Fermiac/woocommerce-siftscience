<?php
/**
 * Additional functionality related to the WooCommerce Authorize.net Gateway plugin
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_AuthorizeNet' ) ) :

	require_once dirname( __DIR__ ) . '/class-wc-siftscience-events.php';
	require_once dirname( __DIR__ ) . '/class-wc-siftscience-logger.php';
	require_once dirname( __DIR__ ) . '/class-wc-siftscience-stats.php';

	/**
	 * Class WC_SiftScience_AuthorizeNet Authorize.net payment type management
	 */
	class WC_SiftScience_AuthorizeNet {
		private const ORDER_DATA_KEY = '_wcsiftsci_authnet';

		/**
		 * Logging service
		 *
		 * @var WC_SiftScience_Logger
		 */
		private $logger;

		/**
		 * Stats tracking object
		 *
		 * @var WC_SiftScience_Stats
		 */
		private $stats;

		/**
		 * Events Object
		 *
		 * @var WC_SiftScience_Events
		 */
		private $events;

		/**
		 * WC_SiftScience_AuthorizeNet constructor.
		 *
		 * @param WC_SiftScience_Events $events Sift.com Events API service.
		 * @param WC_SiftScience_Logger $logger Logging service.
		 * @param WC_SiftScience_Stats  $stats Stats sending service.
		 */
		public function __construct(
				WC_SiftScience_Events $events,
				WC_SiftScience_Logger $logger,
				WC_SiftScience_Stats $stats ) {
			$this->logger = $logger;
			$this->stats  = $stats;
			$this->events = $events;
		}

		/**
		 * Stores Authorize.net payment method info for later use in sift requests
		 *
		 * @param WC_Order $order Order to store payment info to.
		 */
		public function authnet_payment( $order ) {
			$this->logger->log_info( "Authorize.net order processed: {$order->get_id()}: " . wp_json_encode( $order->get_meta_data() ) );

			// Check that the card data is available.
			if ( ! isset( $order, $order->payment ) ) {
				$this->logger->log_info( 'Authorize.net exiting because no order data' );
				return;
			}

			$payment = $order->payment;
			$this->logger->log_info( 'authnet payment info: ' . wp_json_encode( $payment ) );
			if ( ! isset( $payment->last_four, $payment->card_type ) ) {
				$this->logger->log_info( 'Authorize.net exiting because payment data is incomplete' );
				return;
			}

			$payment_details = array(
				'$payment_type'               => '$credit_card',
				'$payment_gateway'            => '$authorizenet',
				'$card_last4'                 => $payment->last_four,
				// '$cvv_result_code'            => $card->cvc_check,
				// '$stripe_address_line1_check' => $card->address_line1_check,
				// '$stripe_address_zip_check'   => $card->address_zip_check,
			);

			$data = array( 'payment_method' => $payment_details );
			update_post_meta( $order->get_id(), self::ORDER_DATA_KEY, wp_json_encode( $data ) );
		}

		/**
		 * Modifies the name of the payment method if Stripe is being used
		 *
		 * @param string   $current_method The current payment method value to filter on.
		 * @param WC_Order $order The order being processed.
		 *
		 * @return string The old or modified payment method value
		 */
		public function order_payment_method( $current_method, WC_Order $order ) {
			if ( null !== $current_method || 'stripe' !== $order->get_payment_method() ) {
				return $current_method;
			}

			$meta = $this->get_order_meta( $order );
			if ( null === $meta || ! isset( $meta['payment_method'] ) ) {
				return null;
			}

			return $meta['payment_method'];
		}

		/**
		 * Fetches the metadata of the order
		 *
		 * @param WC_Order $order Order for which to fetch metadata.
		 *
		 * @return array Metadata of the given order
		 */
		private function get_order_meta( WC_Order $order ) {
			$meta = get_post_meta( $order->get_id(), self::ORDER_DATA_KEY, true );
			if ( ! is_string( $meta ) || 0 === strlen( $meta ) ) {
				return null;
			}

			return json_decode( $meta, true );
		}
	}

endif;
