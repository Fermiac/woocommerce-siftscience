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

	class WC_SiftScience_Logger {
		private $error_level;

		public function __construct() {
			$this->error_level = 2;
			if ( defined( 'WC_SIFTSCIENCE_LOG_LEVEL') ) {
				$this->error_level = WC_SIFTSCIENCE_LOG_LEVEL;
			}
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

		private function log( $status, $message ) {
			if ( $status < $this->error_level ) {
				return;
			}

			if ( ! is_string( $message ) ) {
				$message = json_encode( $message );
			}

			error_log( $message );
		}
	}

endif;
