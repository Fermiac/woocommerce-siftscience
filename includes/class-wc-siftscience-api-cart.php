<?php
/**
 * This class format woocommerce cart events into the Sift format.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package siftscience-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Api_Cart' ) ) :

	require_once 'class-wc-siftscience-options.php';

	/**
	 * Class WC_SiftScience_Api_Cart
	 */
	class WC_SiftScience_Api_Cart {
		/**
		 * Options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * WC_SiftScience_Api_Cart constructor.
		 *
		 * @param WC_SiftScience_Options $options Options service.
		 */
		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		/**
		 * Add item to cart event data
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/add-item-to-cart
		 * @param string $cart_item_key ID for cart item.
		 *
		 * @return mixed|void
		 */
		public function add_to_cart( $cart_item_key ) {
			$cart = WC()->cart->get_cart();
			$item = $cart[ $cart_item_key ];

			$product_id = $item['product_id'];
			$product    = new WC_Product( $product_id );

			$data = array(
				'$type'       => '$add_item_to_cart',
				'$session_id' => $this->options->get_session_id(),
				'$item'       => array(
					'$item_id'       => $cart_item_key,
					'$product_title' => $product->get_title(),
					'$price'         => $product->get_price() * 1000000, // $39.99
					'$currency_code' => get_woocommerce_currency(),
					'$quantity'      => $item['quantity'],
				),
			);

			$user_id = get_current_user_id();
			if ( 0 !== $user_id ) {
				$data['$user_id'] = $this->options->get_sift_user_id( $user_id );
			}

			return apply_filters( 'wc_siftscience_add_to_cart', $data );
		}

		/**
		 * Remove from cart event data
		 *
		 * @link https://sift.com/developers/docs/curl/events-api/reserved-events/remove-item-from-cart
		 * @param string $cart_item_key Cart item key.
		 *
		 * @return array
		 */
		public function remove_from_cart( $cart_item_key ) {
			$cart       = WC()->cart->get_cart();
			$item       = $cart[ $cart_item_key ];
			$product_id = $item['product_id'];
			$product    = new WC_Product( $product_id );
			$data       = array(
				'$type'       => '$remove_item_from_cart',
				'$session_id' => $this->options->get_session_id(),
				'$item'       => array(
					'$item_id'       => $cart_item_key,
					'$product_title' => $product->get_title(),
					'$price'         => $product->get_price() * 1000000, // $39.99
					'$currency_code' => get_woocommerce_currency(),
					'$quantity'      => $item['quantity'],
				),
			);

			$user_id = get_current_user_id();
			if ( 0 !== $user_id ) {
				$data['$user_id'] = $this->options->get_sift_user_id( $user_id );
			}

			return apply_filters( 'wc_siftscience_remove_from_cart', $data );
		}
	}

endif;
