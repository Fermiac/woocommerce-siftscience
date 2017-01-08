<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles logging in a central way
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( "WC_SiftScience_Logger" ) ) :

	require_once( dirname( __FILE__ ) . '/class-wc-siftscience-options.php' );

	class WC_SiftScience_Logger {
		private $min_error_level;
		private $log_path;

		public function __construct( WC_SiftScience_Options $options ) {
			$this->min_error_level = $options->get_log_level();
			$this->log_path = dirname( __DIR__ ) . '/debug.log';
		}

		public function log_info( $message ) {
			$this->log( 0, $message );
		}

		public function log_warning( $message ) {
			$this->log( 1, $message );
		}

		public function log_error( $message ) {
			$this->log( 2, $message );
		}

		public function log_exception( Exception $exception ) {
			$this->log_error( $exception->__toString() );
		}

		private function log( $status, $message ) {
			if ( $status < $this->min_error_level ) {
				return;
			}

			if ( ! is_string( $message ) ) {
				$message = json_encode( $message );
			}

			$date = date( 'Y-m-d H:i:s' );
			error_log( "[$date] $message\n\n", 3, $this->log_path );
		}
	}

endif;
