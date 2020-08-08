<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles communication with Sift
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( "WC_SiftScience_Comm" ) ) :
	require_once( 'class-wc-siftscience-options.php' );

	class WC_SiftScience_Comm {
		private const EVENT_URL = 'https://api.sift.com/v204/events';
		private const LABELS_URL = 'https://api.sift.com/v204/users/{user}/labels';
		private const DELETE_URL = 'https://api.sift.com/v204/users/{user}/labels/?api_key={api}&abuse_type=payment_abuse';
		private const SCORE_URL = 'https://api.sift.com/v204/score/{user}/?api_key={api}';

		private $_options;
		private $_logger;

		private $headers = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		);

		public function __construct( WC_SiftScience_Options $options, WC_SiftScience_Logger $logger ) {
			$this->_options = $options;
			$this->_logger = $logger;
		}

		public function post_event( $data ) {
			$data[ '$api_key' ] = $this->_options->get_api_key();

			$args = array(
				'headers' => $this->headers,
				'method'  => 'POST',
				'body'    => $data
			);

			return $this->send_request( self::EVENT_URL, $args );
		}

		public function post_label( $user_id, $isBad ) {
			$data = array(
				'$api_key'    => $this->_options->get_api_key(),
				'$is_bad'     => ( $isBad ? 'true' : 'false' ),
				'$abuse_type' => 'payment_abuse',
			);

			$url = str_replace( '{user}', urlencode( $user_id ), self::LABELS_URL );
			$args = array(
				'headers' => $this->headers,
				'method'  => 'POST',
				'body'    => $data
			);

			return $this->send_request( $url, $args );
		}

		public function delete_label( $user ) {
			$api = $this->_options->get_api_key();
			$url = str_replace( '{api}', $api, str_replace( '{user}', $user, self::DELETE_URL ) );
			return $this->send_request( $url, array( 'method' => 'DELETE' ) );
		}

		public function get_user_score( $user_id ) {
			$api = $this->_options->get_api_key();
			$url = str_replace( '{api}', $api, str_replace( '{user}', $user_id, self::SCORE_URL ) );

			$response = $this->send_request( $url );

			return json_decode( $response['body'] );
		}

		private function send_request( $url, $args = array() ) {
			$this->_logger->log_info( "Sending Request to Sift API: $url" );
			$this->_logger->log_info( $args );
			if ( ! isset( $args['method'] ) )
				$args['method'] = 'GET';

			$args['timeout'] = 10;

			if ( isset( $args['body'] ) && ! is_string( $args['body'] ) ) {
				$args['body'] = json_encode( $args['body'] );
			}

			$result = wp_remote_request( $url, $args );
			$this->_logger->log_info( $result );
			return $result;
		}
	}

endif;
