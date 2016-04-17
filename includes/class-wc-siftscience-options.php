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
		public static $api_sandbox = 'siftsci_api_sandbox';
		public static $js_sandbox = 'siftsci_js_sandbox';
		public static $api_production = 'siftsci_api_production';
		public static $js_production = 'siftsci_js_production';
		public static $is_api_setup = 'siftsci_is_api_setup';
		public static $send_on_create_enabled = 'siftsci_send_on_create_enabled';

		public function get_api_key() {
			$key = $this->is_production() ? self::$api_production : self::$api_sandbox;
			return get_option( $key );
		}

		public function get_backfill_meta_key() {
			return $this->is_production() ?
				"_wcsiftsci_isbackfill_prod" : "_wcsiftsci_isbackfill_sand";
		}

		public function get_js_key() {
			$key = $this->is_production() ? self::$js_production : self::$js_sandbox;
			return get_option( $key );
		}

		public function get_user_id() {
			return is_user_logged_in() ? wp_get_current_user()->ID : '';
		}

		public function get_session_id() {
			if ( session_status() != PHP_SESSION_ACTIVE ) {
				session_start();
			}

			return session_id();
		}

		public function is_setup() {
			return ( get_option( self::$is_api_setup ) === '1' );
		}

		public function send_on_create_enabled() {
			return ( get_option( self::$send_on_create_enabled ) === 'yes' );
		}

		private function is_production() {
			return get_option( self::$mode ) === 'production';
		}
	}

endif;
