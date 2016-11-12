<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class gets and sets the plugin options.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Options' ) ) :

	class WC_SiftScience_Options {
		public static $mode = 'siftsci_reporting_mode';
		public static $api_key = 'siftsci_api_key';
		public static $js_key = 'siftsci_js_key';
		public static $name_prefix = 'siftsci_name_prefix';
		public static $is_api_setup = 'siftsci_is_api_setup';
		public static $send_on_create_enabled = 'siftsci_send_on_create_enabled';
		public static $threshold_good = 'siftsci_threshold_good';
		public static $threshold_bad = 'siftsci_threshold_bad';

		public function get_api_key() {
			return get_option( self::$api_key );
		}

		public function get_backfill_meta_key() {
			return '_wcsiftsci_isbackfill';
		}

		public function get_session_meta_key() {
			return '_wcsiftsci_session';
		}

		public function get_js_key() {
			return get_option( self::$js_key );
		}

		public function get_name_prefix() {
			return get_option( self::$name_prefix, '' );
		}

		public function get_threshold_good() {
			return get_option( self::$threshold_good, 30 );
		}

		public function get_threshold_bad() {
			return get_option( self::$threshold_bad, 60 );
		}

		public function get_user_id() {
			return is_user_logged_in() ? wp_get_current_user()->ID : null;
		}

		public function get_session_id() {
			return session_id();
		}

		public function is_setup() {
			return ( get_option( self::$is_api_setup ) === '1' );
		}

		public function send_on_create_enabled() {
			return ( get_option( self::$send_on_create_enabled ) === 'yes' );
		}

		public function get_react_app_path() {
			return defined( 'WP_SIFTSCI_DEV' ) && WP_SIFTSCI_DEV
				? 'http://localhost:8085/app.js'
				: plugins_url( "dist/app.js", dirname( __FILE__ ) );
		}
	}

endif;
