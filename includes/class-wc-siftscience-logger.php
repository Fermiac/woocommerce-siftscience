<?php
/**
 * Description: This class handles logging in a central way
 *
 * @package siftscience
 * @author Nabeel Sulieman, Rami Jamleh
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Logger' ) ) :

	require_once dirname( __FILE__ ) . '/class-wc-siftscience-options.php';

	/**
	 * Class WC_SiftScience_Logger
	 */
	class WC_SiftScience_Logger {
		/**
		 * Min error level to log
		 *
		 * @var int
		 */
		private $min_error_level;

		/**
		 * Path to output log
		 *
		 * @var string
		 */
		private $log_path;

		/**
		 * WC_SiftScience_Logger constructor.
		 *
		 * @param WC_SiftScience_Options $options Options service.
		 */
		public function __construct( WC_SiftScience_Options $options ) {
			$this->min_error_level = $options->get_log_level();
			$this->log_path        = dirname( __DIR__ ) . '/debug.log';
		}

		/**
		 * Log info-level message
		 *
		 * @param string $message Message to be logged.
		 */
		public function log_info( $message ) {
			$this->log( 0, $message );
		}

		/**
		 * Log warning-level message
		 *
		 * @param string $message Message to be logged.
		 */
		public function log_warning( $message ) {
			$this->log( 1, $message );
		}

		/**
		 * Log error-level message
		 *
		 * @param string $message Message to be logged.
		 */
		public function log_error( $message ) {
			$this->log( 2, $message );
		}

		/**
		 * Logs message and stack trace of an exception object
		 *
		 * @param Exception $exception The exception to log.
		 */
		public function log_exception( Exception $exception ) {
			$this->log_error( $exception->__toString() );
		}

		/**
		 * The common log funciton that all of the above use
		 *
		 * @param int    $status Error level of the message.
		 * @param string $message The message to log.
		 */
		private function log( $status, $message ) {
			if ( $status < $this->min_error_level ) {
				return;
			}

			if ( ! is_string( $message ) ) {
				$message = wp_json_encode( $message );
			}

			$date = gmdate( 'Y-m-d H:i:s' );

			// @codingStandardsIgnoreStart
			error_log( "[$date] $message\n\n", 3, $this->log_path );
			// @codingStandardsIgnoreEnd
		}
	}

endif;
