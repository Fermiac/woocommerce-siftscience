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

if ( ! class_exists( 'WC_SiftScience_Order_Status' ) ) :

	/**
	 * Class WC_SiftScience_Orders
	 */
	class WC_SiftScience_Order_Status {
		/**
		 * Gets a list of available statuses
		 *
		 * @return array The full list of statuses and order can have.
		 */
		public function get_status_options() {
			$result = array( 'none' => 'Do Nothing' );
			foreach ( wc_get_order_statuses() as $key => $val ) {
				$result[$key] = $val;
			}
			return $result;
		}
	}

endif;
