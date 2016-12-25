<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles sending anonymous stats and error messages
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( "WC_SiftScience_Stats" ) ) :

	class WC_SiftScience_Stats {
		private $logger;
		private $options;
		private $stats;

		public function __construct( WC_SiftScience_Options $options, WC_SiftScience_Logger $logger ) {
			$this->options = $options;
			$this->logger = $logger;

			$stats = get_option( WC_SiftScience_Options::$stats, false );
			if ( false === $stats ) {
				$stats = "{}";
			}

			// get the stats as an array, not a class
			$this->stats = json_decode( $stats, true );
		}

		public function increment_value( $metric, $value = null ) {
			if ( null === $value ) {
				$value = 1;
			}

			if ( ! isset( $this->stats[ $metric ] ) ) {
				$this->stats[ $metric ] = array();
			}

			if ( ! isset( $this->stats[ $metric ][ 'count' ] ) ) {
				$this->stats[ $metric ][ 'count' ] = 0;
			}

			$this->stats[ $metric ][ 'count' ] += $value;

			$this->save_stats();
		}

		public function create_timer( $metric ) {
			return array(
				'name' => $metric,
				'start' => microtime(),
			);
		}

		public function save_timer( $timer ) {
			$metric = $timer[ 'name' ];
			$time = microtime() - $timer[ 'start' ];

			if ( ! isset( $this->stats[ $metric ] ) ) {
				$this->stats[ $metric ] = array();
			}

			if ( ! isset( $this->stats[ $metric ][ 'count' ] ) ) {
				$this->stats[ $metric ][ 'count' ] = 0;
			}

			if ( ! isset( $this->stats[ $metric ][ 'time' ] ) ) {
				$this->stats[ $metric ][ 'time' ] = 0;
			}

			$this->stats[ $metric ][ 'time' ] += $time;
			$this->stats[ $metric ][ 'count' ] += 1;

			$this->save_stats();
		}

		private function save_stats() {
			update_option( WC_SiftScience_Options::$stats, json_encode( $this->stats ) );
		}

		private function send_stats() {
			try{
				$this->send_stats_inner();
			} catch ( Exception $exception ) {
				$this->logger->log_error( $exception->__toString() );
			}
		}

		private function send_stats_inner() {
			$url = WC_SiftScience_Options::$stats_api;

			$headers = array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			);

			$request = array(
				'headers' => $headers,
				'method'  => 'POST',
				'body'    => json_encode( $this->stats ),
				'timeout' => 10,
			);

			$result = wp_remote_request( $url, $request );

			if ( ! ( isset( $result ) && isset( $result[ 'response' ] ) && isset( $result[ 'response' ] ) ) ) {
				$this->logger->log_error( 'Failed to send stats' );
				$this->logger->log_error( $result );
			}
		}
	}

endif;
