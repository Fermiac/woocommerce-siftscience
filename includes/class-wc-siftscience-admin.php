<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles the plugin's settings page.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Admin' ) ) :

	require_once( 'class-wc-siftscience-options.php' );

	class WC_SiftScience_Admin {
		private $id = 'siftsci';
		private $label = 'SiftScience';
		private $options;
		private $logger;
		private $stats;

		public function __construct( WC_SiftScience_Options $options, WC_SiftScience_Comm $comm,
			WC_SiftScience_Logger $logger, WC_SiftScience_Stats $stats )
		{
			$this->options = $options;
			$this->comm = $comm;
			$this->logger = $logger;
			$this->stats = $stats;
		}

		public function check_api() {
			// try requesting a non-existent user score and see that the response isn't a permission fail
			$response = $this->comm->get_user_score( '_dummy_' . rand( 1000, 9999 ) );
			$this->logger->log_info( '[api check response] ' . json_encode( $response ) );
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

			echo '<ul class="subsubsub">';
			$array_keys = array_keys( $sections );

			foreach ( $sections as $id => $label ) {
				echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $this->id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> ' . ( end( $array_keys ) == $id ? '' : '|' ) . ' </li>';
			}

			echo '</ul><br class="clear" />';
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

			echo $this->batch_upload();
			$data = array( 'api' => admin_url( 'admin-ajax.php' ) );

            wp_enqueue_script( 'wc-siftsci-vuejs', plugins_url( "dist/vue-dev.js", dirname( __FILE__ ) ), array(), time(), true );
            wp_enqueue_script( 'wc-siftsci-control', plugins_url( "dist/BatchUpload.umd.js", dirname( __FILE__ ) ), array('wc-siftsci-vuejs'), time(), true );
            wp_enqueue_script( 'wc-siftsci-script', plugins_url( "dist/batch-upload.js", dirname( __FILE__ ) ), array('wc-siftsci-control'), time(), true );
			wp_localize_script( 'wc-siftsci-script', "_siftsci_app_data", $data );
		}

		private function output_settings_debug() {
			$log_file = dirname( __DIR__ ) . '/debug.log';
			if ( isset( $_GET[ 'clear_logs' ] ) ) {
				$url = home_url( remove_query_arg( 'clear_logs' ) );
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
			$logs = nl2br( esc_html( $logs ) );
			echo '<h2>Logs</h2>';
			echo "<p>$logs</p>";
			$url = home_url( add_query_arg( array( 'clear_logs' => 1 ) ) );
			echo "<a href='$url' class=\"button-primary woocommerce-save-button\">Clear Logs</a>";
		}

		private function output_settings_reporting() {
			WC_Admin_Settings::output_fields( $this->get_settings_stats() );
		}

		private function output_settings_stats() {
			$GLOBALS[ 'hide_save_button' ] = true;
			if ( isset( $_GET[ 'clear_stats' ] ) ) {
				$url = home_url( remove_query_arg( 'clear_stats' ) );
				$this->stats->clear_stats();
				wp_redirect( $url );
				exit;
			}

			$stats = get_option( WC_SiftScience_Options::$stats, 'none' );
			if ( 'none' === $stats ) {
				echo '<p>No stats stored yet</p>';
				return;
			}

			$stats = json_decode( $stats );
			$stats = json_encode( $stats, JSON_PRETTY_PRINT );
			echo "<pre>$stats</pre>";
			$url = home_url( add_query_arg( array( 'clear_stats' => 1 ) ) );
			echo "<a href='$url' class=\"button-primary woocommerce-save-button\">Clear Stats</a>";
		}

		private function get_settings_stats() {
			return array(
				array(
					'title' => 'SiftScience Stats and Debug Reporting',
					'type' => 'title',
					'desc' => '<p>Help us improve this plugin by automatically reporting errors and statistics. ' .
					          'All information is anonymous and cannot be traced back to your site. ' .
					          'For details, click <a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">here</a>.</p>' .
					          'Your anonymous id is: ' . $this->options->get_guid(),
					'id' => 'siftsci_stats_title'
				),

				$this->get_check_box( WC_SiftScience_Options::$send_stats,
					'Enable Reporting',
					'Send the plugin developers statistics and error details. More info <a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">here</a>.</p>'
				),

				$this->get_drop_down( WC_SiftScience_Options::$log_level_key,
					'Log Level',
					'How much logging information to generate',
					array( 2 => 'Errors', 1 => 'Errors & Warnings', 0 => 'Errors, Warnings & Info' )
				),

				$this->get_section_end( 'sifsci_section_main' ),
			);
		}

		public function save_settings() {
			global $current_section;
			switch ( $current_section ) {
				case '':
					WC_Admin_Settings::save_fields( $this->get_settings() );
					$is_api_working = $this->check_api() ? 1 : 0;
					update_option( WC_SiftScience_Options::$is_api_setup, $is_api_working );
					if ( $is_api_working === 1 ) {
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
			$pages[$this->id] = $this->label;
			return $pages;
		}

		private function get_settings() {
			return array(
				$this->get_title( 'siftsci_title', 'Sift Settings' ),

				$this->get_text_input( WC_SiftScience_Options::$api_key,
					'Rest API Key', 'The API key for production' ),

				$this->get_text_input( WC_SiftScience_Options::$js_key,
					'Javascript Snippet Key', 'Javascript snippet key for production' ),

				$this->get_number_input( WC_SiftScience_Options::$threshold_good,
					'Good Score Threshold', 'Scores below this value are considered good and shown in green', 30),

				$this->get_number_input( WC_SiftScience_Options::$threshold_bad,
					'Bad Score Threshold', 'Scores above this value are considered bad and shown in red', 60 ),

				$this->get_text_input( WC_SiftScience_Options::$name_prefix,
					'User & Order Name Prefix',
					'Prefix to give order and user names. '
					. 'Useful when you have have multiple stores and one Sift Science account.' ),

				$this->get_check_box( WC_SiftScience_Options::$send_on_create_enabled,
					'Automatically send data',
					'Automatically send data to SiftScience when an order is created'
				),

				$this->get_section_end( 'sifsci_section_main' ),
			);
		}

		private function get_title( $id, $title ) {
			return array( 'title' => $title, 'type' => 'title', 'desc' => '', 'id' => $id );
		}

		private function get_text_input( $id, $title, $desc ) {
			return array(
				'title' => $title,
				'desc' => $desc,
				'desc_tip' => true,
				'type' => 'text',
				'id' => $id,
			);
		}

		private function get_number_input( $id, $title, $desc, $default ) {
			return array(
				'title' => $title,
				'desc' => $desc,
				'desc_tip' => true,
				'type' => 'number',
				'id' => $id,
				'default' => $default,
			);
		}

		private function get_section_end( $id ) {
			return array( 'type' => 'sectionend', 'id' => $id );
		}

		private function get_check_box( $id, $title, $desc ) {
			return array(
				'title' => $title,
				'desc' => $desc,
				'desc_tip' => true,
				'type' => 'checkbox',
				'id' => $id,
			);
		}

		private function get_drop_down( $id, $title, $desc, $options ) {
			return array(
				'id' => $id,
				'title' => $title,
				'desc' => $desc,
				'desc_tip' => true,
				'options' => $options,
				'type' => 'select',
			);
		}

		public function settings_notice() {
			$this->notice_config();
			$this->notice_stats();
		}

		private function notice_config() {
			$uri = $_SERVER[ 'REQUEST_URI' ];
			$is_admin_page = ( strpos( $uri, 'tab=siftsci') > 0 ) ? true : false;
			if ( $is_admin_page || $this->options->is_setup() ) {
				return;
			}

			$link = admin_url( 'admin.php?page=wc-settings&tab=siftsci' );
			$here = "<a href='$link'>here</a>";
			echo "<div class='notice notice-error is-dismissible'>" .
			     "<p>SiftScience configuration is invalid. Click $here to update.</p>" .
			     "</div>";
		}

		private function notice_stats() {
			$s3k = 'set_siftsci_stats'; // a reusable string
			$enabled = get_option( WC_SiftScience_Options::$send_stats, 'not_set' );
			if ( 'not_set' !== $enabled ) {
				return;
			}

			if ( isset( $_GET[ $s3k ] ) ) {
				$value = $_GET[ $s3k ];
				update_option( WC_SiftScience_Options::$send_stats, $value );
				$url = remove_query_arg( $s3k );
				wp_redirect( $url );
				exit;
			}

			$link_yes = add_query_arg( array( $s3k => 'yes' ) );
			$link_no = add_query_arg( array( $s3k => 'no' ) );
			
			$yes = "<a href='$link_yes'>Enable</a>";
			$no = "<a href='$link_no'>disable</a>";

			$link_info = 'https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection';
			$details = "<a target='_blank' href='$link_info'>more info</a>";

			$message = 'Please help improve Sift Science for WooCommerce by enabling Stats and Error Reporting.';

			echo '<div class=\'notice notice-error is-dismissible\'>'.
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
