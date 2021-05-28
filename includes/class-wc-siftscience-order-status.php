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
	 * Class WC_SiftScience_Order_Status
	 */
	class WC_SiftScience_Order_Status {

		/**
		 * The options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * WC_SiftScience_Order_Status constructor.
		 *
		 * @param WC_SiftScience_Options $options Options service.
		 */
		public function __construct( WC_SiftScience_Options $options ) {
			$this->options = $options;
		}

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
		public function try_update_order_status( WC_Order $order, $score ) {
			$threshold_good = $this->options->get_threshold_good();
			$threshold_bad  = $this->options->get_threshold_bad();

			if ( $score < $threshold_good ) {
				$note  = 'Sift score is good. Order status updated.';
				$value = $this->options->get_status_if_good();
			} elseif ( $score > $threshold_bad ) {
				$note  = 'Sift score is bad. Order status updated.';
				$value = $this->options->get_status_if_bad();
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
