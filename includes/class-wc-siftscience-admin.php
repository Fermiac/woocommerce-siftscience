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
		private const NONCE_HOOK  = 'woocommerce_settings_siftsci';

		private const ALLOWED_HTML = array(
			'li'    => array(),
			'table' => array(),
			'thead' => array(),
			'tbody' => array(),
			'p'     => array(),
			'tr'    => array(),
			'br'    => array( 'class' => array() ),
			'div'   => array( 'class' => array() ),
			'td'    => array( 'style' => array() ),
			'span'  => array( 'style' => array() ),
			'style' => array( 'type' => array() ),
			'ul'    => array( 'class' => array() ),
			'a'     => array(
				'class'  => array(),
				'href'   => array(),
				'target' => array(),
			),
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
		 *
		 * @global String $current_section
		 */
		public function get_sections() {
			global $current_section;
			$selected_section = empty( $current_section ) ? 'main' : $current_section;

			$sections = array(
				'main'      => 'Settings',
				'reporting' => 'Reporting',
				'stats'     => 'Stats',
				'debug'     => 'Debug',
			);

			$tabs = array();
			foreach ( $sections as $id => $label ) {
				$url    = admin_url( 'admin.php?page=wc-settings&tab=' . self::ADMIN_ID . '&section=' . sanitize_title( $id ) );
				$class  = $selected_section === $id ? 'current' : '';
				$tabs[] = '<a href="' . $url . '" class="' . $class . '">' . $label . '</a>';
			}

			$tabs_html = '<li>' . join( ' | </li>', $tabs ) . '</li>';
			echo wp_kses( '<ul class="subsubsub">' . $tabs_html . '</ul><br class="clear" />', self::ALLOWED_HTML );
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
			WC_Admin_Settings::output_fields( $this->get_settings() );

			$this->styling_checkbox_label( WC_SiftScience_Options::AUTO_SEND_ENABLED );

			echo <<<'table'
			<table class="form-table"><tbody>
			<tr valign="top">
			<th scope="row" class="titledesc"><label>Batch Upload</label></th>
			<td class="forminp forminp-text"><div id="batch-upload"></div></td>
			</tr>
			</tbody></table>
table;

			self::enqueue_script( 'wc-siftsci-vuejs', 'vue-dev', array() );
			self::enqueue_script( 'wc-siftsci-control', 'BatchUpload.umd', array( 'wc-siftsci-vuejs' ) );
			self::enqueue_script( 'wc-siftsci-script', 'batch-upload', array( 'wc-siftsci-control' ) );
			wp_localize_script( 'wc-siftsci-script', '_siftsci_app_data', array( 'api' => admin_url( 'admin-ajax.php' ) ) );
		}

		/**
		 * Echoing the style rule for the next sibbling of checkbox label to display inline
		 *
		 * @param string $label_for same of The ID of the checkbox html validation.
		 */
		private function styling_checkbox_label( $label_for ) {
			$html = '<style type="text/css">label[for="%1$s"]+p{display:inline}</style>';
			echo wp_kses( sprintf( $html, $label_for ), self::ALLOWED_HTML );
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

			if ( '1' === $this->get_value( 'clear_logs', self::NONCE_HOOK ) ) {
				// @codingStandardsIgnoreStart
				$fh = fopen( $log_file, 'w' );
				fclose( $fh );
				wp_safe_redirect( $this->unbound_nonce_url( 'clear_logs' ) );
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

			if ( '1' === $this->get_value( 'test_ssl', self::NONCE_HOOK ) ) {
				// SSL check logic.
				// Note: I found how to do this here: https://tecadmin.net/test-tls-version-php/.
				// @codingStandardsIgnoreStart
				$ch = curl_init( 'https://www.howsmyssl.com/a/check' );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				$data = curl_exec( $ch );
				curl_close( $ch );
				// @codingStandardsIgnoreEnd

				$tls_version = json_decode( $data )->tls_version;

				$data = "<p>TLS Version: $tls_version</p>\n<p>Full Data: $data</p>\n";

				set_transient( 'wc-siftsci-ssl-log', $data );
				wp_safe_redirect( $this->unbound_nonce_url( 'test_ssl' ) );
				exit;
			}

			echo '<h2>SSL Check</h2>';
			echo '<p>Starting in September 2020, Sift.com will require TLS1.2. Click "Test SSL" to test your store.</p>';
			$ssl_data = get_transient( 'wc-siftsci-ssl-log' );
			if ( false !== $ssl_data ) {
				delete_transient( 'wc-siftsci-ssl-log' );
				echo wp_kses( $ssl_data, self::ALLOWED_HTML );
			}

			$ssl_url = $this->bound_nonce_url( 'test_ssl', '1', self::NONCE_HOOK );
			echo wp_kses( '<a href="' . $ssl_url . '" class="button-primary woocommerce-save-button">Test SSL</a>', self::ALLOWED_HTML );

			// Display logs.
			echo '<h2>Logs</h2>';
			echo wp_kses( '<p>' . nl2br( esc_html( $logs ) ) . '</p>', self::ALLOWED_HTML );

			$log_url = $this->bound_nonce_url( 'clear_logs', '1', self::NONCE_HOOK );
			echo wp_kses( '<a href="' . $log_url . '" class="button-primary woocommerce-save-button">Clear Logs</a>', self::ALLOWED_HTML );
		}

		/**
		 * Outputs the reporting tab in settings
		 */
		private function output_settings_reporting() {
			if ( '1' === $this->get_value( 'reset_guid', self::NONCE_HOOK ) ) {
				delete_option( WC_SiftScience_Options::GUID );
				wp_safe_redirect( $this->unbound_nonce_url( 'reset_guid' ) );
				exit();
			}
			WC_Admin_Settings::output_fields( $this->get_settings_stats() );
			$this->styling_checkbox_label( WC_SiftScience_Options::SEND_STATS );
		}

		/**
		 * Outputs the stats page in settings
		 */
		private function output_settings_stats() {
			$GLOBALS['hide_save_button'] = true;
			if ( '1' === $this->get_value( 'clear_stats', self::NONCE_HOOK ) ) {
				$this->stats->clear_stats();
				wp_safe_redirect( $this->unbound_nonce_url( 'clear_stats' ) );
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

			$url = $this->bound_nonce_url( 'clear_stats', '1', self::NONCE_HOOK );
			echo wp_kses( '<a href="' . $url . '" class="button-primary woocommerce-save-button">Clear Stats</a>', self::ALLOWED_HTML );
		}

		/**
		 * This function is filling form element in the HTML page {Reporting}.
		 *
		 * @return Array []
		 */
		private function get_settings_stats() {
			$reset_anchor = ' <a href="' . $this->bound_nonce_url( 'reset_guid', '1', self::NONCE_HOOK ) . '">Reset</a>';

			return array(
				$this->get_element(
					'title',
					'siftsci_stats_title',
					'Sift Stats and Debug Reporting',
					<<<TITLE
					<p> Help us improve this plugin by automatically reporting errors and statistics. All information is anonymous and cannot be traced back to your site. For details, click <a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">here</a>.</p>
					<p> Your anonymous id is: {$this->options->get_guid()} $reset_anchor </p>
TITLE
				),
				$this->get_element(
					'checkbox',
					WC_SiftScience_Options::SEND_STATS,
					'Enable Reporting',
					'Send the plugin developers statistics and error details. More info <a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">here</a>.'
				),
				$this->get_element(
					'select',
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
				$this->get_element( 'sectionend', 'sifsci_section_main' ),
			);
		}

		/**
		 * Saves the settings
		 */
		public function save_settings() {
			global $current_section;
			switch ( $current_section ) {
				case '':
					WC_Admin_Settings::save_fields( $this->get_settings() );
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
		private function get_settings() {
			return array(
				$this->get_element( 'title', 'siftsci_title', 'Sift Settings' ),
				$this->get_element( 'text', WC_SiftScience_Options::API_KEY, 'Rest API Key', 'The API key for production' ),
				$this->get_element( 'text', WC_SiftScience_Options::JS_KEY, 'Javascript Snippet Key', 'Javascript snippet key for production' ),

				$this->get_element(
					'number',
					WC_SiftScience_Options::THRESHOLD_GOOD,
					'Good Score Threshold',
					'Scores below this value are considered good and shown in green',
					array( 'default' => 30 )
				),

				$this->get_element(
					'number',
					WC_SiftScience_Options::THRESHOLD_BAD,
					'Bad Score Threshold',
					'Scores above this value are considered bad and shown in red',
					array( 'default' => 60 )
				),
				$this->get_element(
					'text',
					WC_SiftScience_Options::NAME_PREFIX,
					'User & Order Name Prefix',
					'Prefix to give order and user names. Useful when you have have multiple stores and one Sift account.'
				),

				$this->get_element( 'checkbox', WC_SiftScience_Options::AUTO_SEND_ENABLED, 'Automatically Send Data', 'Automatically send data to Sift when an order is created' ),

				$this->get_element(
					'number',
					WC_SiftScience_Options::MIN_ORDER_VALUE,
					'Minimum Order Value for Auto Send',
					'Orders less than this value will not be automatically sent to sift. Set to zero to send all orders.',
					array( 'default' => 0 )
				),

				$this->get_element( 'sectionend', 'sifsci_section_main' ),
			);
		}

		/**
		 * This function sets HTML element attributes according to woocommearce provided library.
		 *
		 * @param string $type            Element type name.
		 * @param string $id              HtmlElement ID.
		 * @param string $title           Element label.
		 * @param string $desc            Question mark hilper title.
		 * @param array  $element_options Element special options.
		 *
		 * @return array $element         An array of attributes.
		 */
		private function get_element( $type, $id, $title = '', $desc = '', $element_options = array() ) {

			$element = array(
				'type' => $type,
				'id'   => $id,
			);

			switch ( $type ) {
				case 'sectionend':
					return $element;
				case 'title':
					return array_merge(
						$element,
						array(
							'title' => $title,
							'desc'  => $desc,
						)
					);
				case 'number':
				case 'select':
					if ( ! empty( $element_options ) ) {
						$element = array_merge( $element, $element_options );
					} elseif ( 'select' === $type ) {
						$this->logger->log_error( 'Drop down ' . $id . ' cannot be empty!' );
						break;
					}
					// Select and number may have a Description.
				case 'checkbox':
				case 'text':
					if ( ! empty( $desc ) ) {

						$element = array_merge(
							$element,
							array(
								'desc'     => $desc,
								'desc_tip' => true,
							)
						);

					}
					$element['title'] = $title;
					break;
				default:
					$this->logger->log_error( $type . ' is not a valid type!' );
					break;
			}

			return $element;
		}

		/**
		 * Calls the functions to decide if we need to show notices
		 */
		public function settings_notice() {
			$this->notice_config();
			$this->notice_stats();
		}

		/**
		 * Creates the notice for when sift is not correctly configured
		 */
		private function notice_config() {
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$uri           = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
				$is_admin_page = strpos( $uri, 'tab=siftsci' );
				if ( false === $is_admin_page || $this->options->is_setup() ) {
					return;
				}
			}

			$link   = admin_url( 'admin.php?page=wc-settings&tab=siftsci' );
			$anchor = '<a href="' . $link . '">here</a>';
			$notice = <<<NOTICE
			<div class="notice notice-error is-dismissible">
			<p>Sift configuration is invalid. Click $anchor to update.</p>
			</div>
NOTICE;
			echo wp_kses( $notice, self::ALLOWED_HTML );
		}

		/**
		 * Displays the notice for opting in/out of stats
		 */
		private function notice_stats() {
			$enabled = get_option( WC_SiftScience_Options::SEND_STATS, 'not_set' );

			// Reusable strings.
			$set_siftsci_key = 'set_siftsci_stats';
			$sift_key_hook   = 'settings_notice';

			if ( 'not_set' !== $enabled ) {
				return;
			}

			$value = $this->get_value( $set_siftsci_key, $sift_key_hook );
			if ( false !== $value ) {
				update_option( WC_SiftScience_Options::SEND_STATS, $value );
				wp_safe_redirect( $this->unbound_nonce_url( $set_siftsci_key ) );
				exit;
			}

			$no_anchor  = '<a href="' . $this->bound_nonce_url( $set_siftsci_key, 'no', $sift_key_hook ) . '">Disable</a>';
			$yes_anchor = '<a href="' . $this->bound_nonce_url( $set_siftsci_key, 'yes', $sift_key_hook ) . '">Enable</a>';

			$link_info      = 'https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection';
			$details_anchor = '<a target="_blank" href="' . $link_info . '">more info</a>';

			$message = 'Please help improve Sift for WooCommerce by enabling Stats and Error Reporting.';

			$improve = <<<IMPROVE
			<div class="notice notice-error is-dismissible">
				<p> $message $yes_anchor, $no_anchor, $details_anchor. </p>
			</div>
IMPROVE;
			echo wp_kses( $improve, self::ALLOWED_HTML );
		}
		/**
		 * This function will validate GET var with its nonce
		 *
		 * @param String $var_name the  get variable name.
		 * @param String $hook the hook in which the nonce was created for.
		 *
		 * @return String|False $result if the get var and it's nonce are valid return it's value else return false.
		 */
		private function get_value( $var_name, $hook ) {
			$nonce_name = $this->get_nonce_name( $var_name );

			// Check that nonce is valid and input value exists.
			$is_valid_input = isset( $_GET[ $var_name ], $_GET[ $nonce_name ] )
				&& wp_verify_nonce( sanitize_key( $_GET[ $nonce_name ] ), $hook );
			if ( ! $is_valid_input ) {
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
		 * @param String $hook          the related nonce hook.
		 *
		 * @return String bounded link with a get var and it's nonce.
		 */
		private function bound_nonce_url( $get_var_name, $get_var_value, $hook ) {
			$url = add_query_arg( array( $get_var_name => $get_var_value ) );
			$url = wp_nonce_url( $url, $hook, $this->get_nonce_name( $get_var_name ) );
			return $url;
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
