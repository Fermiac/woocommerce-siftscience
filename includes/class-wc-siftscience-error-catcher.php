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

	include_once 'class-wc-siftscience-logger.php';

	class WC_SiftScience_Error_Catcher {
		private $subject;
		private $logger;

		public function __construct( $subject, WC_SiftScience_Logger $logger ) {
			$this->subject = $subject;
			$this->logger = $logger;
		}

		public function __call( $name, $args ) {
			try {
				return call_user_func_array( array( $this->subject, $name ), $args );
			} catch ( Exception $exception ) {
				$this->logger->log_error( $exception->__toString() );
				throw $exception;
			}
		}
	}

endif;
