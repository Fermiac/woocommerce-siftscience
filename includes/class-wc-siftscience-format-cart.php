<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class format woocommerce cart events into the SiftScience format.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Format_Cart' ) ) :

	require_once 'class-wc-siftscience-options.php';

	class WC_SiftScience_Format_Cart {
		private $options;

		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/add-item-to-cart
		public function add_to_cart( $cart_item_key ) {
			$data = array(
				'$type'       =>  '$add_item_to_cart',
				'$session_id' => $this->options->get_session_id(),
				'$item'       => array(
					'$item_id'        => $cart_item_key,
					//'$product_title'  => 'The Slanket Blanket-Texas Tea',
					//'$price'          => 39990000, // $39.99
					//'$currency_code'  => 'USD',
					//'$upc'            => '67862114510011',
					//'$sku'            => '004834GQ',
					//'$brand'          => 'Slanket',
					//'$manufacturer'   => 'Slanket',
					//'$category'       => 'Blankets & Throws',
					//'$tags'           => ['Awesome', 'Wintertime specials'],
					//'$color'          => 'Texas Tea',
					//'$quantity'       => 16,
				)
			);

			$user_id = get_current_user_id();
			if ( 0 !== $user_id ) {
				$data[ '$user_id' ] = $this->options->get_user_id_from_user_id( $user_id );
			}

			return apply_filters( 'wc_siftscience_add_to_cart', $data );
		}

		// https://siftscience.com/developers/docs/curl/events-api/reserved-events/remove-item-from-cart
		public function remove_from_cart( $cart_item_key ) {
			$data = array(
				'$type'       => '$remove_item_from_cart',
				//'$user_id'    => 'billy_jones_301',
				// Supported Fields
				'$session_id' => $this->options->get_session_id(),
				'$item'       => array(
					'$item_id'        => $cart_item_key,
					//'$product_title'  => 'The Slanket Blanket-Texas Tea',
					//'$price'          => 39990000, // $39.99
					//'$currency_code'  => 'USD',
					//'$quantity'       => 2,
					//'$upc'            => '67862114510011',
					//'$sku'            => '004834GQ',
					//'$brand'          => 'Slanket',
					//'$manufacturer'   => 'Slanket',
					//'$category'       => 'Blankets & Throws',
					//'$tags'           => ['Awesome', 'Wintertime specials'],
					//'$color'          => 'Texas Tea'
				)
			);

			$user_id = get_current_user_id();
			if ( 0 !== $user_id ) {
				$data[ '$user_id' ] = $this->options->get_user_id_from_user_id( $user_id );
			}

			return apply_filters( 'wc_siftscience_remove_from_cart', $data );
		}
	}

endif;