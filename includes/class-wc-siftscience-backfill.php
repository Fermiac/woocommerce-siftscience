<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles backfilling information about an order to SiftScience
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Backfill' ) ) :
	include_once( 'class-wc-siftscience-comm.php' );

	class WC_SiftScience_Backfill {
		private $comm;
		private $meta_key;

		public function __construct() {
			$this->comm = new WC_SiftScience_Comm;
			$options = new WC_SiftScience_Options();
			$this->meta_key = $options->get_backfill_meta_key();
		}

		public function backfill( $post_id ) {
			if ( $this->is_backfilled( $post_id ) ) return false;

			$this->create_order( $post_id );

			update_post_meta( $post_id, $this->meta_key, '1' );
		}

		public function is_backfilled( $post_id ) {
			$is_backfilled = get_post_meta( $post_id, $this->meta_key, true );
			return $is_backfilled === '1';
		}

		public function unset_backfill( $post_id ) {
			delete_post_meta( $post_id, $this->meta_key );
		}

		private function create_order( $order_id ) {
			$order = new WC_Order( $order_id );
			$ord_arr = $this->create_order_array( $order );
			$result = $this->comm->post_event( '$create_order', $order->user_id, $ord_arr );
			return $result;
		}

		private function create_order_array( $order ) {
			return array(
				'$order_id'         => $order->get_order_number(),
				'$user_email'       => $order->billing_email,
				'$time'             => strtotime( $order->order_date ),
				'$amount'           => $order->get_total() * 1000000,
				'$currency_code'    => 'USD',
				'$billing_address'  => $this->create_address( $order, 'billing' ),
				'$shipping_address' => $this->create_address( $order, 'shipping' ),
				'$items'            => $this->create_item_array( $order ),
			);
		}

		private function create_address( $order, $type = 'shipping' ) {
			return array(
				'$name'      => $this->get_order_param( $order, $type, '_first_name' )
					. ' ' . $this->get_order_param( $order, $type, '_last_name' ),
				'$address_1' => $this->get_order_param( $order, $type, '_address_1' ),
				'$address_2' => $this->get_order_param( $order, $type, '_address_2' ),
				'$city'      => $this->get_order_param( $order, $type, '_city' ),
				'$region'    => $this->get_order_param( $order, $type, '_state' ),
				'$country'   => $this->get_order_param( $order, $type, '_country' ),
				'$zipcode'   => $this->get_order_param( $order, $type, '_postcode' ),
				'$phone'     => '',
			);
		}

		private function get_order_param( $order, $type, $param ) {
			$key = $type . $param;
			return $order->$key;
		}

		private function create_item_array( $order ) {
			$item_arr = array();
			foreach ( $order->get_items() as $wc_item ) {
				array_push( $item_arr, $this->create_item( $wc_item ) );
			}
			return $item_arr;
		}

		private function create_item( $wc_item ) {
			return array(
				'$item_id'       => $wc_item['product_id'],
				'$product_title' => $wc_item['name'],
				'$price'         => $wc_item['line_subtotal'],
				'$quantity'      => $wc_item['qty'],
			);
		}
	}

endif;
