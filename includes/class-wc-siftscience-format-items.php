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
		private $logger;

		public function __construct( WC_SiftScience_Options  $options, WC_SiftScience_Logger $logger ) {
			$this->options = $options;
			$this->logger  = $logger;
		}

		public function get_order_items( WC_Order $order ) {
			$data = array();
			foreach( $order->get_items() as $item ) {
				$data[] = $this->create_item( $item, $order );
			};
			return apply_filters( 'wc_siftscience_create_order_items', $data, $order );
		}

		/**
		 * https://sift.com/developers/docs/v204/curl/events-api/complex-field-types/item
		 * @param $wc_item
		 * @param WC_Order $order
		 * @return array
		 */
		public function create_item( WC_Order_Item $wc_item, WC_Order $order ) {
			$data = $wc_item->get_data();
			$this->logger->log_info( 'create_item data: ' . json_encode( $data ) );
			$order_item = array(
				'$item_id'       => $this->options->get_sift_product_id( $data[ 'product_id' ] ),
				'$product_title' => $data[ 'name' ],
				'$currency_code' => $order->get_currency(),
				'$price'         => $data[ 'line_subtotal' ] * 1000000,
				'$quantity'      => $data[ 'qty' ],
			);
			return apply_filters( 'wc_siftscience_create_order_item', $order_item, $wc_item );
		}
	}

endif;