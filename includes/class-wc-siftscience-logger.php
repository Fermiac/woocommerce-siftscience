<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class is used for logging messages.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Logger' ) ) :

	class WC_SiftScience_Logger {
		public function log( $msg ) {
			if ( WP_DEBUG === true && WP_DEBUG_LOG === true ) {
				error_log( $msg );
			}
		}
	}

endif;
