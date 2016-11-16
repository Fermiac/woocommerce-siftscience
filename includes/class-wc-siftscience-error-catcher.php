<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles the API request ( from the React components )
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( "WC_SiftScience_Error_Catcher" ) ) :

	class WC_SiftScience_Error_Catcher {
		private $subject;
		private $error_handler;

		public function __construct( $subject, $error_handler ) {
			$this->subject = $subject;
			$this->error_handler = $error_handler;
		}

		public function __call( $name, $args ) {
			try {
				return call_user_func_array( array( $this->subject, $name ), $args );
			} catch ( Exception $exception ) {
				call_user_func( $this->error_handler, $exception );
				throw $exception;
			}
		}
	}

endif;
