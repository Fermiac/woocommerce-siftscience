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

	require_once __DIR__ . '/class-wc-siftscience-options.php';

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
				$result[ $key ] = $val;
			}

			return $result;
		}

		/**
		 * Checks the order sift score and updates the order status if needed
		 *
		 * @param WC_Order $order Order to update.
		 * @param int      $score The score received by the order.
		 */
		public function try_update_order_status( WC_Order $order, int $score ) {
			$threshold_good = get_option( WC_SiftScience_Options::THRESHOLD_GOOD );
			$threshold_bad  = get_option( WC_SiftScience_Options::THRESHOLD_BAD );

			if ( $score < $threshold_good ) {
				$note  = 'Sift score is good. Order status updated.';
				$value = get_option( WC_SiftScience_Options::ORDER_STATUS_IF_GOOD, 'none' );
			} elseif ( $score > $threshold_bad ) {
				$note  = 'Sift score is bad. Order status updated.';
				$value = get_option( WC_SiftScience_Options::ORDER_STATUS_IF_BAD, 'none' );
			} else {
				// Score is in mid range. Do Nothing.
				return;
			}

			if ( 'none' !== $value ) {
				$order->set_status( $value, $note );
			}
		}
	}

endif;
