<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class wraps a class and logs any exceptions thrown and collects various metrics
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( "WC_SiftScience_Error_Catcher" ) ) :

	include_once 'class-wc-siftscience-logger.php';
	include_once 'class-wc-siftscience-stats.php';

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
			try {
				$metric = "{$this->prefix}_{$name}";
				$timer = $this->stats->create_timer( $metric );
				$result = call_user_func_array( array( $this->subject, $name ), $args );
				$this->stats->save_timer( $timer );
				return $result;
			} catch ( Exception $exception ) {

				$this->logger->log_error( $exception->__toString() );
				throw $exception;
			}
		}

		public function __get( $name ) {
			return $this->subject->$name;
		}
	}

endif;
