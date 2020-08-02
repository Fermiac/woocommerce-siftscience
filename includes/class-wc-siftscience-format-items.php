<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class format woocommerce items into the Sift format.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Format_Items' ) ) :

	class WC_SiftScience_Format_Items {
		private $options;

		public function __construct( WC_SiftScience_Options  $options ) {
			$this->options = $options;
		}

		public function get_order_items( WC_Order $order ) {
			$data = array();
			foreach( $order->get_items() as $item ) {
				$data[] = $this->create_item( $item );
			};
			return apply_filters( 'wc_siftscience_create_order_items', $data, $order );
		}

		/**
		 * https://sift.com/developers/docs/v204/curl/events-api/complex-field-types/item
		 * @param $wc_item
		 * @param WC_Order $order
		 * @return array
		 */
		public function create_item( $wc_item, WC_Order $order ) {
			$order_item = array(
				'$item_id'       => $this->options->get_sift_product_id( $wc_item[ 'product_id' ] ),
				'$product_title' => $wc_item[ 'name' ],
				'$currency_code' => $order->get_currency(),
				'$price'         => $wc_item[ 'line_subtotal' ] * 1000000,
				'$quantity'      => $wc_item[ 'qty' ],
			);
			return apply_filters( 'wc_siftscience_create_order_item', $order_item, $wc_item );
		}
	}

endif;