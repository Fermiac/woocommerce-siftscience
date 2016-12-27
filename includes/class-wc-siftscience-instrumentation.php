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
		private $subject;
		private $logger;
		private $stats;
		private $prefix;

		public function __construct( $subject, $prefix, WC_SiftScience_Logger $logger, WC_SiftScience_Stats $stats ) {
			$this->subject = $subject;
			$this->logger = $logger;
			$this->stats = $stats;
			$this->prefix = $prefix;
		}

		public function __call( $name, $args ) {
			$metric = "{$this->prefix}_{$name}";
			$timer = $this->stats->create_timer( $metric );
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

		public function __get( $name ) {
			return $this->subject->$name;
		}
	}

endif;
