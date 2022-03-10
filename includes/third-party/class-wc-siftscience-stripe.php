<?php
/**
 * Additional functionality related to the WooCommerce Stripe Gateway plugin
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Stripe' ) ) :

	require_once dirname( __DIR__ ) . '/class-wc-siftscience-events.php';
	require_once dirname( __DIR__ ) . '/class-wc-siftscience-logger.php';
	require_once dirname( __DIR__ ) . '/class-wc-siftscience-stats.php';

	/**
	 * Class WC_SiftScience_Stripe Stripe payment type management
	 */
	class WC_SiftScience_Stripe {
		private const ORDER_DATA_KEY = '_wcsiftsci_stripe';

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
		 * WC_SiftScience_Stripe constructor.
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
		 * Stores Stripe payment method info for later use in sift requests
		 *
		 * @param object   $request The original request data.
		 * @param WC_Order $order Order to store payment info to.
		 */
		public function stripe_payment( $request, $order ) {
			$this->logger->log_info( "WooSiftStripe detected order: {$order->get_id()}" );

			// Check that the card data is available.
			if ( ! isset( $request, $request->source, $request->source->card ) ) {
				$this->logger->log_info( "Exiting due to missing request data." );
				return;
			}

			$card = $request->source->card;

			// check that the card has all the expected data.
			if ( ! isset( $card, $card->last4, $card->cvc_check, $card->address_line1_check, $card->address_zip_check ) ) {
				$this->logger->log_info( "Exiting due to missing card data." );
				return;
			}

			$payment_details = array(
				'$payment_type'               => '$credit_card',
				'$payment_gateway'            => '$stripe',
				'$card_last4'                 => $card->last4,
				'$cvv_result_code'            => $card->cvc_check,
				'$stripe_address_line1_check' => $card->address_line1_check,
				'$stripe_address_zip_check'   => $card->address_zip_check,
			);

			$data = array( 'payment_method' => $payment_details );
			$this->logger->log_info( "Saving details: " . wp_json_encode( $data ) );
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
