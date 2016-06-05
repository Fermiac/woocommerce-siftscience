<?php

/*
 * Author: Nabeel Sulieman
 * Description: Asynchronous API for reporting events to SiftScience
 * License: GPL2
 */

include_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

if ( ! is_super_admin() ) {
	http_response_code( 401 );
	echo json_encode( array( 'error' => 'not allowed' ) );
	die;
}

if ( ! class_exists( "WC_SiftScience_Event" ) ) :

	include_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );
	include_once( 'includes/class-wc-siftscience-comm.php' );
	include_once( 'includes/class-wc-siftscience-backfill.php' );
	include_once( 'includes/class-wc-siftscience-eventdata.php' );
	include_once( 'includes/class-wc-siftscience-nonce.php' );
	include_once( 'includes/class-wc-siftscience-logger.php' );

	class WC_SiftScience_Event {
		private $comm;
		private $log;
		private $options;

		public function __construct() {
			$options = new WC_SiftScience_Options();
			$this->options = $options;
			$this->log = new WC_SiftScience_Logger();
			$this->comm = new WC_SiftScience_Comm( $options, $this->log );
		}

		public function process_request() {
			$method = filter_input( INPUT_SERVER, 'REQUEST_METHOD' );
			if ( $method !== 'POST' ) {
				http_response_code( 400 );
				return array( 'error' => 'invalid method' );
			}

			$data = filter_input_array( INPUT_POST );
			if ( ! isset( $data['event'], $data['nonce'] ) ) {
				http_response_code( 400 );
				return array( 'error' => 'invalid data' );
			}

			if ( ! $this->check_nonce( $data ) ) {
				http_response_code( 403 );
				return array( 'error' => 'action expired' );
			}

			$result = $this->handle_request( $data );
			$this->log->log( "\nEvent Request: " . json_encode( $data ) . "\nEvent Response: " . json_encode( $result ) );
			return $result;
		}

		private function check_nonce( $data ) {
			$action = WC_SiftScience_Nonce::action( $data );
			return ( wp_verify_nonce( $data['nonce'], $action ) === 1 );
		}

		private function handle_request( $data ) {
			$event_data = new WC_SiftScience_EventData( $data, $this->options );
			$result = $event_data->get( $data );
			if ( $result === false ) {
				http_response_code( 403 );
				return array( 'error' => 'invalid event' );
			}

			$this->comm->post_event( $result['$type'], $result['$user_id'], $result );
			return $this->success_result();
		}

		private static function success_result() {
			return array( 'status' => 'success' );
		}
	}

	try {
		$wc_siftsci_event_result = ( new WC_SiftScience_Event() )->process_request();
		echo json_encode( $wc_siftsci_event_result );
	} catch ( Exception $e ) {
		http_response_code( 500 );
		throw $e;
	}

endif;
