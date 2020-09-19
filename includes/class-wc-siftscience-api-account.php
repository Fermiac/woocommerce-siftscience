<?php
/**
 * This class format woocommerce account events into the Sift format.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Api_Account' ) ) :

	require_once 'class-wc-siftscience-options.php';

	/**
	 * Class WC_SiftScience_Api_Account
	 */
	class WC_SiftScience_Api_Account {
		/**
		 * Options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * WC_SiftScience_Api_Account constructor.
		 *
		 * @param WC_SiftScience_Options $options Options service.
		 */
		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		/**
		 * Create account event
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/create-account
		 * @param string  $user_id ID of the user.
		 * @param WP_User $user The user object.
		 *
		 * @return array
		 */
		public function create_account( $user_id, WP_User $user ) {
			$data = array(
				'$type'       => '$create_account',
				'$user_id'    => $this->options->get_sift_user_id( $user_id ),
				'$session_id' => $this->options->get_session_id(),
				'$user_email' => $user->user_email,
				'$name'       => $user->first_name . ' ' . $user->last_name,
			);

			return apply_filters( 'wc_siftscience_create_account', $data );
		}

		/**
		 * Format update account event
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/update-account
		 * @param string $user_id User's ID.
		 * @param array  $old_user_data Old user data before change.
		 *
		 * @return array
		 */
		public function update_account( $user_id, $old_user_data ) {
			$user = get_userdata( $user_id );
			$data = array(
				'$type'             => '$update_account',
				'$user_id'          => $this->options->get_sift_user_id( $user_id ),
				'$changed_password' => $this->is_password_changed( $user_id, $old_user_data ),
				'$user_email'       => $user->user_email,
				'$name'             => $user->first_name . ' ' . $user->last_name,
			);

			return apply_filters( 'wc_siftscience_update_account', $data );
		}

		/**
		 * Add session data to user data.
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/link-session-to-user
		 * @param string $user_id User's id.
		 *
		 * @return array
		 */
		public function link_session_to_user( $user_id ) {
			$data = array(
				'$type'       => '$link_session_to_user',
				'$user_id'    => $this->options->get_sift_user_id( $user_id ),
				'$session_id' => $this->options->get_session_id(),
			);

			return apply_filters( 'wc_siftscience_link_session_to_user', $data );
		}

		/**
		 * Checks if password has changed
		 *
		 * @param string $user_id User id.
		 * @param array  $old_user_data Old data before change.
		 *
		 * @return bool
		 */
		private function is_password_changed( $user_id, $old_user_data ) {
			$user = get_user_by( 'id', $user_id );
			if ( false === $user || null === $old_user_data ) {
				return false;
			}
			return ( isset( $old_user_data->user_pass ) && $user->user_pass !== $old_user_data->user_pass );
		}
	}

endif;
