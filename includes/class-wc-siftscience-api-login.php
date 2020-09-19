<?php
/**
 * This class format woocommerce login and logout events into the Sift format.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Api_Login' ) ) :

	require_once 'class-wc-siftscience-options.php';

	/**
	 * Class WC_SiftScience_Api_Login
	 */
	class WC_SiftScience_Api_Login {
		/**
		 * Options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * WC_SiftScience_Api_Login constructor.
		 *
		 * @param WC_SiftScience_Options $options Options service.
		 */
		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		/**
		 * Successful login event
		 *
		 * @link https://sift.com/developers/docs/v204/curl/events-api/reserved-events/login
		 * @param WP_User $user The user object.
		 *
		 * @return array
		 */
		public function login_success( WP_User $user ) {
			$data = array(
				'$type'         => '$login',
				'$user_id'      => $this->options->get_sift_user_id( $user->ID ),
				'$session_id'   => $this->options->get_session_id(),
				'$login_status' => '$success',
			);

			return apply_filters( 'wc_siftscience_login_success', $data );
		}

		/**
		 * Failed login event
		 *
		 * @link https://sift.com/developers/docs/v204/curl/events-api/reserved-events/login
		 * @param string $username The attempted username.
		 *
		 * @return array
		 */
		public function login_failure( $username ) {
			$data = array(
				'$type'         => '$login',
				'$login_status' => '$failure',
				'$session_id'   => $this->options->get_session_id(),
			);

			$user = get_user_by( 'login', $username );
			if ( false !== $user ) {
				$data['$user_id'] = $this->options->get_sift_user_id( $user->ID );
			}

			return apply_filters( 'wc_siftscience_login_failure', $data );
		}

		/**
		 * The logout event
		 *
		 * @link https://sift.com/developers/docs/v204/curl/events-api/reserved-events/logout
		 * @param string $user_id Logged out user id.
		 *
		 * @return array
		 */
		public function logout( $user_id ) {
			$data = null;
			if ( null !== $user_id && 0 !== $user_id ) {
				$data = array(
					'$type'    => '$logout',
					'$user_id' => $this->options->get_sift_user_id( $user_id ),
				);
			}

			return apply_filters( 'wc_siftscience_logout', $data );
		}
	}

endif;
