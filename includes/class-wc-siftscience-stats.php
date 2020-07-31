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
		private $last_sent;

		private $send_period = 60 * 60; //send stats once every hour at most

		public function __construct( WC_SiftScience_Options $options, WC_SiftScience_Logger $logger ) {
			$this->options   = $options;
			$this->logger    = $logger;
			$this->last_sent = get_option( WC_SiftScience_Options::STATS_LAST_SENT, 0 );

			$stats       = get_option( WC_SiftScience_Options::STATS, false );
			$this->stats = ( false === $stats ) ? array() : json_decode( $stats, true );
			if ( defined( 'WP_SIFTSCI_STATS_PERIOD' ) ) {
				$this->send_period = WP_SIFTSCI_STATS_PERIOD;
			}
		}

		public function clear_stats() {
			$this->stats = array();
		}

		public function create_timer( $metric ) {
			return array(
				'name'  => $metric,
				'start' => microtime( true ),
			);
		}

		public function save_timer( $timer ) {
			$metric       = $timer[ 'name' ];
			$start_time   = $timer[ 'start' ];
			$current_time = microtime( true );
			$time         = $current_time - $start_time;

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
		}

		public function shutdown() {
			$this->send_stats();
			$this->save_stats();
		}

		private function save_stats() {
			$stats = json_encode( $this->stats );
			update_option( WC_SiftScience_Options::STATS, $stats );
		}

		public function send_error( Exception $error ) {
			if ( ! $this->is_reporting_enabled() ) {
				return;
			}

			$data = array(
				'guid'  => $this->options->get_guid(),
				'type'  => 'error',
				'error' => $error->__toString(),
			);
			$this->send_data( $data );
		}

		private function send_stats() {
			if ( ! ( $this->is_reporting_enabled() && $this->is_time_to_send() ) ) {
				return;
			}

			$data = array(
				'guid'    => $this->options->get_guid(),
				'type'    => 'stats',
				'version' => $this->options->get_version(),
				'stats'   => $this->stats,
			);

			update_option( WC_SiftScience_Options::STATS_LAST_SENT, microtime( true ) );
			$this->send_data( $data );
		}

		private function send_data( $data ) {
			try {
				$timer = $this->create_timer( 'stats_send_data' );
				$this->send_data_inner( $data );
				$this->save_timer( $timer );
			} catch ( Exception $exception ) {
				$this->logger->log_exception( $exception );
			}
		}

		private function send_data_inner( $data ) {
			$url = WC_SiftScience_Options::STATS_API;

			$headers = array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			);

			$request = array(
				'headers' => $headers,
				'method'  => 'POST',
				'body'    => json_encode( $data ),
				'timeout' => 5,
			);

			$result = wp_remote_request( $url, $request );

			$is_success = isset( $result )
			              && ! is_wp_error( $result )
			              && isset( $result[ 'response' ] )
			              && isset( $result[ 'response' ][ 'code' ] )
			              && 200 === $result[ 'response' ][ 'code' ];

			if ( ! $is_success ) {
				$this->logger->log_error( 'Failed to send stats' );
				$this->logger->log_error( $result );
			}
		}

		private function is_reporting_enabled() {
			return 'yes' === get_option( WC_SiftScience_Options::SEND_STATS, 'no' );
		}

		private function is_time_to_send() {
			return ( microtime( true ) - $this->last_sent ) > $this->send_period;
		}
	}

endif;
