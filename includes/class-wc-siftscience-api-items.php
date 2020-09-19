<?php
/**
 * This class format woocommerce items into the Sift format.
 *
 * @author  Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Api_Items' ) ) :

	/**
	 * Class WC_SiftScience_Api_Items
	 */
	class WC_SiftScience_Api_Items {
		/**
		 * Options service.
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * WC_SiftScience_Api_Items constructor.
		 *
		 * @param WC_SiftScience_Options $options Options service.
		 */
		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		/**
		 * Fetches order items from the order
		 *
		 * @param WC_Order $order The order object.
		 *
		 * @return array
		 */
		public function get_order_items( WC_Order $order ) {
			$data = array();
			foreach ( $order->get_items() as $item ) {
				$data[] = $this->create_item( $item, $order );
			};
			return apply_filters( 'wc_siftscience_create_order_items', $data, $order );
		}

		/**
		 * Create item event.
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/complex-field-types/item
		 * @param WC_Order_Item $wc_item The order item.
		 * @param WC_Order      $order The order of the order item.
		 *
		 * @return array
		 */
		private function create_item( WC_Order_Item $wc_item, WC_Order $order ) {
			$data = $wc_item->get_data();

			$order_item = array(
				'$item_id'       => $this->options->get_sift_product_id( $data['product_id'] ),
				'$product_title' => $data['name'],
				'$currency_code' => $order->get_currency(),
				'$price'         => $data['subtotal'] * 1000000,
				'$quantity'      => $wc_item->get_quantity(),
			);
			return apply_filters( 'wc_siftscience_create_order_item', $order_item, $wc_item );
		}
	}

endif;
