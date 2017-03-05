<?php
/**
 * Additional functionality related to the WooCommerce Stripe Gateway plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( "WC_SiftScience_Stripe" ) ) :

	require_once dirname( dirname( __FILE__ ) ) . '/class-wc-siftscience-logger.php';
	require_once dirname( dirname( __FILE__ ) ) . '/class-wc-siftscience-logger.php';

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

	public function add_hooks() {
		add_action( 'wc_gateway_stripe_process_payment', array( $this, 'stripe_payment' ), 10, 2 );
		add_filter( 'wc_siftscience_create_order', array( $this, 'add_payment_methods' ), 10, 2 );
		add_filter( 'wc_siftscience_update_order', array( $this, 'add_payment_methods' ), 10, 2 );
	}

	/**
	 * Stores Stripe payment method info for later use in sift science requests
	 *
	 * @param $request Object
	 * @param $order WC_Order
	 */
	public function stripe_payment( $request, $order ) {
		try {
			$this->stripe_payment_internal( $request, $order );
			$this->events->update_order( $order->id );
		} catch ( Exception $exception ) {
			$this->logger->log_exception( $exception );
			$this->stats->send_error( $exception );
		}
	}

	/**
	 * @param $data array
	 * @param $order WC_Order
	 */
	public function add_payment_methods( $data, WC_Order $order ) {
		error_log('checking...');
		$meta = $this->get_order_meta( $order );
		if ( null !== $meta && isset( $meta[ '$payment_methods' ] ) ) {
			error_log('got it');
			$data[ '$payment_methods' ] = $meta[ '$payment_methods' ];
		}

		return $data;
	}

	private function get_order_meta( WC_Order $order ) {
		if ( 'stripe' !== $order->payment_method ) {
			error_log('not a stripe method');
			return null;
		}

		$meta = get_post_meta( $order->id, self::$order_data_key, true );
		if ( ! is_string( $meta ) || 0 === strlen( $meta ) ) {
			error_log('no metadata');
			return null;
		}

		return json_decode( $meta, true );
	}

	private function stripe_payment_internal( $request, $order ) {
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

		$data = array( '$payment_methods' => array( $payment_details ) );
		error_log('saving');
		update_post_meta( $order->id, self::$order_data_key, json_encode( $data ) );
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
