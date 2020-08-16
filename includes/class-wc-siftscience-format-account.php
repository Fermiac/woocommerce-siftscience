<?php

/**
 * This class format woocommerce account events into the Sift format.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package siftsience
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Format_Account' ) ) :

	require_once 'class-wc-siftscience-options.php';

	class WC_SiftScience_Format_Account {
		private $options;

		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		// When doc comment use @link for this site.
		// https://sift.com/developers/docs/v204/curl/events-api/reserved-events/create-account.
		public function create_account( $user_id, WP_User $user ) {
			$data = array(
				'$type'       => '$create_account',
				'$user_id'    => $this->options->get_user_id_from_user_id( $user_id ),
				'$session_id' => $this->options->get_session_id(),
				'$user_email' => $user->user_email,
				'$name'       => $user->first_name . ' ' . $user->last_name,
			);

			return apply_filters( 'wc_siftscience_create_account', $data );
		}

		// When doc comment use @link for this site.
		// https://sift.com/developers/docs/v204/curl/events-api/reserved-events/update-account.
		public function update_account( $user_id, $old_user_data ) {
			$user = get_userdata( $user_id );
			$data = array(
				'$type'             => '$update_account',
				'$user_id'          => $this->options->get_user_id_from_user_id( $user_id ),
				'$changed_password' => $this->is_password_changed( $user_id, $old_user_data ),
				'$user_email'       => $user->user_email,
				'$name'             => $user->first_name . ' ' . $user->last_name,
			);

			return apply_filters( 'wc_siftscience_update_account', $data );
		}

		public function link_session_to_user( $user_id ) {
			$data = array(
				'$type'       => '$link_session_to_user',
				'$user_id'    => $this->options->get_user_id_from_user_id( $user_id ),
				'$session_id' => $this->options->get_session_id(),
			);

			return apply_filters( 'wc_siftscience_link_session_to_user', $data );
		}

		private function is_password_changed( $user_id, $old_user_data ) {
			$user = get_user_by( 'id', $user_id );
			if ( false === $user || null === $old_user_data ) {
				return false;
			}
			return ( isset( $old_user_data->user_pass ) && $user->user_pass !== $old_user_data->user_pass );
		}
	}

endif;