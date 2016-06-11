<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles the display of SiftScience feedback icons in order list and order view.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Hooks_Orders' ) ) :

	include_once( 'class-wc-siftscience-html.php' );
	include_once( 'class-wc-siftscience-options.php' );

	class WC_SiftScience_Hooks_Orders {
		private $options;

		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		public function run() {
			add_filter( 'manage_edit-shop_order_columns', array( $this, 'create_header' ), 100 );
			add_action( 'manage_shop_order_posts_custom_column', array( $this, 'create_row' ), 11 );

			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		}

		public function create_row( $column ) {
			if ( $column == 'siftsci' ) {
				global $post;
				$id = $post->ID;
				echo "<div class='siftsci-order' id='siftsci-order-$id' data-id='$id'>hello</div>\n";
			}
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

			$js_vars = array( 'url' => plugins_url( 'woocommerce-siftscience/wc-siftscience-score.php' ) );
			WC_SiftScience_Html::enqueue_script( 'wc-siftsci-order', $js_vars );

			$jsPath = $this->options->get_react_app_path();
			$imgPath = plugins_url( 'images/', dirname( __FILE__ ) );
			$data = array(
				'imgPath' => $imgPath,
				'apiUrl' => plugins_url( 'api.php', dirname( __FILE__ ) ),
			);
			wp_enqueue_script( 'wc-siftsci-react-app', $jsPath, array(), false, true );
			wp_localize_script( 'wc-siftsci-react-app', "_siftsci_app_input_data", $data );

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
			WC_SiftScience_Html::score_label_icons( false );

			$js_vars = array( 'url' => plugins_url( 'woocommerce-siftscience/wc-siftscience-score.php' ) );
			WC_SiftScience_Html::enqueue_script( 'wc-siftsci-order', $js_vars );
		}
	}

endif;
