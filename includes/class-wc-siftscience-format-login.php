<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class format woocommerce login and logout events into the Sift format.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Format_Login' ) ) :

	require_once 'class-wc-siftscience-options.php';

	class WC_SiftScience_Format_Login {
		private $options;

		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/login
		public function login_success( WP_User $user ) {
			$data = array(
				'$type'         => '$login',
				'$user_id'      => $this->options->get_user_id_from_user_id( $user->ID ),
				'$session_id'   => $this->options->get_session_id(),
				'$login_status' => '$success'
			);

			return apply_filters( 'wc_siftscience_login_success', $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/login
		public function login_failure( $username ) {
			$data = array(
				'$type'         => '$login',
				'$login_status' => '$failure',
				'$session_id'   => $this->options->get_session_id(),
			);

			$user = get_user_by( 'login', $username );
			if ( false !== $user ) {
				$data[ '$user_id' ] = $this->options->get_user_id_from_user_id( $user->ID );
			}

			return apply_filters( 'wc_siftscience_login_failure', $data );
		}

		//https://siftscience.com/developers/docs/curl/events-api/reserved-events/logout
		public function logout( $user_id ) {
			$data = null;
			if ( null !== $user_id && 0 !== $user_id ) {
				$data = array(
					'$type'         => '$logout',
					'$user_id'      => $this->options->get_user_id_from_user_id( $user_id ),
				);
			}

			return apply_filters( 'wc_siftscience_logout', $data );
		}
	}

endif;