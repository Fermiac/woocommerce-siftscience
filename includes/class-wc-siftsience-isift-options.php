<?php
/**
 * This interface has sift options
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! interface_exists( 'Isift_Options' ) ) :
	interface Isift_Options {

		public const GUID    = 'siftsci_guid';
		public const API_KEY = 'siftsci_api_key';
		public const JS_KEY  = 'siftsci_js_key';
		public const STATS   = 'siftsci_stats';

		public const SEND_STATS  = 'siftsci_send_stats';
		public const NAME_PREFIX = 'siftsci_name_prefix';
		public const STATS_API   = 'https://sift.fermiac.staat.us';

		public const THRESHOLD_GOOD    = 'siftsci_threshold_good';
		public const THRESHOLD_BAD     = 'siftsci_threshold_bad';
		public const AUTO_SEND_ENABLED = 'siftsci_auto_send_enabled';
		public const MIN_ORDER_VALUE   = 'siftsci_min_order_value';
		public const LOG_LEVEL_KEY     = 'siftsci_log_level';
		public const IS_API_SETUP      = 'siftsci_is_api_setup';
		public const STATS_LAST_SENT   = 'siftsci_stats_last_sent';
	}
endif;
