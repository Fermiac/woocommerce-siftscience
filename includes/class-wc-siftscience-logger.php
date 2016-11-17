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
		private $min_error_level;

		public function __construct( WC_SiftScience_Options $options ) {
			$this->min_error_level = $options->get_log_level();
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
			if ( $status >= $this->min_error_level ) {
				return;
			}

			if ( ! is_string( $message ) ) {
				$message = json_encode( $message );
			}

			error_log( $message );
		}
	}

endif;
