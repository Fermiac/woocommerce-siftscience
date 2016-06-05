<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles communication with SiftScience
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( "WC_SiftScience_Comm" ) ) :
	include_once( 'class-wc-siftscience-options.php' );
	include_once( 'class-wc-siftscience-logger.php' );

    class WC_SiftScience_Comm {
		private $options;
		private $logger;
		private $event_url = 'https://api.siftscience.com/v203/events';
		private $labels_url = 'https://api.siftscience.com/v203/users/{user}/labels';
		private $delete_url = 'https://api.siftscience.com/v203/users/{user}/labels/?api_key={api}';
		private $score_url = 'https://api.siftscience.com/v203/score/{user}/?api_key={api}';

		private $headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		);

		public function __construct( WC_SiftScience_Options $options, WC_SiftScience_Logger $logger ) {
			$this->logger = $logger;
			$this->options = $options;
		}

		public function post_event( $type, $data = array() ) {
			$data['$type'] = $type;
			return $this->post( $data );
		}

		public function post_label( $user_id, $isBad ) {
			$data = array(
				'$api_key' => $this->options->get_api_key(),
				'$is_bad'  => ( $isBad ? 'true' : 'false' ),
			);

			$url = str_replace( '{user}', urlencode( $user_id ), $this->labels_url );
			$args = array(
				'headers' => $this->headers,
				'method'  => 'POST',
				'body'    => $data
			);

			$response = $this->send_request( $url, $args );

			return $response;
		}

		public function delete_label( $user ) {
			$api = $this->options->get_api_key();
			$url = str_replace( '{api}', $api, str_replace( '{user}', $user, $this->delete_url ) );
			$result = $this->send_request( $url, array( 'method' => 'DELETE' ) );

			return $result;
		}

		public function get_user_score( $user_id ) {
			$api = $this->options->get_api_key();
			$url = str_replace( '{api}', $api, str_replace( '{user}', $user_id, $this->score_url ) );

			$response = $this->send_request( $url );

			return json_decode( $response['body'] );
		}

		private function post( $data ) {
			$body = $data;
			$body['$api_key'] = $this->options->get_api_key();
			$body['$session_id'] = $this->options->get_session_id();

			$args = array(
				'headers' => $this->headers,
				'method'  => 'POST',
				'body'    => $body
			);

			return $this->send_request( $this->event_url, $args );
		}

		private function send_request( $url, $args = array() ) {
			if ( ! isset( $args['method'] ) )
				$args['method'] = 'GET';

			$args['timeout'] = 10;

			if ( isset( $args['body'] ) && ! is_string( $args['body'] ) ) {
				$args['body'] = json_encode( $args['body'] );
			}

			$result = wp_remote_request( $url, $args );

			$this->logger->log( "\nSent request to : $url\n" .
				'request details : ' . json_encode( $args ) . "\n" .
				'response details: ' . json_encode( $result ) );

			return $result;
		}
	}

endif;
