<?php
/**
 * This class handles communication with Sift
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Comm' ) ) :

	require_once 'class-wc-siftscience-options.php';

	/**
	 * Class WC_SiftScience_Comm
	 */
	class WC_SiftScience_Comm {
		private const EVENT_URL  = 'https://api.sift.com/v205/events';
		private const SCORE_URL  = 'https://api.sift.com/v205/score/{user}/?api_key={api}';
		private const LABELS_URL = 'https://api.sift.com/v205/users/{user}/labels';
		private const DELETE_URL = 'https://api.sift.com/v205/users/{user}/labels/?api_key={api}&abuse_type=payment_abuse';

		private const HEADERS = array(
			'Accept'       => 'application/json',
			'Content-Type' => 'application/json',
		);

		/**
		 * Options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * Logger service
		 *
		 * @var WC_SiftScience_Logger
		 */
		private $logger;

		/**
		 * WC_SiftScience_Comm constructor.
		 *
		 * @param WC_SiftScience_Options $options Options service.
		 * @param WC_SiftScience_Logger  $logger Logger service.
		 */
		public function __construct(WC_SiftScience_Options $options, WC_SiftScience_Logger $logger ) {
			$this->options = $options;
			$this->logger  = $logger;
		}

		/**
		 * Sends event data to Sift
		 *
		 * @param array $data The data to send.
		 *
		 * @return array|WP_Error
		 */
		public function post_event( array $data ) {
			$data['$api_key'] = $this->options->get_api_key();

			$args = array(
				'headers' => self::HEADERS,
				'method'  => 'POST',
				'body'    => $data,
			);

			return $this->send_request( self::EVENT_URL, $args );
		}

		/**
		 * Sends a good/bad label to Sift
		 *
		 * @param string $user_id ID of the user to label.
		 * @param bool $is_bad Is bad.
		 *
		 * @return array|WP_Error
		 */
		public function post_label( string $user_id, bool $is_bad ) {
			$data = array(
				'$api_key'    => $this->options->get_api_key(),
				'$is_bad'     => ( $is_bad ? true : false ),
				'$abuse_type' => 'payment_abuse',
			);

			$url  = str_replace( '{user}', rawurlencode( $user_id ), self::LABELS_URL );
			$args = array(
				'headers' => self::HEADERS,
				'method'  => 'POST',
				'body'    => $data,
			);

			return $this->send_request( $url, $args );
		}

		/**
		 * Removes the label the user has
		 *
		 * @param string $user The user id to remove label for.
		 *
		 * @return array|WP_Error
		 */
		public function delete_label( string $user ) {
			$api = $this->options->get_api_key();
			$url = str_replace( '{api}', $api, str_replace( '{user}', $user, self::DELETE_URL ) );
			return $this->send_request( $url, array( 'method' => 'DELETE' ) );
		}

		/**
		 * Gets the score of a user from sift
		 *
		 * @param string $user_id User's ID as it's saved in sift.
		 *
		 * @return array
		 */
		public function get_user_score( string $user_id ) {
			$api = $this->options->get_api_key();
			$url = str_replace( '{api}', $api, str_replace( '{user}', $user_id, self::SCORE_URL ) );

			$response = $this->send_request( $url );

			return json_decode( $response['body'] );
		}

		/**
		 * Common function for sending request to sift
		 *
		 * @param string $url URL to use.
		 * @param array $args Arguments of the request.
		 *
		 * @return array|WP_Error
		 */
		private function send_request( string $url, $args = array() ) {
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
