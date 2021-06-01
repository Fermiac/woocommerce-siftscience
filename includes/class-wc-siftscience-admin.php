<?php
/**
 * This class handles the plugin's settings page.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Admin' ) ) :

	require_once 'class-wc-siftscience-options.php';
	require_once 'class-wc-siftscience-html.php';
	require_once 'class-wc-siftscience-order-status.php';

	/**
	 * Class WC_SiftScience_Admin
	 */
	class WC_SiftScience_Admin {
		private const ADMIN_ID    = 'siftsci';
		private const ADMIN_LABEL = 'Sift';

		private const GET_VAR_SCHEMA      = 'wc_sift_';
		private const GET_VAR_SET_STATS   = self::GET_VAR_SCHEMA . 'send_stats';
		private const GET_VAR_CLEAR_STATS = self::GET_VAR_SCHEMA . 'clear_stats';
		private const GET_VAR_RESET_GUID  = self::GET_VAR_SCHEMA . 'reset_guid';
		private const GET_VAR_TEST_SSL    = self::GET_VAR_SCHEMA . 'test_ssl';
		private const GET_VAR_CLEAR_LOGS  = self::GET_VAR_SCHEMA . 'clear_logs';

		/**
		 * The options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * The logger service
		 *
		 * @var WC_SiftScience_Logger
		 */
		private $logger;

		/**
		 * The stats service
		 *
		 * @var WC_SiftScience_Stats
		 */
		private $stats;

		/**
		 * Communication service
		 *
		 * @var WC_SiftScience_Comm
		 */
		private $comm;

		/**
		 * Html service
		 *
		 * @var WC_SiftScience_Html
		 */
		private $html;
		/**
		 * WC element creator
		 *
		 * @var WC_SiftScience_Element
		 */
		private $wc_element;

		/**
		 * Class for accessing order status information
		 *
		 * @var WC_SiftScience_Order_Status
		 */
		private $status;

		/**
		 * WC_SiftScience_Admin constructor.
		 *
		 * @param WC_SiftScience_Options      $options    Options service.
		 * @param WC_SiftScience_Comm         $comm       Communication service.
		 * @param WC_SiftScience_Order_Status $status     Order status manager.
		 * @param WC_SiftScience_Html         $html       HTML service.
		 * @param WC_SiftScience_Logger       $logger     Logger service.
		 * @param WC_SiftScience_Stats        $stats      Stats service.
		 * @param WC_SiftScience_Element      $wc_element WC element.
		 */
		public function __construct(
				WC_SiftScience_Options $options,
				WC_SiftScience_Comm $comm,
				WC_SiftScience_Order_Status $status,
				WC_SiftScience_Html $html,
				WC_SiftScience_Logger $logger,
				WC_SiftScience_Stats $stats,
				WC_SiftScience_Element $wc_element ) {
			$this->options    = $options;
			$this->comm       = $comm;
			$this->status     = $status;
			$this->html       = $html;
			$this->logger     = $logger;
			$this->stats      = $stats;
			$this->wc_element = $wc_element;
		}

		/**
		 * Checks of the API settings allow for successful communication
		 *
		 * @return bool True if successfully communicated
		 */
		public function check_api() {
			// try requesting a non-existent user score and see that the response isn't a permission fail.
			$response = $this->comm->get_user_score( '_dummy_' . wp_rand( 1000, 9999 ) );
			$this->logger->log_info( '[api check response] ' . wp_json_encode( $response ) );
			return isset( $response->status ) && ( 0 === $response->status || 54 === $response->status );
		}

		/**
		 * Adds the settings tabs in Woo configs, hooks woocommerce_settings_tabs_array filter
		 *
		 * @param array $pages The current array of pages.
		 *
		 * @return array The resulting pages
		 */
		public function add_settings_page( $pages ) {
			$pages[ self::ADMIN_ID ] = self::ADMIN_LABEL;
			return $pages;
		}

		/**
		 * This function sets sub-tab titles in  woocomemearce sift settings tab, hooks woocommerce_sections_siftsci filter.
		 */
		public function get_sections() {

			$sections = array(
				'main'      => 'Settings',
				'reporting' => 'Reporting',
				'stats'     => 'Stats',
				'debug'     => 'Debug',
			);

			$this->html->display_sections( $sections, self::ADMIN_ID );
		}

		/**
		 * Outputs the settings in the WooCommerce tab, hooks woocommerce_settings_siftsci action.
		 *
		 * @global String $current_section
		 */
		public function output_settings_fields() {
			global $current_section;
			$selected_section = empty( $current_section ) ? 'main' : $current_section;

			switch ( $selected_section ) {
				case 'debug':
					$log_file = dirname( __DIR__ ) . '/debug.log';
					if ( '1' === $this->get_value( self::GET_VAR_CLEAR_LOGS ) ) {
						// @codingStandardsIgnoreStart
						$fh = fopen( $log_file, 'w' );
						fclose( $fh );
						wp_safe_redirect( $this->unbound_nonce_url( self::GET_VAR_CLEAR_LOGS ) );
						// @codingStandardsIgnoreEnd
						exit;
					}

					$GLOBALS['hide_save_button'] = true;

					$logs = '';
					if ( file_exists( $log_file ) ) {
						// @codingStandardsIgnoreStart
						$logs = file_get_contents( $log_file );
						// @codingStandardsIgnoreEnd
					}

					if ( '1' === $this->get_value( self::GET_VAR_TEST_SSL ) ) {
						// Note: SSL check logic reference: https://tecadmin.net/test-tls-version-php/.
						$response    = wp_remote_get( 'https://www.howsmyssl.com/a/check' );
						$body        = wp_json_encode( json_decode( $response['body'] ), JSON_PRETTY_PRINT );
						$tls_version = json_decode( $body )->tls_version;
						$data        = "TLS Version: $tls_version\n\nFull Data:\n$body";

						set_transient( 'wc-siftsci-ssl-log', $data );
						wp_safe_redirect( $this->unbound_nonce_url( self::GET_VAR_TEST_SSL ) );
						exit;
					}

					$ssl_data = get_transient( 'wc-siftsci-ssl-log' );
					if ( false !== $ssl_data ) {
						delete_transient( 'wc-siftsci-ssl-log' );
					}

					$ssl_url = $this->bound_nonce_url( self::GET_VAR_TEST_SSL, '1' );
					$log_url = $this->bound_nonce_url( self::GET_VAR_CLEAR_LOGS, '1' );

					$this->html->display_debugging_info( $ssl_data, $ssl_url, $log_url, $logs );
					break;

				case 'reporting':
					if ( '1' === $this->get_value( self::GET_VAR_RESET_GUID ) ) {
						delete_option( WC_SiftScience_Options::GUID );
						wp_safe_redirect( $this->unbound_nonce_url( self::GET_VAR_RESET_GUID ) );
						exit();
					}

					WC_Admin_Settings::output_fields( $this->get_section_fields( 'reporting' ) );
					break;

				case 'stats':
					$GLOBALS['hide_save_button'] = true;

					if ( '1' === $this->get_value( self::GET_VAR_CLEAR_STATS ) ) {
						$this->stats->clear_stats();
						wp_safe_redirect( $this->unbound_nonce_url( self::GET_VAR_CLEAR_STATS ) );
						exit;
					}

					$stats = get_option( WC_SiftScience_Options::STATS, 'none' );
					if ( 'none' === $stats ) {
						echo '<p>No stats stored yet</p>';
						return;
					}

					$url   = $this->bound_nonce_url( self::GET_VAR_CLEAR_STATS, '1' );
					$stats = json_decode( $stats, true );
					ksort( $stats );
					$this->html->display_stats_tables( $stats, $url );
					break;

				default:
					WC_Admin_Settings::output_fields( $this->get_section_fields( 'main' ) );
					$this->wc_element->add_batch_table();

					$this->html->enqueue_script( 'wc-siftsci-vuejs', 'vue.global' );
					$this->html->enqueue_script( 'wc-siftsci-api', 'api' );
					$this->html->enqueue_script( 'wc-siftsci-script', 'batch-upload', array( 'wc-siftsci-vuejs', 'wc-siftsci-api' ) );
					wp_localize_script( 'wc-siftsci-script', '_siftsci_app_data', array( 'api' => admin_url( 'admin-ajax.php' ) ) );
					break;
			}
		}

		/**
		 * This function adding wc elements for the intended subsection.
		 *
		 * @param String $sub_section the name of the subsection.
		 *
		 * @return Array [] the dictionary in which All fields are added.
		 */
		private function get_section_fields( $sub_section ) {

			if ( 'main' === $sub_section ) {
				$auto_update_settings = $this->options->get_order_auto_update_settings();
				return array(
					$this->wc_element->create(
						WC_SiftScience_Element::TITLE,
						'siftsci_title_id',
						'Sift Settings'
					),

					$this->wc_element->create(
						WC_SiftScience_Element::TEXT,
						WC_SiftScience_Options::API_KEY,
						'Rest API Key',
						'The API key for production'
					),

					$this->wc_element->create(
						WC_SiftScience_Element::TEXT,
						WC_SiftScience_Options::JS_KEY,
						'Javascript Snippet Key',
						'Javascript snippet key for production'
					),

					$this->wc_element->create(
						WC_SiftScience_Element::NUMBER,
						WC_SiftScience_Options::THRESHOLD_GOOD,
						'Good Score Limit',
						'Scores below this value are considered good and shown in green.',
						array(
							'default'  => 30,
							'min'      => 0,
							'max'      => 100,
							'step'     => 1,
							'css'      => 'width:75px;',
							'desc_tip' => false,
						)
					),

					$this->wc_element->create(
						WC_SiftScience_Element::CUSTOM,
						'limit_good',
						'Process Good Orders',
						'Automatically change order status if its Sift score is good.',
						array(
							'sift_state'      => 'good',
							'auto_settings'   => $auto_update_settings,
							'threshold_value' => $this->options->get_threshold_good(),
							'callback'        => 'gb_callback',
							'status'          => $this->status->get_status_options(),
						)
					),

					$this->wc_element->create(
						WC_SiftScience_Element::NUMBER,
						WC_SiftScience_Options::THRESHOLD_BAD,
						'Bad Score Limit',
						'Scores above this value are considered bad and shown in red.',
						array(
							'default'  => 60,
							'min'      => 0,
							'max'      => 100,
							'step'     => 1,
							'css'      => 'width:75px;',
							'desc_tip' => false,
						)
					),

					$this->wc_element->create(
						WC_SiftScience_Element::CUSTOM,
						'limit_bad',
						'Process Bad Orders',
						'Automatically change order status if its Sift score is bad.',
						array(
							'sift_state'      => 'bad',
							'auto_settings'   => $auto_update_settings,
							'threshold_value' => $this->options->get_threshold_bad(),
							'callback'        => 'gb_callback',
							'status'          => $this->status->get_status_options(),
						)
					),

					$this->wc_element->create(
						WC_SiftScience_Element::TEXT,
						WC_SiftScience_Options::NAME_PREFIX,
						'User & Order Name Prefix',
						'Prefix to give order and user names.',
						array( 'desc_tip' => 'Useful when you have have multiple stores and one Sift account.' )
					),

					$this->wc_element->create(
						WC_SiftScience_Element::CHECKBOX,
						WC_SiftScience_Options::AUTO_SEND_ENABLED,
						'Automatically Send Data',
						'Automatically send data to Sift when an order is created'
					),

					$this->wc_element->create(
						WC_SiftScience_Element::NUMBER,
						WC_SiftScience_Options::MIN_ORDER_VALUE,
						'Auto Send Minimum Value',
						'Set to zero to send all orders.',
						array(
							'default'  => 0,
							'min'      => 0,
							'step'     => 1,
							'css'      => 'width:75px;',
							'desc_tip' => 'Orders less than this value won\'t be automatically sent to sift.',
						)
					),

					$this->wc_element->create(
						WC_SiftScience_Element::CUSTOM,
						'_woosift_nonce',
						'',
						'',
						array(
							'nonce'    => wp_create_nonce( 'woo-sifscience-settings' ),
							'callback' => 'nonce_callback',
						)
					),

					$this->wc_element->create(
						WC_SiftScience_Element::SECTIONEND,
						'sifsci_section_main'
					),
				);

			} elseif ( 'reporting' === $sub_section ) {
				return array(
					$this->wc_element->create(
						WC_SiftScience_Element::TITLE,
						'siftsci_title_reporting',
						'Sift Debug & Reporting Settings'
					),

					$this->wc_element->create(
						WC_SiftScience_Element::CUSTOM,
						'anon_id',
						'Anonymous ID',
						'The randomly generated anonymous ID that is used report stats.',
						array(
							'callback'  => 'anon_id_callback',
							'anon_id'   => $this->options->get_guid(),
							'reset_url' => $this->bound_nonce_url( self::GET_VAR_RESET_GUID, '1' ),
						)
					),

					$this->wc_element->create(
						WC_SiftScience_Element::CHECKBOX,
						WC_SiftScience_Options::SEND_STATS,
						'Enable Reporting',
						'Send anonymous statistics and error details.',
						array( 'desc_tip' => $this->get_reporting_checkbox_description() )
					),

					$this->wc_element->create(
						WC_SiftScience_Element::SELECT,
						WC_SiftScience_Options::LOG_LEVEL_KEY,
						'Log Level',
						'How much logging information to generate',
						array(
							'options' =>
								array(
									2 => 'Errors',
									1 => 'Errors & Warnings',
									0 => 'Errors, Warnings & Info',
								),
						)
					),

					$this->wc_element->create(
						WC_SiftScience_Element::SECTIONEND,
						'sifsci_section_reporting'
					),
				);
			}
		}

		/**
		 * Saves the settings, hooks woocommerce_settings_save_siftsci action.
		 *
		 * @global String $current_section
		 */
		public function save_settings() {
			global $current_section;
			$selected_section = empty( $current_section ) ? 'main' : $current_section;

			$fields = $this->get_section_fields( $selected_section );
			WC_Admin_Settings::save_fields( $fields );
			$this->save_custom_fields();

			if ( 'main' === $selected_section ) {
				$is_api_working = $this->check_api();
				$this->options->set_is_setup( $is_api_working );

				if ( $is_api_working ) {
					WC_Admin_Settings::add_message( 'API is correctly configured' );
				} else {
					WC_Admin_Settings::add_error( 'API settings are broken' );
				}
			}
		}

		/**
		 * This function decides to show update or improve notices, hooks admin-notices action.
		 */
		public function settings_notice() {
			// Check to display update notice.
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );

				$is_admin_page = strpos( $uri, 'tab=siftsci' );
				$is_valid_conf = ( false === $is_admin_page || $this->options->is_setup() );

				if ( false === $is_valid_conf ) {
					$settings_url = admin_url( 'admin.php?page=wc-settings&tab=siftsci' );

					$this->html->display_update_notice( $settings_url );
				}
			}

			// Check to display improve notice.
			$is_send_stat_set = get_option( WC_SiftScience_Options::SEND_STATS, 'not_set' );

			if ( 'not_set' === $is_send_stat_set ) {
				$value = $this->get_value( self::GET_VAR_SET_STATS );

				if ( false !== $value ) {
					update_option( WC_SiftScience_Options::SEND_STATS, $value );
					wp_safe_redirect( $this->unbound_nonce_url( self::GET_VAR_SET_STATS ) );

				} else {
					$disabled_link = $this->bound_nonce_url( self::GET_VAR_SET_STATS, 'no' );
					$enabled_link  = $this->bound_nonce_url( self::GET_VAR_SET_STATS, 'yes' );

					$this->html->display_improve_message( $enabled_link, $disabled_link );
				}
			}
		}

		/**
		 * This function will validate GET var with its nonce.
		 *
		 * @param String $var_name the  get variable name.
		 *
		 * @return String|False $result if the get var and it's nonce are valid return it's value else return false.
		 */
		private function get_value( $var_name ) {
			$nonce_name = $this->get_nonce_name( $var_name );

			// Check that nonce is valid and input value exists.
			$is_valid_input = isset( $_GET[ $var_name ], $_GET[ $nonce_name ] )
				&& wp_verify_nonce( sanitize_key( $_GET[ $nonce_name ] ), $nonce_name );

			return ( true === $is_valid_input ) ? sanitize_key( $_GET[ $var_name ] ) : false;
		}

		/**
		 * This function attaches '_nonce' to the get variable name.
		 *
		 * @param String $get_var the GET array variable.
		 *
		 * @return String concatenated nonce name.
		 */
		private function get_nonce_name( $get_var ) {
			return $get_var . '_nonce';
		}

		/**
		 * Creates a variable URL with it's nonce respectivly.
		 *
		 * @param String $get_var_name  the GET variable.
		 * @param String $get_var_value the assigned value.
		 *
		 * @return String bounded link with a get var and it's nonce.
		 */
		private function bound_nonce_url( $get_var_name, $get_var_value ) {
			$url        = add_query_arg( array( $get_var_name => $get_var_value ) );
			$nonce_name = $this->get_nonce_name( $get_var_name );

			return wp_nonce_url( $url, $nonce_name, $nonce_name );
		}

		/**
		 * Retunning a URL dispatching the required GET var and the nonce related.
		 *
		 * @param String $get_var_name the required GET variable.
		 *
		 * @return String the dispatched URL from the GET var and the nonce related.
		 */
		private function unbound_nonce_url( $get_var_name ) {
			return remove_query_arg( array( $get_var_name, $this->get_nonce_name( $get_var_name ) ) );
		}

		/**
		 * Gets the desc_tip that goes under the checkbox.
		 *
		 * @return string
		 * @see get_section_fields( 'reporting' )
		 */
		private function get_reporting_checkbox_description() {
			$url     = 'https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection';
			$message = 'Help us improve this plugin by automatically reporting errors and statistics. ';
			return "$message More info <a target='_blank' href='$url'>here</a>.";
		}

		/**
		 * Saves custom fields that aren't covered by WooCommerce's automatic method
		 */
		private function save_custom_fields() {
			if ( ! ( isset( $_REQUEST['_woosift_nonce'] ) && wp_verify_nonce( sanitize_key( $_REQUEST['_woosift_nonce'] ), 'woo-sifscience-settings' ) ) ) {
				return;
			}

			if ( isset( $_REQUEST['good_from'], $_REQUEST['good_to'], $_REQUEST['bad_from'], $_REQUEST['bad_to'] ) ) {
				$good_from = sanitize_key( $_REQUEST['good_from'] );
				$good_to   = sanitize_key( $_REQUEST['good_to'] );
				$bad_from  = sanitize_key( $_REQUEST['bad_from'] );
				$bad_to    = sanitize_key( $_REQUEST['bad_to'] );

				$this->options->set_order_auto_update_settings( $good_from, $good_to, $bad_from, $bad_to );
			}
		}
	}
endif;
