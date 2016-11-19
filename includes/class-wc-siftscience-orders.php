<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles the display of SiftScience feedback icons in order list and order view.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Orders' ) ) :

	include_once( 'class-wc-siftscience-html.php' );
	include_once( 'class-wc-siftscience-options.php' );

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
			$jsPath = $this->options->get_react_app_path();
			$imgPath = plugins_url( 'images/', dirname( __FILE__ ) );
			$data = array(
				'imgPath' => $imgPath,
				'apiUrl' => admin_url( 'admin-ajax.php' ),
				'thresholdGood' => $this->options->get_threshold_good(),
				'thresholdBad' => $this->options->get_threshold_bad(),
			);
			wp_enqueue_script( 'wc-siftsci-react-app', $jsPath, array(), $this->options->get_version(), true );
			wp_localize_script( 'wc-siftsci-react-app', "_siftsci_app_input_data", $data );
		}

		public function create_header( $columns ) {
			$icon = 'Sift Sci';
			$header = WC_SiftScience_Html::tool_tip( $icon, 'SiftScience' );
			$html = WC_SiftScience_Html::div( $header, array( 'style' => 'width: 24px;' ) );
			$newcolumns = array();

			foreach ( $columns as $k => $v ) {
				$newcolumns[$k] = $v;
				if ( $k == 'order_status' ) {
					$newcolumns['siftsci'] = $html;
				}
			}

			$this->add_react_app();

			return $newcolumns;
		}

		public function add_meta_box() {
			add_meta_box(
				'wc_sift_score_meta_box',
				'SiftScience Fraud score',
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
