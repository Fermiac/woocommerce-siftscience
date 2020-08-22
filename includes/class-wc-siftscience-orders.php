<?php
/**
 * This class handles the display of Sift feedback icons in order list and order view.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Orders' ) ) :

	require_once 'class-wc-siftscience-html.php';
	require_once 'class-wc-siftscience-options.php';

	/**
	 * Class WC_SiftScience_Orders
	 */
	class WC_SiftScience_Orders {
		private const ALLOWED_HTML = array(
			'div' => array(
				'id'      => array(),
				'class'   => array(),
				'data-id' => array(),
			),
		);

		/**
		 * The options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * WC_SiftScience_Orders constructor.
		 *
		 * @param WC_SiftScience_Options $options The options service.
		 */
		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		/**
		 * Outputs the html to the row in orders list page
		 *
		 * @param string $column The current column name.
		 */
		public function create_row( $column ) {
			if ( 'siftsci' === $column ) {
				$this->output_order_control_div();
			}
		}

		/**
		 * Adds the VueJS app for the order control
		 * TODO: Rename this function
		 */
		private function add_react_app() {
			$data = array(
				'imgPath'       => plugins_url( 'images/', dirname( __FILE__ ) ),
				'api'           => admin_url( 'admin-ajax.php' ),
				'thresholdGood' => $this->options->get_threshold_good(),
				'thresholdBad'  => $this->options->get_threshold_bad(),
			);

			wp_enqueue_script( 'wc-siftsci-vuejs', plugins_url( 'dist/vue-dev.js', dirname( __FILE__ ) ), array(), time(), true );
			wp_enqueue_script( 'wc-siftsci-control', plugins_url( 'dist/OrderControl.umd.js', dirname( __FILE__ ) ), array( 'wc-siftsci-vuejs' ), time(), true );
			wp_enqueue_script( 'wc-siftsci-script', plugins_url( 'dist/order-control.js', dirname( __FILE__ ) ), array( 'wc-siftsci-control' ), time(), true );
			wp_localize_script( 'wc-siftsci-script', '_siftsci_app_data', $data );
		}

		/**
		 * Adds the header for the column on the orders list page.
		 * The header is added after the order_status column
		 *
		 * @param array $columns The columns list to be filtered.
		 *
		 * @return array The new list of colums with the new column added
		 */
		public function create_header( $columns ) {
			$newcolumns = array();

			foreach ( $columns as $k => $v ) {
				$newcolumns[ $k ] = $v;

				if ( 'order_status' === $k ) {
					$newcolumns['siftsci'] = 'Sift';
				}
			}

			$this->add_react_app();

			return $newcolumns;
		}

		/**
		 * Adds a meta box on the orders page
		 */
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

		/**
		 * Display the siftscience div for the VueJS control
		 */
		public function display_siftsci_box() {
			$this->output_order_control_div();
			$this->add_react_app();
		}

		/**
		 * Outputs the div for the Order Control VueJS component
		 */
		private function output_order_control_div() {
			global $post;
			$id   = $post->ID;
			$html = "<div class='siftsci-order' id='siftsci-order-$id' data-id='$id'></div>\n";
			echo wp_kses( $html, self::ALLOWED_HTML );
		}
	}

endif;
