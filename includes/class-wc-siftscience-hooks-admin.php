<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles the plugin's settings page.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Hooks_Admin' ) ) :

	include_once( 'class-wc-siftscience-options.php' );

	class WC_SiftScience_Hooks_Admin {
		private $id = 'siftsci';
		private $label = 'SiftScience';
		private $settings;
		private $options;

		public function __construct()
		{
			$this->settings = $this->get_settings();
			$this->options = new WC_SiftScience_Options();
		}

		public function run() {
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 30 );
			add_action( 'woocommerce_settings_siftsci', array( $this, 'output_settings_fields' ) );
			add_action( 'woocommerce_settings_save_siftsci', array( $this, 'save_settings' ) );
			add_action( 'admin_notices', array( $this, 'settings_notice' ) );
		}

		public function check_api() {
			$comm = new WC_SiftScience_Comm();
			$response = $comm->get_user_score( 1 );
			return isset( $response->status ) && ( $response->status === 0 || $response->status === 54 );
		}

		public function output_settings_fields() {
			WC_Admin_Settings::output_fields( $this->settings );
		}

		public function save_settings() {
			WC_Admin_Settings::save_fields( $this->settings );
			$is_api_working = $this->check_api() ? 1 : 0;
			update_option( WC_SiftScience_Options::$is_api_setup, $is_api_working );
			if ( $is_api_working === 1 ) {
				WC_Admin_Settings::add_message( 'API is correctly configured' );
			} else {
				WC_Admin_Settings::add_error( 'API settings are broken' );
			}
		}

		public function add_settings_page( $pages ) {
			$pages[$this->id] = $this->label;
			return $pages;
		}

		private function get_settings() {
			return array(
				$this->get_title( 'siftsci_title', 'SiftScience Settings' ),

				$this->get_text_input( WC_SiftScience_Options::$api_key,
					'Rest API Key', 'The API key for production' ),

				$this->get_text_input( WC_SiftScience_Options::$js_key,
					'Javascript Snippet Key', 'Javascript snippet key for production' ),

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

		private function get_section_end( $id ) {
			return array( 'type' => 'sectionend', 'id' => $id );
		}

		private function get_radio_buttons( $id, $title, $desc, $options ) {
			return array(
				'title' => $title,
				'desc' => $desc,
				'desc_tip' => true,
				'type' => 'radio',
				'options' => $options,
				'id' => $id,
			);
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

		public function settings_notice() {
			$uri = $_SERVER['REQUEST_URI'];
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
	}

endif;
