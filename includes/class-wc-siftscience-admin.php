<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles the plugin's settings page.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Admin' ) ) :

	require_once( 'class-wc-siftscience-options.php' );
	require_once( 'class-wc-siftscience-html.php' );

	class WC_SiftScience_Admin {
		private const ADMIN_ID = 'siftsci';
		private const ADMIN_LABEL = 'Sift';
		private $_options;
		private $_logger;
		private $_stats;
		private $_comm;
		private $html;

		public function __construct(
				WC_SiftScience_Options $options,
				WC_SiftScience_Comm $comm,
				WC_SiftScience_Html $html,
				WC_SiftScience_Logger $logger,
				WC_SiftScience_Stats $stats ) {
			$this->_options = $options;
			$this->_comm    = $comm;
			$this->html     = $html;
			$this->_logger  = $logger;
			$this->_stats   = $stats;
		}

		public function check_api() {
			// try requesting a non-existent user score and see that the response isn't a permission fail.
			$response = $this->_comm->get_user_score( '_dummy_' . rand( 1000, 9999 ) );
			$this->_logger->log_info( '[api check response] ' . json_encode( $response ) );
			return isset( $response->status ) && ( $response->status === 0 || $response->status === 54 );
		}

		public function get_sections() {
			global $current_section;
			$sections  = array(
				'' => 'Settings',
				'reporting' => 'Reporting',
				'stats' => 'Stats',
				'debug' => 'Debug',
			);

			$tabs = array();
			foreach ( $sections as $id => $label ) {
				$url = admin_url( 'admin.php?page=wc-settings&tab=' . self::ADMIN_ID . '&section=' . sanitize_title( $id ) );
				$class = $current_section == $id ? 'current' : '';
				$tabs[] = "<a href='$url' class='$class'>$label</a>";
			}

			$tabs_html = '<li>' . join( ' | </li>', $tabs ) . '</li>';
			echo "<ul class='subsubsub'>$tabs_html</ul><br class='clear' />";
		}

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

		private function output_settings_main() {
			WC_Admin_Settings::output_fields( $this->get_settings() );

			echo $this->styling_checkbox_label( WC_SiftScience_Options::AUTO_SEND_ENABLED );
			echo $this->batch_upload();
			$data = array( 'api' => admin_url( 'admin-ajax.php' ) );

			self::enqueue_script( 'wc-siftsci-vuejs', 'vue-dev', array() );
			self::enqueue_script( 'wc-siftsci-control', 'BatchUpload.umd', array( 'wc-siftsci-vuejs' ) );
			self::enqueue_script( 'wc-siftsci-script', 'batch-upload', array( 'wc-siftsci-control' ) );
			wp_localize_script( 'wc-siftsci-script', "_siftsci_app_data", $data );
		}

		private function styling_checkbox_label( $label_for ) {
			return sprintf( '<style type="text/css">label[for="%1$s"]+p{display:inline}</style>', $label_for );
		}

		private static function enqueue_script( $name, $file, $deps ) {
			$version = time(); // TODO: Make this switchable for dev purposes.
			$path = plugins_url( "dist/$file.js", dirname( __FILE__ ) );
			wp_enqueue_script( $name, $path, $deps, $version, true );
		}

		private function output_settings_debug() {
			$log_file = dirname( __DIR__ ) . '/debug.log';
			if ( isset( $_GET[ 'clear_logs' ] ) ) {
				$url =  remove_query_arg( 'clear_logs' );
				$fh = fopen( $log_file, 'w' );
				fclose( $fh );
				wp_redirect( $url );
				exit;
			}

			$GLOBALS[ 'hide_save_button' ] = true;
			$logs = 'none';
			if ( file_exists( $log_file ) ) {
				$logs = file_get_contents( $log_file );
			}

			// SSL check logic.
			// Note: I found how to do this here: https://tecadmin.net/test-tls-version-php/.
			if ( isset( $_GET[ 'test_ssl' ] ) ) {
				$ch = curl_init( 'https://www.howsmyssl.com/a/check' );
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
				$data = curl_exec( $ch );
				curl_close( $ch );
				$tls_version = json_decode( $data )->tls_version;
				$data =  "<p>TLS Version: $tls_version</p>\n<p>Full Data: $data</p>\n";
				set_transient( 'wc-siftsci-ssl-log', $data );
				wp_redirect( remove_query_arg( 'test_ssl' ) );
				exit;
			}

			echo '<h2>SSL Check</h2>';
			echo '<p>Starting in September 2020, Sift.com will require TLS1.2. Click "Test SSL" to test your store.</p>';
			$ssl_data = get_transient( 'wc-siftsci-ssl-log' );
			if ( false !== $ssl_data ) {
				delete_transient( 'wc-siftsci-ssl-log' );
				echo $ssl_data;
			}
			$ssl_url = add_query_arg( array( 'test_ssl' => 1 ) );
			echo "<a href='$ssl_url' class='button-primary woocommerce-save-button'>Test SSL</a>";

			// Display logs.
			echo '<h2>Logs</h2>';
			echo '<p>' . nl2br( esc_html( $logs ) ) . '</p>';
			echo '<a href="' . add_query_arg( array( 'clear_logs' => 1 ) ) . '" class="button-primary woocommerce-save-button">Clear Logs</a>';
		}

		private function output_settings_reporting() {
			if ( isset( $_GET[ 'reset_guid' ] ) ) {
				if ( $_GET[ 'reset_guid' ] == 1 ) {
					$url = remove_query_arg( 'reset_guid' );
					delete_option( WC_SiftScience_Options::GUID );
					wp_redirect( $url );
					exit(); 
				}
			}
			WC_Admin_Settings::output_fields( $this->get_settings_stats() );
			echo $this->styling_checkbox_label( WC_SiftScience_Options::SEND_STATS );
		}

		private function output_settings_stats() {
			$GLOBALS[ 'hide_save_button' ] = true;
			if ( isset( $_GET[ 'clear_stats' ] ) ) {
				$url = remove_query_arg( 'clear_stats' );
				$this->_stats->clear_stats();
				wp_redirect( $url );
				exit;
			}

			echo '<h2>Statistics</h2>';

			$stats = get_option( WC_SiftScience_Options::STATS, 'none' );
			if ( $stats === 'none' ) {
				echo '<p>No stats stored yet</p>';
				return;
			}

			$stats = json_decode( $stats , true );
			ksort( $stats );

			foreach ( $stats as $outer_k => $outer_v ) {

				$outer_k = '<span style="color:#00a0d2">' . str_replace( '::', '</span>::', $outer_k );

				echo '<table><thead>',
					'<tr><th scope="colgroup" colspan="2" style="text-align:left">' . $outer_k . ':</th></tr>',
					'</thead><tbody>';

				foreach ( array_reverse( $outer_v ) as $inner_k => $inner_v ) {
					echo '<tr><td style="width:50px">' . $inner_k . '</td><td>' . $inner_v . '</td></tr>';
				}

				echo '</tbody></table><br>';
			}
			$url = add_query_arg( array( 'clear_stats' => 1 ) );
			echo '<a href="' . $url . '" class="button-primary woocommerce-save-button">Clear Stats</a>';
		}
		/**
		 * This function is filling form element in the HTML page {Reporting}.
		 *
		 * @return Array []
		 */
		private function get_settings_stats() {
			$reset_url    = add_query_arg( array( 'reset_guid' => 1 ) );
			$reset_anchor = ' <a href="' . $reset_url . '">Reset</a>';

			return array(
				$this->get_element(
					'title',
					'siftsci_stats_title',
					'Sift Stats and Debug Reporting',
					'<p>Help us improve this plugin by automatically reporting errors and statistics. ' .
					'All information is anonymous and cannot be traced back to your site. For details, click ' .
					'<a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">here</a>.</p>' .
					'Your anonymous id is: ' . $this->_options->get_guid() . $reset_anchor
				),
				$this->get_element(
					'checkbox',
					WC_SiftScience_Options::SEND_STATS,
					'Enable Reporting',
					'Send the plugin developers statistics and error details. More info <a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">here</a>.</p>'
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
		 * @param String $type            Element type name.
		 * @param String $id              HtmlElement ID.
		 * @param String $title           Element label.
		 * @param String $desc            Question mark hilper title.
		 * @param Array  $element_options Element special options.
		 *
		 * @return Array $element         An array of attributes.
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
						$this->_logger->log_error( 'Drop down ' . $id . ' cannot be empty!' );
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
					$this->_logger->log_error( $type . ' is not a valid type!' );
					break;
			}

			return $element;
		}

		public function settings_notice() {
			$this->notice_config();
			$this->notice_stats();
		}

		private function notice_config() {
			$uri = $_SERVER[ 'REQUEST_URI' ];
			$is_admin_page = strpos( $uri, 'tab=siftsci') > 0;
			if ( $is_admin_page || $this->_options->is_setup() ) {
				return;
			}

			$link = admin_url( 'admin.php?page=wc-settings&tab=siftsci' );
			$here = "<a href='$link'>here</a>";
			echo "<div class='notice notice-error is-dismissible'>" .
			     "<p>Sift configuration is invalid. Click $here to update.</p>" .
			     "</div>";
		}

		private function notice_stats() {
			$set_siftsci_key = 'set_siftsci_stats'; // a reusable string.
			$enabled = get_option( WC_SiftScience_Options::SEND_STATS, 'not_set' );
			if ( 'not_set' !== $enabled ) {
				return;
			}

			if ( isset( $_GET[ $set_siftsci_key ] ) ) {
				$value = $_GET[ $set_siftsci_key ];
				update_option( WC_SiftScience_Options::SEND_STATS, $value );
				$url = remove_query_arg( $set_siftsci_key );
				wp_redirect( $url );
				exit;
			}

			$link_yes = add_query_arg( array( $set_siftsci_key => 'yes' ) );
			$link_no = add_query_arg( array( $set_siftsci_key => 'no' ) );
			
			$yes = "<a href='$link_yes'>Enable</a>";
			$no = "<a href='$link_no'>disable</a>";

			$link_info = 'https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection';
			$details = "<a target='_blank' href='$link_info'>more info</a>";

			$message = 'Please help improve Sift for WooCommerce by enabling Stats and Error Reporting.';

			echo '<div class="notice notice-error is-dismissible">'.
			     "<p> $message $yes, $no, $details. </p>" .
			     '</div>';
		}

		public function batch_upload() {
			return "<table class='form-table'><tbody>" .
			       "<tr valign='top'>" .
			       "<th scope='row' class='titledesc'><label>Batch Upload</label></th>" .
			       "<td class='forminp forminp-text'><div id='batch-upload'></div></td>" .
			       "</tr>" .
			       "</tbody></table>";
		}
	}

endif;
