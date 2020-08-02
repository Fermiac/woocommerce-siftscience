<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class format woocommerce account events into the Sift format.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Format_Account' ) ) :

	require_once 'class-wc-siftscience-options.php';

	class WC_SiftScience_Format_Account {
		private $options;

		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/create-account
		public function create_account( $user_id, WP_User $user ) {
			$data = array(
				// Required Fields
				'$type'       => '$create_account',
				'$user_id'    => $this->options->get_user_id_from_user_id( $user_id ),

				// Supported Fields
				'$session_id'       => $this->options->get_session_id(),
				'$user_email'       => $user->user_email,
				'$name'             => $user->first_name . ' ' . $user->last_name,
				//'$phone'            => $user->get_billing_phone(),
				//'$referrer_user_id' => 'janejane101',
				//'$payment_methods'  => array(
				//array(
				//'$payment_type'    => '$credit_card',
				//'$card_bin'        => '542486',
				//'$card_last4'      => '4444'
				//)
				//),
				//'$billing_address'  => $this->create_address( $user, 'billing' ),
				//'$shipping_address'  => array(
				//'$name'          => 'Bill Jones',
				//'$phone'         => '1-415-555-6041',
				//'$address_1'     => '2100 Main Street',
				//'$address_2'     => 'Apt 3B',
				//'$city'          => 'New London',
				//'$region'        => 'New Hampshire',
				//'$country'       => 'US',
				//'$zipcode'       => '03257'
				//),
				//'$promotions'       => array(
				//array(
				//'$promotion_id'     => 'FriendReferral',
				//'$status'           => '$success',
				//'$referrer_user_id' => 'janejane102',
				//'$credit_point'     => array(
				//'$amount'             => 100,
				//'$credit_point_type'  => 'account karma'
				//)
				//)
				//),

				// '$social_sign_on_type'   => '$twitter',

				// Suggested Custom Fields
				// 'twitter_handle'          => 'billyjones',
				// 'work_phone'              => '1-347-555-5921',
				// 'location'                => 'New London, NH',
				// 'referral_code'           => 'MIKEFRIENDS',
				// 'email_confirmed_status'  => '$pending',
				// 'phone_confirmed_status'  => '$pending'
			);

			return apply_filters( 'wc_siftscience_create_account', $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/update-account
		public function update_account( $user_id, $old_user_data ) {
			$user = get_userdata( $user_id );
			$data = array(
				// Required Fields
				'$type'       => '$update_account',
				'$user_id'    => $this->options->get_user_id_from_user_id( $user_id ),

				// Supported Fields
				'$changed_password' => $this->is_password_changed( $user_id, $old_user_data ),
				'$user_email'       => $user->user_email,
				'$name'             => $user->first_name . ' ' . $user->last_name,
				//'$phone'            => $user->get_billing_phone(),
				//'$referrer_user_id' => 'janejane102',
				//'$payment_methods'  => array(
				//array(
				//'$payment_type'    => '$credit_card',
				//'$card_bin'        => '542486',
				//'$card_last4'      => '4444'
				//)
				//),
				//'$billing_address'  => $this->create_address( $user, 'billing' ),

				//'$social_sign_on_type'   => '$twitter',

				// Suggested Custom Fields
				//'email_confirmed_status'   => '$success',
				//'phone_confirmed_status'   => '$success'
			);

			return apply_filters( 'wc_siftscience_update_account', $data );
		}

		public function link_session_to_user( $user_id ) {
			$data = array (
				'$type'       => '$link_session_to_user',
				'$user_id'    => $this->options->get_user_id_from_user_id( $user_id ),
				'$session_id' => $this->options->get_session_id(),
			);

			return apply_filters( 'wc_siftscience_link_session_to_user', $data );
		}

		private function is_password_changed( $user_id, $old_user_data ) {
			$user = get_user_by( 'id', $user_id );
			if ( $user === null || $user === false || $old_user_data === null )
				return false;
			return ( isset( $old_user_data->user_pass ) && $user->user_pass !== $old_user_data->user_pass );
		}
	}

endif;