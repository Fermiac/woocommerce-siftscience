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

		private const ALLOWED_HTML = array(
			'table' => array(),
			'thead' => array(),
			'tbody' => array(),
			'tr'    => array(),
			'td'    => array( 'style' => array() ),
			'span'  => array( 'style' => array() ),
			'th'    => array(
				'scope'   => array(),
				'colspan' => array(),
				'style'   => array(),
			),
		);

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
		 * WC_SiftScience_Admin constructor.
		 *
		 * @param WC_SiftScience_Options $options Options service.
		 * @param WC_SiftScience_Comm    $comm Communication service.
		 * @param WC_SiftScience_Html    $html HTML service.
		 * @param WC_SiftScience_Logger  $logger Logger service.
		 * @param WC_SiftScience_Stats   $stats Stats service.
		 */
		public function __construct(
				WC_SiftScience_Options $options,
				WC_SiftScience_Comm $comm,
				WC_SiftScience_Html $html,
				WC_SiftScience_Logger $logger,
				WC_SiftScience_Stats $stats ) {
			$this->options = $options;
			$this->comm    = $comm;
			$this->html    = $html;
			$this->logger  = $logger;
			$this->stats   = $stats;

			$this->html->set_allowed_tags( self::ALLOWED_HTML );
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
		 * This function sets sub-tab titles in  woocomemearce sift settings tab.
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
		 * Outputs the settings in the WooCommerce tab
		 */
		public function output_settings_fields() {
			global $current_section;
			switch ( $current_section ) {
				case 'debug':
					$this->output_settings_debug();
					break;
				case 'reporting':
					$this->output_settings_reporting();
					break;
				case 'stats':
					$this->output_settings_stats();
					break;
				default:
					$this->output_settings_main();
					break;
			}
		}

		/**
		 * Outputs the main settings page
		 * Creates the HTML table for the batch upload control
		 */
		private function output_settings_main() {
			WC_Admin_Settings::output_fields( $this->get_settings_main() );

			$this->html->display_batch_table();

			self::enqueue_script( 'wc-siftsci-vuejs', 'vue-dev', array() );
			self::enqueue_script( 'wc-siftsci-control', 'BatchUpload.umd', array( 'wc-siftsci-vuejs' ) );
			self::enqueue_script( 'wc-siftsci-script', 'batch-upload', array( 'wc-siftsci-control' ) );
			wp_localize_script( 'wc-siftsci-script', '_siftsci_app_data', array( 'api' => admin_url( 'admin-ajax.php' ) ) );
		}

		/**
		 * Enqueues the a javascript file for inclusion at end of page
		 *
		 * @param string $name Name of the script to enqueue.
		 * @param string $file Filename of the js file.
		 * @param array  $deps Array of dependencies.
		 */
		private static function enqueue_script( $name, $file, $deps ) {
			$version = time(); // TODO: Make this switchable for dev purposes.
			$path    = plugins_url( "dist/$file.js", dirname( __FILE__ ) );
			wp_enqueue_script( $name, $path, $deps, $version, true );
		}

		/**
		 * Outputs the debug page in settings
		 */
		private function output_settings_debug() {
			$log_file = dirname( __DIR__ ) . '/debug.log';

			if ( '1' === $this->get_value( self::GET_VAR_CLEAR_LOGS ) ) {
				// @codingStandardsIgnoreStart
				$fh = fopen( $log_file, 'w' );
				fclose( $fh );
				wp_safe_redirect( $this->unbound_nonce_url( self::GET_VAR_CLEAR_LOGS ) );
				// @codingStandardsIgnoreEnd
				exit;
			}

			$logs = 'none';

			$GLOBALS['hide_save_button'] = true;

			if ( file_exists( $log_file ) ) {
				// @codingStandardsIgnoreStart
				$logs = file_get_contents( $log_file );
				// @codingStandardsIgnoreEnd
			}

			if ( '1' === $this->get_value( self::GET_VAR_TEST_SSL ) ) {
				// SSL check logic.
				// Note: I found how to do this here: https://tecadmin.net/test-tls-version-php/.
				$response    = wp_remote_get( 'https://www.howsmyssl.com/a/check' );
				$body        = $response['body'];
				$tls_version = json_decode( $body )->tls_version;
				$data        = "<p>TLS Version: $tls_version</p>\n<p>Full Data: $body</p>\n";

				set_transient( 'wc-siftsci-ssl-log', $data );
				wp_safe_redirect( $this->unbound_nonce_url( self::GET_VAR_TEST_SSL ) );
				exit;
			}

			echo '<h2>SSL Check</h2>';
			echo '<p>Starting in September 2020, Sift.com will require TLS1.2. Click "Test SSL" to test your store.</p>';
			$ssl_data = get_transient( 'wc-siftsci-ssl-log' );
			if ( false !== $ssl_data ) {
				delete_transient( 'wc-siftsci-ssl-log' );
				echo wp_kses( $ssl_data, self::ALLOWED_HTML );
			}

			$ssl_url = $this->bound_nonce_url( self::GET_VAR_TEST_SSL, '1' );
			echo '<a href="' . esc_url( $ssl_url ) . '" class="button-primary woocommerce-save-button">Test SSL</a>';

			// Display logs.
			echo '<h2>Logs</h2>';
			echo '<p>' . nl2br( esc_html( $logs ) ) . '</p>';

			$log_url = $this->bound_nonce_url( self::GET_VAR_CLEAR_LOGS, '1' );
			echo '<a href="' . esc_url( $log_url ) . '" class="button-primary woocommerce-save-button">Clear Logs</a>';
		}

		/**
		 * Outputs the reporting tab in settings
		 */
		private function output_settings_reporting() {
			if ( '1' === $this->get_value( self::GET_VAR_RESET_GUID ) ) {
				delete_option( WC_SiftScience_Options::GUID );
				wp_safe_redirect( $this->unbound_nonce_url( self::GET_VAR_RESET_GUID ) );
				exit();
			}
			$url = $this->bound_nonce_url( self::GET_VAR_RESET_GUID, '1' );

			$reset_anchor = '<a href="' . $url . '">Reset</a>';
			$anonymous_id = $this->options->get_guid();

			$this->html->display_reporting_text( $anonymous_id, $reset_anchor );
			WC_Admin_Settings::output_fields( $this->get_settings_reporting() );
		}

		/**
		 * Outputs the stats page in settings
		 */
		private function output_settings_stats() {
			$GLOBALS['hide_save_button'] = true;

			if ( '1' === $this->get_value( self::GET_VAR_CLEAR_STATS ) ) {
				$this->stats->clear_stats();
				wp_safe_redirect( $this->unbound_nonce_url( self::GET_VAR_CLEAR_STATS ) );
				exit;
			}

			echo '<h2>Statistics</h2>';

			$stats = get_option( WC_SiftScience_Options::STATS, 'none' );
			if ( 'none' === $stats ) {
				echo '<p>No stats stored yet</p>';
				return;
			}

			$stats = json_decode( $stats, true );
			ksort( $stats );

			$stats_tables = '';

			foreach ( $stats as $outer_k => $outer_v ) {

				$outer_k = '<span style="color:#00a0d2">' . str_replace( '::', '</span>::', $outer_k );

				$stats_tables .= <<< STATS_TABLE
				<table><thead>
					<tr>
						<th scope="colgroup" colspan="2" style="text-align:left"> $outer_k: </th>
					</tr>
				</thead>
				<tbody>
STATS_TABLE;

				foreach ( array_reverse( $outer_v ) as $inner_k => $inner_v ) {
					$stats_tables .= '<tr><td style="width:50px">' . $inner_k . '</td><td>' . $inner_v . '</td></tr>';
				}

				$stats_tables .= '</tbody></table><br>';
			}

			echo wp_kses( $stats_tables, self::ALLOWED_HTML );

			$url = $this->bound_nonce_url( self::GET_VAR_CLEAR_STATS, '1' );
			echo '<a href="' . esc_url( $url ) . '" class="button-primary woocommerce-save-button">Clear Stats</a>';
		}

		/**
		 * This function is filling form element in the HTML page {Reporting}.
		 *
		 * @return Array []
		 */
		private function get_settings_reporting() {

			return array(
				$this->create_element(
					WC_SiftScience_Html::WC_TITLE_ELEMENT,
					'siftsci_title_reporting',
					'Sift Debug & Reporting Settings'
				),

				$this->create_element(
					WC_SiftScience_Html::WC_CHECKBOX_ELEMENT,
					WC_SiftScience_Options::SEND_STATS,
					'Enable Reporting',
					'Send the plugin developers statistics and error details.',
					array(
						'desc_tip' => '<em>More info</em> <a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">here</a>.',
					)
				),

				$this->create_element(
					WC_SiftScience_Html::WC_SELECT_ELEMENT,
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

				$this->create_element(
					WC_SiftScience_Html::WC_SECTIONEND_ELEMENT,
					'sifsci_section_reporting'
				),
			);
		}

		/**
		 * Saves the settings
		 */
		public function save_settings() {
			global $current_section;
			switch ( $current_section ) {
				case '':
					WC_Admin_Settings::save_fields( $this->get_settings_main() );
					$is_api_working = $this->check_api();
					update_option( WC_SiftScience_Options::IS_API_SETUP, $is_api_working ? 1 : 0 );
					if ( $is_api_working ) {
						WC_Admin_Settings::add_message( 'API is correctly configured' );
					} else {
						WC_Admin_Settings::add_error( 'API settings are broken' );
					}
					break;
				case 'reporting':
					WC_Admin_Settings::save_fields( $this->get_settings_stats() );
					break;
				default:
					break;
			}
		}

		/**
		 * Adds the settings tabs in Woo configs
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
		 * This function is filling form element in the main HTML page {Sift sittings}.
		 *
		 * @return Array []
		 */
		private function get_settings_main() {
			return array(
				$this->create_element(
					WC_SiftScience_Html::WC_TITLE_ELEMENT,
					'siftsci_title',
					'Sift Settings'
				),

				$this->create_element(
					WC_SiftScience_Html::WC_TEXT_ELEMENT,
					WC_SiftScience_Options::API_KEY,
					'Rest API Key',
					'The API key for production'
				),

				$this->create_element(
					WC_SiftScience_Html::WC_TEXT_ELEMENT,
					WC_SiftScience_Options::JS_KEY,
					'Javascript Snippet Key',
					'Javascript snippet key for production'
				),

				$this->create_element(
					WC_SiftScience_Html::WC_NUMBER_ELEMENT,
					WC_SiftScience_Options::THRESHOLD_GOOD,
					'Good Score Threshold',
					'Scores below this value are considered good and shown in green',
					array(
						'default' => 30,
						'min'     => 0,
						'max'     => 100,
						'step'    => 1,
						'css'     => 'width:75px;',
					)
				),

				$this->create_element(
					WC_SiftScience_Html::WC_NUMBER_ELEMENT,
					WC_SiftScience_Options::THRESHOLD_BAD,
					'Bad Score Threshold',
					'Scores above this value are considered bad and shown in red',
					array(
						'default' => 60,
						'min'     => 0,
						'max'     => 100,
						'step'    => 1,
						'css'     => 'width:75px;',
					)
				),
				$this->create_element(
					WC_SiftScience_Html::WC_TEXT_ELEMENT,
					WC_SiftScience_Options::NAME_PREFIX,
					'User & Order Name Prefix',
					'Prefix to give order and user names. Useful when you have have multiple stores and one Sift account.'
				),

				$this->create_element(
					WC_SiftScience_Html::WC_CHECKBOX_ELEMENT,
					WC_SiftScience_Options::AUTO_SEND_ENABLED,
					'Automatically Send Data',
					'Automatically send data to Sift when an order is created'
				),

				$this->create_element(
					WC_SiftScience_Html::WC_NUMBER_ELEMENT,
					WC_SiftScience_Options::MIN_ORDER_VALUE,
					'Auto Send Minimum Value',
					'Orders less than this value will not be automatically sent to sift. Set to zero to send all orders.',
					array(
						'default' => 0,
						'min'     => 0,
						'step'    => 1,
						'css'     => 'width:75px;',
					)
				),

				$this->create_element(
					WC_SiftScience_Html::WC_SECTIONEND_ELEMENT,
					'sifsci_section_main'
				),
			);
		}

		/**
		 * This function sets HTML element attributes according to woocommearce provided library.
		 * desc_tip Mixed [bool:false] (default)
		 *     field type of checkbox; the desc text is going next to the control
		 *     field type of select, number or text; the desc text is going underneath control
		 * desc_tip Mixed [bool:true]
		 *     field type of check box; the desc text is going underneath control
		 *     field type of select, number or text; a question mark pop-up appears before control with desc text
		 * desc_tip Mixed [string]
		 *     field type of checkbox; the text is going underneath control
		 *     field type of select, number or text; a question mark pop-up appears before control with desc tip
		 * note: currently desc_tip can only be added as element_options
		 *
		 * @param string $type            Element type name.
		 * @param string $id              HtmlElement ID.
		 * @param string $label           Element label.
		 * @param string $desc            Description text.
		 * @param array  $element_options Element special options.
		 *
		 * @return array $element         An array of attributes.
		 * @since 1.1.0
		 */
		private function create_element( $type, $id, $label = '', $desc = '', $element_options = array() ) {

			$element = array();

			$custom_attributes = array(); // array flattener.

			if ( WC_SiftScience_Html::WC_SELECT_ELEMENT === $type ) {
				if ( ! isset( $element_options['options'] ) || empty( $element_options['options'] ) ) {
					$this->logger->log_error( 'Drop down ' . $id . ' cannot be empty!' );
					return;
				}
			}

			switch ( $type ) {

				case WC_SiftScience_Html::WC_NUMBER_ELEMENT:
					if ( isset( $element_options['min'] ) ) {
						$custom_attributes['min'] = $element_options['min'];
						unset( $element_options['min'] );
					}
					if ( isset( $element_options['max'] ) ) {
						$custom_attributes['max'] = $element_options['max'];
						unset( $element_options['max'] );
					}
					if ( isset( $element_options['step'] ) ) {
						$custom_attributes['step'] = $element_options['step'];
						unset( $element_options['step'] );
					}
					// Number field min, nax and step values saved and unseted to avoid duplicates.

				case WC_SiftScience_Html::WC_TEXT_ELEMENT:
				case WC_SiftScience_Html::WC_CHECKBOX_ELEMENT:
				case WC_SiftScience_Html::WC_SELECT_ELEMENT:
					if ( ! empty( $element_options ) ) {
						$element = array_merge( $element, $element_options );
					}
					// $element_options added.

				case WC_SiftScience_Html::WC_TITLE_ELEMENT:
					if ( ! empty( $desc ) ) {
						$element['desc'] = $desc;
					}

					if ( ! empty( $label ) ) {
						$element['title'] = $label;
					}
					// Title and description are added all What's left [id and type].

				case WC_SiftScience_Html::WC_SECTIONEND_ELEMENT:
					$element = array_merge(
						$element,
						array(
							'id'   => $id,
							'type' => $type,
						)
					);
					break;

				default:
					$this->logger->log_error( $type . ' is not a valid type!' );
					break;
			}

			if ( ! empty( $custom_attributes ) ) {
				$element['custom_attributes'] = $custom_attributes;
			}

			return $element;
		}

		/**
		 * This function handles admin-notices action and decides to show update, improve notices
		 */
		public function settings_notice() {
			// Check to display update notice.
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );

				$is_admin_page = strpos( $uri, 'tab=siftsci' );
				$is_valid_conf = ( false === $is_admin_page || $this->options->is_setup() );

				if ( false === $is_valid_conf ) {
					$settings_url = admin_url( 'admin.php?page=wc-settings&tab=siftsci' );

					$this->html->disply_update_notice( $settings_url );
				}
			}
			// Check to dispkay improve notice.
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
		 * This function will validate GET var with its nonce
		 *
		 * @param String $var_name the  get variable name.
		 *
		 * @return String|False $result if the get var and it's nonce are valid return it's value else return false.
		 */
		private function get_value( $var_name ) {
			$nonce_name = $this->get_nonce_name( $var_name );

			// Check that nonce is valid and input value exists.
			$is_valid_input = isset( $_GET[ $var_name ], $_GET[ $nonce_name ] )
				&& wp_verify_nonce( sanitize_key( $_GET[ $nonce_name ] ), $this->get_nonce_name( $var_name ) );

			if ( false === $is_valid_input ) {
				return false;
			}

			return sanitize_key( $_GET[ $var_name ] );
		}

		/**
		 * This function attaches '_nonce' to the get variable name
		 *
		 * @param String $get_var the GET array variable.
		 *
		 * @return String concatenated nonce name.
		 */
		private function get_nonce_name( $get_var ) {
			return $get_var . '_nonce';
		}

		/**
		 * Creates a variable URL with it's nonce respectivly
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
		 * Retunning a URL dispatching the required GET var and the nonce related
		 *
		 * @param String $get_var_name the required GET variable.
		 *
		 * @return String the dispatched URL from the GET var and it's nonce.
		 */
		private function unbound_nonce_url( $get_var_name ) {
			return remove_query_arg( array( $get_var_name, $this->get_nonce_name( $get_var_name ) ) );
		}
	}
endif;
