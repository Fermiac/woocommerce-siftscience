<?php
/**
 * This class handles communication with Sift
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package siftsience
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Comm' ) ) :
	require_once 'class-wc-siftscience-options.php';

	class WC_SiftScience_Comm {
		private const EVENT_URL  = 'https://api.sift.com/v204/events';
		private const LABELS_URL = 'https://api.sift.com/v204/users/{user}/labels';
		private const SCORE_URL  = 'https://api.sift.com/v204/score/{user}/?api_key={api}';
		private const DELETE_URL = 'https://api.sift.com/v204/users/{user}/labels/?api_key={api}&abuse_type=payment_abuse';

		private const HEADERS = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		);

		private $options;
		private $logger;

		public function __construct(
				WC_SiftScience_Options $options,
				WC_SiftScience_Logger $logger ) {
			$this->options = $options;
			$this->logger  = $logger;
		}

		public function post_event( $data ) {
			$data['$api_key'] = $this->options->get_api_key();

			$args = array(
				'headers' => self::HEADERS,
				'method'  => 'POST',
				'body'    => $data,
			);

			return $this->send_request( self::EVENT_URL, $args );
		}

		public function post_label( $user_id, $is_bad ) {
			$data = array(
				'$api_key'    => $this->options->get_api_key(),
				'$is_bad'     => ( $is_bad ? 'true' : 'false' ),
				'$abuse_type' => 'payment_abuse',
			);

			$url  = str_replace( '{user}', rawurlencode( $user_id ), self::LABELS_URL );
			$args = array(
				'headers' => self::HEADERS,
				'method'  => 'POST',
				'body'    => $data
			);

			return $this->send_request( $url, $args );
		}

		public function delete_label( $user ) {
			$api = $this->options->get_api_key();
			$url = str_replace( '{api}', $api, str_replace( '{user}', $user, self::DELETE_URL ) );
			return $this->send_request( $url, array( 'method' => 'DELETE' ) );
		}

		public function get_user_score( $user_id ) {
			$api = $this->options->get_api_key();
			$url = str_replace( '{api}', $api, str_replace( '{user}', $user_id, self::SCORE_URL ) );

			$response = $this->send_request( $url );

			return json_decode( $response['body'] );
		}

		private function send_request( $url, $args = array() ) {
			$this->logger->log_info( "Sending Request to Sift API: $url" );
			$this->logger->log_info( $args );
			if ( ! isset( $args['method'] ) ) {
				$args['method'] = 'GET';
			}
			$args['timeout'] = 10;

			if ( isset( $args['body'] ) && ! is_string( $args['body'] ) ) {
				$args['body'] = wp_json_encode( $args['body'] );
			}

			$result = wp_remote_request( $url, $args );
			$this->logger->log_info( $result );
			return $result;
		}
	}

endif;
