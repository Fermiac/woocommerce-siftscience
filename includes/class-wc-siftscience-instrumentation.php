<?php
/**
 * This class wraps a class and logs any exceptions thrown and collects various metrics
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Instrumentation' ) ) :

	require_once 'class-wc-siftscience-logger.php';
	require_once 'class-wc-siftscience-stats.php';

	/**
	 * This class wraps another class and:
	 * - measures performance
	 * - automatically logs errors
	 *
	 * Class WC_SiftScience_Instrumentation
	 */
	class WC_SiftScience_Instrumentation {
		/**
		 * The object that is being wrapped by this class
		 *
		 * @var object
		 */
		private $subject;

		/**
		 * Logging service
		 *
		 * @var WC_SiftScience_Logger
		 */
		private $logger;

		/**
		 * Stats service
		 *
		 * @var WC_SiftScience_Stats
		 */
		private $stats;

		/**
		 * Prefix to add to all subjects
		 *
		 * @var string
		 */
		private $prefix;

		/**
		 * WC_SiftScience_Instrumentation constructor.
		 *
		 * @param object                $subject The object being instrumented.
		 * @param WC_SiftScience_Logger $logger Logging service.
		 * @param WC_SiftScience_Stats  $stats Stats sending service.
		 */
		public function __construct( $subject, WC_SiftScience_Logger $logger, WC_SiftScience_Stats $stats ) {
			$this->subject = $subject;
			$this->logger  = $logger;
			$this->stats   = $stats;
			$this->prefix  = get_class( $subject );
		}

		/**
		 * This function acts as a wrapper to all calls to the subject
		 *
		 * @param string $name Name of function being called.
		 * @param array  $args Arguments to pass to the function.
		 *
		 * @return mixed     The return value of the subject function
		 * @throws Exception Any errors thrown by the subject method.
		 */
		public function __call( $name, $args ) {
			$metric      = "{$this->prefix}::{$name}";
			$timer       = $this->stats->create_timer( $metric );
			$error_timer = $this->stats->create_timer( "error_$metric" );

			try {
				$result = call_user_func_array( array( $this->subject, $name ), $args );
				$this->stats->save_timer( $timer );
				return $result;
			} catch ( Exception $exception ) {
				$this->stats->save_timer( $error_timer );
				$this->stats->send_error( $exception );
				$this->logger->log_exception( $exception );
				throw $exception;
			}
		}

		/**
		 * Wraps gets to members of the class
		 *
		 * @param string $name The name of the member to get.
		 *
		 * @return mixed The member value from the subject class
		 */
		public function __get( $name ) {
			return $this->subject->$name;
		}
	}

endif;
