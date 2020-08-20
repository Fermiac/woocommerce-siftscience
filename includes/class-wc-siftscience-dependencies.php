<?php
/**
 * This class builds all the components in the order needed for dependencies
 *
 * @package siftscience
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

	/**
	 * Class WC_SiftScience_Dependencies
	 */
	class WC_SiftScience_Dependencies {
		/**
		 * Options service
		 *
		 * @var WC_SiftScience_Options
		 */
		public $options;

		/**
		 * Logging service
		 *
		 * @var WC_SiftScience_Logger
		 */
		public $logger;

		/**
		 * Stats service
		 *
		 * @var WC_SiftScience_Stats
		 */
		public $stats;

		/**
		 * Events service
		 *
		 * @var WC_SiftScience_Events
		 */
		public $events;

		/**
		 * Order service
		 *
		 * @var WC_SiftScience_Orders
		 */
		public $orders;

		/**
		 * Admin functionality
		 *
		 * @var WC_SiftScience_Admin
		 */
		public $admin;

		/**
		 * API service
		 *
		 * @var WC_SiftScience_Api
		 */
		public $api;

		/**
		 * Stripe payment method functionality
		 *
		 * @var WC_SiftScience_Stripe
		 */
		public $stripe;

		/**
		 * WC_SiftScience_Dependencies constructor.
		 *
		 * @param string $version Version of the plugin.
		 */
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
