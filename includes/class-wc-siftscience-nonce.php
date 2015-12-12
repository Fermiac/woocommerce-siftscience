<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles the nonce creation.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists('WC_SiftScience_Nonce')) {
	class WC_SiftScience_Nonce {
		public static function action( $data ) {
			$string = 'wc_siftsci';
			$string .= isset( $data['event'] ) ? ( '_' . $data['event'] ) : '';
			$string .= isset( $data['event_id'] ) ? ( '_' . $data['event_id'] ) : '';
			$string .= isset( $data['user_id'] ) ? ( '_' . $data['user_id'] ) : '';

			return wp_create_nonce( $string );
		}
	}
}