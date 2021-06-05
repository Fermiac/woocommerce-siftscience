<?php
/**
 * This class gets and sets the plugin options.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Options' ) ) :
	/**
	 * Class WC_SiftScience_Options
	 */
	class WC_SiftScience_Options {
		/**
		 * The plugin version
		 *
		 * @var string
		 */
		private $version;

		/**
		 * Either the version of the plugin or a timestamp for cache invalidation
		 *
		 * @var string
		 */
		private $asset_version;

		/**
		 * The current log level to log at
		 *
		 * @var int
		 */
		private $log_level;

		/**
		 * Stores the most recent value of API config state.
		 * We use this to avoid a race condition in WordPress's get_option().
		 *
		 * @var string
		 */
		private $is_setup_value;

		private const SCHEMA = 'siftsci_';

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

		public const ORDER_STATUS_AUTO_UPDATE = self::SCHEMA . 'order_status_auto_update';

		/**
		 * WC_SiftScience_Options constructor.
		 */
		public function __construct() {
			$this->version        = WC_SiftScience_Plugin::PLUGIN_VERSION;
			$this->log_level      = get_option( self::LOG_LEVEL_KEY, 2 );
			$this->is_setup_value = get_option( self::IS_API_SETUP, '0' );

			$is_debug            = defined( 'WP_DEBUG' ) && WP_DEBUG;
			$this->asset_version = $is_debug ? (string) time() : self::get_version();
		}

		/**
		 * Returns the log level
		 *
		 * @return int The current log level
		 */
		public function get_log_level() {
			return $this->log_level;
		}

		/**
		 * Get the current version
		 *
		 * @return string
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Get the version to append at the and of CSS or JS files
		 *
		 * @return string
		 */
		public function get_asset_version() {
			return $this->asset_version;
		}

		/**
		 * Fetches the sift api key
		 *
		 * @return string
		 */
		public function get_api_key() {
			return get_option( self::API_KEY );
		}

		/**
		 * Gets the meta key for storing backfill state
		 *
		 * @return string
		 */
		public function get_backfill_meta_key() {
			return '_wcsiftsci_isbackfill';
		}

		/**
		 * Gets the meta key for storing the session
		 *
		 * @return string
		 */
		public function get_session_meta_key() {
			return '_wcsiftsci_session';
		}

		/**
		 * Gets the sift JS key
		 *
		 * @return string
		 */
		public function get_js_key() {
			return get_option( self::JS_KEY );
		}

		/**
		 * Gets the prefix used for users and orders in sift
		 *
		 * @return string
		 */
		public function get_name_prefix() {
			return get_option( self::NAME_PREFIX, '' );
		}

		/**
		 * Gets the threshold for good users (lower is better)
		 *
		 * @return int
		 */
		public function get_threshold_good() {
			return get_option( self::THRESHOLD_GOOD, 30 );
		}

		/**
		 * Gets the threshold for good users (lower is better)
		 *
		 * @param int $value The value of the good limit.
		 */
		public function set_threshold_good( int $value ) {
			update_option( self::THRESHOLD_GOOD, $value );
		}

		/**
		 * Gets the threshold for bad orders (higher is badder)
		 *
		 * @return int
		 */
		public function get_threshold_bad() {
			return get_option( self::THRESHOLD_BAD, 60 );
		}

		/**
		 * Gets the threshold for good users (lower is better)
		 *
		 * @param int $value The value of the bad limit.
		 */
		public function set_threshold_bad( int $value ) {
			update_option( self::THRESHOLD_BAD, $value );
		}

		/**
		 * Gets the ID of the current user
		 *
		 * @return int|null
		 */
		public function get_current_user_id() {
			return is_user_logged_in() ? wp_get_current_user()->ID : null;
		}

		/**
		 * Gets the current session id
		 *
		 * @return string
		 */
		public function get_session_id() {
			return session_id();
		}

		/**
		 * Checks if API is correctly set up
		 *
		 * @return bool
		 */
		public function is_setup() {
			return '1' === $this->is_setup_value;
		}

		/**
		 * Sets the status of the is_setup option
		 *
		 * @param bool $is_api_working Whether or not the API settings are correct.
		 */
		public function set_is_setup( $is_api_working ) {
			$this->is_setup_value = $is_api_working ? '1' : '0';
			update_option( self::IS_API_SETUP, $this->is_setup_value );
		}

		/**
		 * Checks if auto-send is enabled
		 *
		 * @return bool
		 */
		public function auto_send_enabled() {
			return ( get_option( self::AUTO_SEND_ENABLED ) === 'yes' );
		}

		/**
		 * Gets the minimum order value to auto-send
		 *
		 * @return double
		 */
		public function get_min_order_value() {
			return get_option( self::MIN_ORDER_VALUE, 0 );
		}

		/**
		 * Gets (and sets if not already set) the stats guid for the store
		 *
		 * @return string
		 */
		public function get_guid() {
			$guid = get_option( self::GUID, false );
			if ( false === $guid ) {
				$guid = $this->generate_guid();
				update_option( self::GUID, $guid );
			}

			return $guid;
		}

		/**
		 * Fetches the session id of the order
		 *
		 * @param WC_Order $order The order to fetch from.
		 *
		 * @return string
		 */
		public function get_order_session_id( WC_Order $order ) {
			$session_id = get_post_meta( $order->get_id(), $this->get_session_meta_key(), true );
			return false === $session_id ? $this->get_session_id() : $session_id;
		}

		/**
		 * Gets the user id from the order
		 *
		 * @param WC_Order $order The order from which to get the user id.
		 *
		 * @return string
		 */
		public function get_user_id( WC_Order $order ) {
			return 0 === $order->get_user_id()
				? $this->get_user_id_from_order_id( $order->get_id() )
				: $this->get_sift_user_id( $order->get_user_id() );
		}

		/**
		 * Generates the sift-specific order id from the given id
		 *
		 * @param string $id The order id.
		 *
		 * @return string
		 */
		public function get_user_id_from_order_id( $id ) {
			return $this->get_name_prefix() . 'anon_order_' . $id;
		}

		/**
		 * Gets the sift-user id from the WordPress user id
		 *
		 * @param string $id The user id.
		 *
		 * @return string
		 */
		public function get_sift_user_id( $id ) {
			return $this->get_name_prefix() . 'user_' . $id;
		}

		/**
		 * Gets the sift-specific product id
		 *
		 * @param string $product_id The product id.
		 *
		 * @return string
		 */
		public function get_sift_product_id( $product_id ) {
			return $this->get_name_prefix() . 'product_' . $product_id;
		}

		/**
		 * Gets auto-update order status setting
		 *
		 * @return array
		 */
		public function get_order_auto_update_settings() {
			$default = $this->create_order_auto_update_array( 'wc-processing', 'none', 'wc-processing', 'none' );
			return get_option( self::ORDER_STATUS_AUTO_UPDATE, $default );
		}

		/**
		 * Sets auto-update order status setting
		 *
		 * @param string $good_from Start state for good orders.
		 * @param string $good_to Target state for good orders.
		 * @param string $bad_from Start state for bad orders.
		 * @param string $bad_to Target state for bad orders.
		 */
		public function set_order_auto_update_settings( string $good_from, string $good_to, string $bad_from, string $bad_to ) {
			$settings = $this->create_order_auto_update_array( $good_from, $good_to, $bad_from, $bad_to );
			update_option( self::ORDER_STATUS_AUTO_UPDATE, $settings );
		}

		/**
		 * Formats the array for auto-order-status settings
		 *
		 * @param string $good_from Start state for good orders.
		 * @param string $good_to Target state for good orders.
		 * @param string $bad_from Start state for bad orders.
		 * @param string $bad_to Target state for bad orders.
		 *
		 * @return array
		 */
		private function create_order_auto_update_array( string $good_from, string $good_to, string $bad_from, string $bad_to ) {
			return array(
				'good_from' => $good_from,
				'good_to'   => $good_to,
				'bad_from'  => $bad_from,
				'bad_to'    => $bad_to,
			);
		}

		/**
		 * Generates a GUID.
		 * This code is based of a snippet found in https://github.com/alixaxel/phunction,
		 * which was referenced in http://php.net/manual/en/function.com-create-guid.php
		 *
		 * @return string
		 */
		private function generate_guid() {
			return strtolower(
				sprintf(
					'%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
					wp_rand( 0, 65535 ),
					wp_rand( 0, 65535 ),
					wp_rand( 0, 65535 ),
					wp_rand( 16384, 20479 ),
					wp_rand( 32768, 49151 ),
					wp_rand( 0, 65535 ),
					wp_rand( 0, 65535 ),
					wp_rand( 0, 65535 )
				)
			);
		}
	}

endif;
