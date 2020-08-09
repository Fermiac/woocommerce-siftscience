<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class wraps a class and logs any exceptions thrown and collects various metrics
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( "WC_SiftScience_Instrumentation" ) ) :

	require_once( 'class-wc-siftscience-logger.php' );
	require_once( 'class-wc-siftscience-stats.php' );

	class WC_SiftScience_Instrumentation {
		private $_subject;
		private $_logger;
		private $_stats;
		private $_prefix;

		public function __construct( $subject, WC_SiftScience_Logger $logger, WC_SiftScience_Stats $stats ) {
			$this->_subject = $subject;
			$this->_logger = $logger;
			$this->_stats = $stats;
			$this->_prefix = get_class( $subject );
		}

		public function __call( $name, $args ) {
			$metric = "{$this->_prefix}::{$name}";
			$timer = $this->_stats->create_timer( $metric );
			$error_timer = $this->_stats->create_timer( "error_$metric" );
			try {
				$result = call_user_func_array( array( $this->_subject, $name ), $args );
				$this->_stats->save_timer( $timer );
				return $result;
			} catch ( Exception $exception ) {
				$this->_stats->save_timer( $error_timer );
				$this->_stats->send_error( $exception );
				$this->_logger->log_exception( $exception );
				throw $exception;
			}
		}

		public function __get( $name ) {
			return $this->_subject->$name;
		}
	}

endif;
