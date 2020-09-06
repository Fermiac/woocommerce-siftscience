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

		public const SCHEMA = 'siftsci_';

		public const GUID    = self::SCHEMA . 'guid';
		public const API_KEY = self::SCHEMA . 'api_key';
		public const JS_KEY  = self::SCHEMA . 'js_key';
		public const STATS   = self::SCHEMA . 'stats';

		public const SEND_STATS  = self::SCHEMA . 'send_stats';
		public const NAME_PREFIX = self::SCHEMA . 'name_prefix';
		public const STATS_API   = 'https://sift.fermiac.staat.us';

		public const THRESHOLD_GOOD    = self::SCHEMA . 'threshold_good';
		public const THRESHOLD_BAD     = self::SCHEMA . 'threshold_bad';
		public const AUTO_SEND_ENABLED = self::SCHEMA . 'auto_send_enabled';
		public const MIN_ORDER_VALUE   = self::SCHEMA . 'min_order_value';
		public const LOG_LEVEL_KEY     = self::SCHEMA . 'log_level';
		public const IS_API_SETUP      = self::SCHEMA . 'is_api_setup';
		public const STATS_LAST_SENT   = self::SCHEMA . 'stats_last_sent';
	}
endif;
