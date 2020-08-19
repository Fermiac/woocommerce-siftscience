<?php
/**
 * @package sift-for-woo
 * @author Nabeel Sulieman, Rami Jamleh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Format_Order' ) ) :

	require_once dirname( __FILE__ ) . '/class-wc-siftscience-options.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-logger.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-stats.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-instrumentation.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-comm.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-html.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-format.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-api.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-events.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-admin.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-orders.php';
	require_once dirname( __FILE__ ) . '/third-party/class-wc-siftscience-stripe.php';

	class WC_SiftScience_Dependencies {
		public $options;
		public $logger;
		public $stats;
		public $events;
		public $orders;
		public $admin;
		public $api;
		public $stripe;

		public function __construct( $version ) {
			$options = new WC_SiftScience_Options( $version );
			$logger  = new WC_SiftScience_Logger( $options );
			$stats   = new WC_SiftScience_Stats( $options, $logger );
			$comm    = new WC_SiftScience_Comm( $options, $logger );
			$html    = new WC_SiftScience_Html();

			// Construct formatting classes.
			$transaction = new WC_SiftScience_Format_Transaction( $options );
			$items       = new WC_SiftScience_Format_Items( $options );
			$login       = new WC_SiftScience_Format_Login( $options );
			$account     = new WC_SiftScience_Format_Account( $options );
			$order       = new WC_SiftScience_Format_Order( $items, $transaction, $options, $logger );
			$cart        = new WC_SiftScience_Format_Cart( $options );
			$format      = new WC_SiftScience_Format( $transaction, $items, $login, $account, $order, $cart );

			$events = new WC_SiftScience_Events( $comm, $options, $format, $logger );
			$orders = new WC_SiftScience_Orders( $options );
			$admin  = new WC_SiftScience_Admin( $options, $comm, $html, $logger, $stats );
			$api    = new WC_SiftScience_Api( $comm, $events, $options, $logger, $stats );
			$stripe = new WC_SiftScience_Stripe( $events, $logger, $stats );

			$this->options = $options;
			$this->logger  = $logger;
			$this->stats   = $stats;
			$this->events  = $events;
			$this->orders  = $orders;
			$this->admin   = $admin;
			$this->api     = $api;
			$this->stripe  = $stripe;
		}
	}
endif;
