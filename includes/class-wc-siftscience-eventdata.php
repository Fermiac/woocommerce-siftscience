<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class creates data to be sent to SiftScience based on the type of event occurring.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_EventData' ) ) :
	class WC_SiftScience_EventData {
		private $order = null;
		private $user = null;
		private $data = null;
		private $item = null;
		private $product = null;
		private $options;

		public function __construct( $data, $options ) {
			$this->data = $data;
			$this->options = $options;

			if ( isset( $data['order_id'] ) ) {
				$this->order = new WC_Order( $data['order_id'] );
				$this->user = $this->order->get_user();
			} else {
				$this->user = new WP_User( $data['user_id'] );
			}

			if ( isset( $data['item_id'] ) ) {
				$this->item = WC()->cart->get_cart_item( $data['item_id'] );
				$this->product = wc_get_product( $this->item['product_id'] );
			}
		}

		private static $event_fields = array(
			'$create_order'   	=> array( '$user_id', '$session_id', '$order_id', '$user_email', '$payment_methods',
								'$shipping_address', '$expedited_shipping', '$items', '$seller_user_id' ),
			'$transaction'    	=> array( '$user_id', '$user_email', '$transaction_type', '$transaction_status',
								'$amount', '$currency_code', '$order_id', '$transaction_id', '$billing_address',
								'$payment_method', '$shipping_address', '$session_id', '$seller_user_id'
			),
			'$create_account' 	=> array( '$user_id', '$session_id', '$user_email', '$name',
								'$phone', '$referrer_user_id',
								//'$payment_methods', '$billing_address',
								'$social_sign_on_type' ),
			'$update_account' 	=> array( '$user_id', '$changed_password', '$user_email', '$name',
								//'$phone',
								'$referrer_user_id', '$payment_methods',
								//'$billing_address',
								'$social_sign_on_type' ),
			'$add_item_to_cart'			=> array( '$session_id', '$user_id', '$item' ),
			'$remove_item_from_cart' 	=> array( '$session_id', '$user_id', '$item' ),
			/* TODO: not currently supported
			'$submit_review' => array('$user_id', '$session_id', '$content', '$review_title',
				'$item_id', '$reviewed_user_id', '$submission_status'),
			'$send_message' => array('$user_id', '$session_id', '$recipient_user_id', '$subject', '$content'),
			*/
			'$login'                 => array( '$session_id', '$user_id', '$login_status' ),
			'$logout'                => array( '$user_id' ),
			'$link_session_to_user'  => array( '$user_id', '$session_id' ),
		);

		public function get( $data ) {
			if ( ! isset( self::$event_fields[$data['event']] ) ) {
				return false;
			}

			$event = $data['event'];
			$this->data = $data;

			$result = array();
			$result['$type'] = $event;

			foreach ( self::$event_fields[$event] as $key ) {
				$val = $this->get_field( $key );
				if ( $val !== false ) {
					$result[$key] = $val;
				}
			}

			return $result;
		}

		private function get_field( $key ) {
			switch ( $key ) {
				case '$amount':
					return $this->order->get_total() * 1000000;
				case '$billing_address':
					return $this->get_billing_address();
				case '$changed_password':
					return $this->get_data( "changed_password" );
				//case '$content':
				case '$currency_code':
					return $this->order->order_currency;
				//case '$expedited_shipping':
				case '$item':
					return $this->get_item_info();
				case '$item_id':
					return false;
				case '$items':
					return $this->get_items();
				case '$login_status':
					return $this->get_data( 'login_status' );
				case '$name':
					return $this->user->first_name . ' ' . $this->user->last_name;
				case '$order_id':
					return $this->order->get_order_number();
				case '$payment_method':
					return $this->order->payment_method;
				//case '$payment_methods':
				//case '$phone':
				//case '$recipient_user_id':
				//case '$referrer_user_id':
				//case '$review_title':
				//case '$reviewed_user_id':
				//case '$seller_user_id':
				case '$session_id':
					return $this->options->get_session_id();
				case '$shipping_address':
					return $this->get_shipping_address();
				//case '$social_sign_on_type':
				//case '$subject':
				//case '$submission_status':
				case '$transaction_id':
					return $this->order->get_transaction_id();
				//case '$transaction_status':
				//case '$transaction_type':
				case '$user_email':
					return $this->order != null ? $this->order->billing_email : $this->user->email;
				case '$user_id':
					return $this->user !== null ? $this->user->ID : false;
				default:
					return false;
			}
		}

		private function get_data( $key ) {
			return isset( $this->data[$key] ) ? $this->data[$key] : false;
		}

		private function get_item( $item ) {
			return array(
				'$item_id'       => $item['product_id'],
				'$product_title' => $item['name'],
				'$price'         => $item['line_subtotal'] * 10000,
				//'$currency_code'  => "USD",
				//'$upc'            => "67862114510011",
				//'$sku'            => "004834GQ",
				//'$brand'          => "Slanket",
				//'$manufacturer'   => "Slanket",
				//'$category'       => "Blankets & Throws",
				//'$tags'           => ["Awesome", "Wintertime specials"],
				//'$color'          => "Texas Tea",
				'$quantity'      => $item['qty'],
			);
		}

		private function get_items() {
			$result = array();
			foreach ( $this->order->get_items() as $item ) {
				$result[] = $this->get_item( $item );
			}
			return $result;
		}

		private function get_shipping_address() {
			return array(
				'$name'      => $this->order->shipping_first_name
					. ' ' . $this->order->shipping_last_name,
				'$address_1' => $this->order->shipping_address_1,
				'$address_2' => $this->order->shipping_address_2,
				'$city'      => $this->order->shipping_city,
				'$region'    => $this->order->shipping_state,
				'$country'   => $this->order->shipping_country,
				'$zipcode'   => $this->order->shipping_postcode,
				'$phone'     => $this->order->shipping_phone,
			);
		}

		private function get_billing_address() {
			return array(
				'$name'      => $this->order->billing_first_name
					. ' ' . $this->order->billing_last_name,
				'$address_1' => $this->order->billing_address_1,
				'$address_2' => $this->order->billing_address_2,
				'$city'      => $this->order->billing_city,
				'$region'    => $this->order->billing_state,
				'$country'   => $this->order->billing_country,
				'$zipcode'   => $this->order->billing_postcode,
				'$phone'     => $this->order->billing_phone,
			);
		}

		private function get_item_info() {
			return array(
				'$item_id'       => $this->product->id,
				'$product_title' => $this->product->get_title(),
				'$price'         => $this->product->price * 10000,
				//'$currency_code'  => 'USD',
				//'$isbn'           => '0446576220',
				//'$sku'            => '10101042',
				//'$brand'          => 'Writers of the Round Table Press',
				//'$manufacturer'   => 'eBook Digital Services, Inc.',
				//'$category'       => 'Business books',
				//'$tags'           => ['reprint', 'paperback', 'Tony Hsieh'],
				'$quantity'      => $this->item['quantity']

			);
		}
	}

endif;
