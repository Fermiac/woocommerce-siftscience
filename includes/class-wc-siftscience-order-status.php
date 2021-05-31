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
		 * The communications service
		 *
		 * @var WC_SiftScience_Comm
		 */
		private $comm;

		/**
		 * WC_SiftScience_Order_Status constructor.
		 *
		 * @param WC_SiftScience_Options $options Options service.
		 */
		public function __construct( WC_SiftScience_Comm $comm, WC_SiftScience_Options $options ) {
			$this->comm    = $comm;
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
		 */
		public function try_update_order_status( WC_Order $order ) {
			$settings  = $this->options->get_order_auto_update_settings();
			$good_from = $settings['good_from'];
			$good_to   = $settings['good_to'];
			$bad_from  = $settings['bad_from'];
			$bad_to    = $settings['bad_to'];

			// Abort if there are no actions configure.
			if ( ! in_array( 'non', array( $good_to, $bad_to ) ) ) {
				return;
			}

			// Abort if the current status is not one of the configured "from" statuses.
			$status = $order->get_status();
			if ( ! in_array( $status, array( $good_from, $bad_from ) ) ) {
				return;
			}

			$user_id = $this->options->get_user_id( $order );
			$result  = $this->comm->get_user_score( $user_id );

			// abort if sift.com doesn't return a score
			if ( ! isset( $result, $result['scores'], $result['scores']['payment_abuse'], $result['scores']['payment_abuse']['score'] ) ) {
				return;
			}

			$score = $result['scores']['payment_abuse']['score'] * 100;

			$threshold_good = $this->options->get_threshold_good();
			$threshold_bad  = $this->options->get_threshold_bad();

			$note  = null;
			$value = null;

			if ( $score <= $threshold_good && $status === $good_from ) {
				$note  = 'Sift score is good. Order status updated.';
				$value = $good_to;
			} elseif ( $score >= $threshold_bad && $status === $bad_from ) {
				$note  = 'Sift score is bad. Order status updated.';
				$value = $bad_to;
			}

			if ( null !== $value ) {
				$order->set_status( $value, $note );
			}
		}
	}

endif;
