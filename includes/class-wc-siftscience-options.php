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
		public const API_KEY = 'siftsci_api_key';
		public const JS_KEY = 'siftsci_js_key';
		public const NAME_PREFIX = 'siftsci_name_prefix';
		public const THRESHOLD_GOOD = 'siftsci_threshold_good';
		public const THRESHOLD_BAD = 'siftsci_threshold_bad';
		public const AUTO_SEND_ENABLED = 'siftsci_auto_send_enabled';
		public const MIN_ORDER_VALUE = 'siftsci_min_order_value';
		public const LOG_LEVEL_KEY = 'siftsci_log_level';
		public const IS_API_SETUP = 'siftsci_is_api_setup'; 
		public const STATS = 'siftsci_stats';
		public const STATS_API = 'https://sift.fermiac.staat.us';
		public const SEND_STATS = 'siftsci_send_stats';
		public const STATS_LAST_SENT = 'siftsci_stats_last_sent';
		public const GUID = 'siftsci_guid';

		private $version = false;
		private $log_level;

		public function __construct( $version = false ) {
			$this->version = $version;
			$this->log_level = get_option( self::LOG_LEVEL_KEY, 2 );
		}

		public function get_log_level() {
			return $this->log_level;
		}

		public function get_version() {
			return $this->version;
		}

		public function get_api_key() {
			return get_option( self::API_KEY );
		}

		public function get_backfill_meta_key() {
			return '_wcsiftsci_isbackfill';
		}

		public function get_session_meta_key() {
			return '_wcsiftsci_session';
		}

		public function get_js_key() {
			return get_option( self::JS_KEY );
		}

		public function get_name_prefix() {
			return get_option( self::NAME_PREFIX, '' );
		}

		public function get_threshold_good() {
			return get_option( self::THRESHOLD_GOOD, 30 );
		}

		public function get_threshold_bad() {
			return get_option( self::THRESHOLD_BAD, 60 );
		}

		public function get_current_user_id() {
			return is_user_logged_in() ? wp_get_current_user()->ID : null;
		}

		public function get_session_id() {
			return session_id();
		}

		public function is_setup() {
			return ( get_option( self::IS_API_SETUP ) === '1' );
		}

		public function auto_send_enabled() {
			return ( get_option( self::AUTO_SEND_ENABLED ) === 'yes' );
		}

		public function get_min_order_value() {
			return get_option( self::MIN_ORDER_VALUE, 0 ) ;
		}

		public function get_react_app_path() {
			return defined( 'WP_SIFTSCI_DEV' )
				? WP_SIFTSCI_DEV
				: plugins_url( "dist/app.js", dirname( __FILE__ ) );
		}

		public function get_guid() {
			$guid = get_option( self::GUID, false );
			if ( false === $guid ) {
				$guid = $this->generate_guid();
				update_option( self::GUID, $guid );
			}

			return $guid;
		}

		public function get_order_session_id( WC_Order $order ) {
			$session_id = get_post_meta( $order->get_id(), $this->get_session_meta_key(), true );
			return false === $session_id ? $this->get_session_id() : $session_id;
		}

		public function get_user_id( WC_Order $order ) {
			return 0 === $order->get_user_id()
				? $this->get_user_id_from_order_id( $order->get_id() )
				: $this->get_user_id_from_user_id( $order->get_user_id() );
		}

		public function get_user_id_from_order_id( $id ) {
			return $this->get_name_prefix() . 'anon_order_' . $id;
		}

		public function get_user_id_from_user_id( $id ) {
			return $this->get_name_prefix() . 'user_' . $id;
		}

		public function get_sift_product_id( $product_id ) {
			return $this->get_name_prefix() . 'product_' . $product_id;
		}

		/**
		 * Generates a GUID.
		 * This code is based of a snippet found in https://github.com/alixaxel/phunction,
		 * which was referenced in http://php.net/manual/en/function.com-create-guid.php
		 *
		 * @return string
		 */
		private function generate_guid() {
			return strtolower( sprintf( '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
				mt_rand( 0, 65535 ),
				mt_rand( 0, 65535 ),
				mt_rand( 0, 65535 ),
				mt_rand( 16384, 20479 ),
				mt_rand( 32768, 49151 ),
				mt_rand( 0, 65535 ),
				mt_rand( 0, 65535 ),
				mt_rand( 0, 65535 )
			) );
		}
	}

endif;
