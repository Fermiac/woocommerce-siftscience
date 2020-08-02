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
				$data[] = $this->create_item( $item, $order );
			};
			return apply_filters( 'wc_siftscience_create_order_items', $data, $order );
		}

		/**
		 *    array(
		 *        '$item_id'        => 'B004834GQO',
		 *        '$product_title'  => 'The Slanket Blanket-Texas Tea',
		 *        '$price'          => 39990000, // $39.99
		 *        '$upc'            => '67862114510011',
		 *        '$sku'            => '004834GQ',
		 *        '$brand'          => 'Slanket',
		 *        '$manufacturer'   => 'Slanket',
		 *        '$category'       => 'Blankets & Throws',
		 *        '$tags'           => array('Awesome', 'Wintertime specials'),
		 *        '$color'          => 'Texas Tea',
		 *        '$quantity'       => 2
		 *    )
		 *
		 * @param WC_Order_Item $wc_item
		 *
		 * @param WC_Order $order
		 *
		 * @return array
		 */
		private function create_item( WC_Order_Item $wc_item, WC_Order $order ) {
			$data = $wc_item->get_data();
			$order_item = array(
				'$item_id'       => $this->options->get_sift_product_id( $data[ 'product_id' ] ),
				'$product_title' => $data[ 'name' ],
				'$currency_code' => $order->get_currency(),
				'$price'         => $data[ 'subtotal' ] * 1000000,
				'$quantity'      => $wc_item->get_quantity(),
			);
			return apply_filters( 'wc_siftscience_create_order_item', $order_item, $wc_item );
		}
	}

endif;