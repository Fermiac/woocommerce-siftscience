<?php
/**
 * Additional functionality related to the WooCommerce Stripe Gateway plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( "WC_SiftScience_Stripe" ) ) :

	require_once dirname( dirname( __FILE__ ) ) . '/class-wc-siftscience-events.php';
	require_once dirname( dirname( __FILE__ ) ) . '/class-wc-siftscience-logger.php';
	require_once dirname( dirname( __FILE__ ) ) . '/class-wc-siftscience-stats.php';

class WC_SiftScience_Stripe {
	private static $order_data_key = '_wcsiftsci_stripe';
	private $logger;
	private $stats;
	private $events;

	public function __construct( WC_SiftScience_Events $events, WC_SiftScience_Logger $logger, WC_SiftScience_Stats $stats ) {
		$this->logger = $logger;
		$this->stats = $stats;
		$this->events = $events;
	}

	/**
	 * Stores Stripe payment method info for later use in sift science requests
	 *
	 * @param $request Object
	 * @param $order WC_Order
	 */
	public function stripe_payment( $request, $order ) {
		if ( ! ( isset( $request ) && isset( $request->source ) ) ) {
			return;
		}

		$source = $request->source;

		$payment_details = array(
			'$payment_type'  => $this->convert_payment_type( $source ),
			'$payment_gateway' => '$stripe',
			'$card_last4'      => $source->last4,
			'$cvv_result_code' => $source->cvc_check,
			'$stripe_address_line1_check' => $source->address_line1_check,
			'$stripe_address_zip_check'   => $source->address_zip_check
		);

		$data = array( 'payment_method' => $payment_details );
		update_post_meta( $order->id, self::$order_data_key, json_encode( $data ) );
	}

	public function order_payment_method( $current_method, WC_Order $order ) {
		if ( null !== $current_method || 'stripe' !== $order->payment_method ) {
			return $current_method;
		}

		$meta = $this->get_order_meta( $order );
		if ( null === $meta || ! isset( $meta[ 'payment_method' ])) {
			return $current_method;
		}

		return $meta[ 'payment_method' ];
	}

	private function get_order_meta( WC_Order $order ) {
		$meta = get_post_meta( $order->id, self::$order_data_key, true );
		if ( ! is_string( $meta ) || 0 === strlen( $meta ) ) {
			return null;
		}

		return json_decode( $meta, true );
	}

	private function convert_payment_type( $source ) {
		switch( $source->object ) {
			case 'card':
				return '$credit_card';
			default:
				throw new Exception( 'Unknown Stripe Source Payment Type: ' . $source->object );
		}
	}
}

endif;
