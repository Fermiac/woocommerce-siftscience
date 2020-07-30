<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles the display of Sift feedback icons in order list and order view.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Orders' ) ) :

	require_once( 'class-wc-siftscience-html.php' );
	require_once( 'class-wc-siftscience-options.php' );

	class WC_SiftScience_Orders {
		private $options;

		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		public function create_row( $column ) {
			if ( $column == 'siftsci' ) {
				global $post;
				$id = $post->ID;
				echo "<div class='siftsci-order' id='siftsci-order-$id' data-id='$id'></div>\n";
			}
		}

		private function add_react_app() {
			$data = array(
				'imgPath' => plugins_url( 'images/', dirname( __FILE__ ) ),
				'api' => admin_url( 'admin-ajax.php' ),
				'thresholdGood' => $this->options->get_threshold_good(),
				'thresholdBad' => $this->options->get_threshold_bad(),
			);

            wp_enqueue_script( 'wc-siftsci-vuejs', plugins_url( "dist/vue-dev.js", dirname( __FILE__ ) ), array(), time(), true );
            wp_enqueue_script( 'wc-siftsci-control', plugins_url( "dist/OrderControl.umd.js", dirname( __FILE__ ) ), array('wc-siftsci-vuejs'), time(), true );
            wp_enqueue_script( 'wc-siftsci-script', plugins_url( "dist/order-control.js", dirname( __FILE__ ) ), array('wc-siftsci-control'), time(), true );
            wp_localize_script( 'wc-siftsci-script', "_siftsci_app_data", $data );
		}

		public function create_header( $columns ) {
			$newcolumns = array();

			foreach ( $columns as $k => $v ) {
				$newcolumns[$k] = $v;

				if ( $k == 'order_status' ) {
					$newcolumns['siftsci'] = 'Sift';
				}
			}

			$this->add_react_app();

			return $newcolumns;
		}

		public function add_meta_box() {
			add_meta_box(
				'wc_sift_score_meta_box',
				'Sift Fraud score',
				array( $this, 'display_siftsci_box' ),
				'shop_order',
				'side',
				'high'
			);
		}

		public function display_siftsci_box() {
			global $post;
			$id = $post->ID;
			echo "<div class='siftsci-order' id='siftsci-order-$id' data-id='$id'></div>\n";
			$this->add_react_app();
		}
	}

endif;
