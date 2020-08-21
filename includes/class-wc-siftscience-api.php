<?php
/**
 * This class handles the API request ( from the React components ).
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Api' ) ) :
	require_once 'class-wc-siftscience-comm.php';
	require_once 'class-wc-siftscience-events.php';
	require_once 'class-wc-siftscience-options.php';
	require_once 'class-wc-siftscience-logger.php';
	require_once 'class-wc-siftscience-stats.php';

	/**
	 * Class WC_SiftScience_Api
	 */
	class WC_SiftScience_Api {
		/**
		 * Communications service
		 *
		 * @var WC_SiftScience_Comm
		 */
		private $comm;

		/**
		 * Events service
		 *
		 * @var WC_SiftScience_Events
		 */
		private $events;

		/**
		 * Options service
		 *
		 * @var WC_SiftScience_Options
		 */
		private $options;

		/**
		 * Logger service
		 *
		 * @var WC_SiftScience_Logger
		 */
		private $logger;

		/**
		 * Stats service
		 *
		 * @var WC_SiftScience_Stats
		 */
		private $stats;

		/**
		 * WC_SiftScience_Api constructor.
		 *
		 * @param WC_SiftScience_Comm    $comm Communications service.
		 * @param WC_SiftScience_Events  $events Events service.
		 * @param WC_SiftScience_Options $options Options service.
		 * @param WC_SiftScience_Logger  $logger Logger service.
		 * @param WC_SiftScience_Stats   $stats Stats service.
		 */
		public function __construct(
				WC_SiftScience_Comm $comm,
				WC_SiftScience_Events $events,
				WC_SiftScience_Options $options,
				WC_SiftScience_Logger $logger,
				WC_SiftScience_Stats $stats ) {
			$this->comm    = $comm;
			$this->events  = $events;
			$this->options = $options;
			$this->logger  = $logger;
			$this->stats   = $stats;
		}

		/**
		 * Handle Ajax calls
		 */
		public function handle_ajax() {
			try {
				$id     = filter_input( INPUT_GET, 'id' );
				$action = filter_input( INPUT_GET, 'wcss_action' );
				$result = $this->handle_request( $action, $id );

				if ( isset( $result['status'] ) ) {
					http_response_code( $result['status'] );
				}

				$response = wp_json_encode( $result );
				$this->logger->log_info( '[ajax response] ' . $response );
				echo $response;
			} catch ( Exception $error ) {
				$this->logger->log_exception( $error );
				$this->stats->send_error( $error );
				http_response_code( 500 );
				echo wp_json_encode(
					array(
						'error'   => true,
						'code'    => $error->getCode(),
						'message' => $error->getMessage(),
						'file'    => $error->getFile(),
						'line'    => $error->getLine(),
					)
				);
			}

			wp_die();
		}

		/**
		 * Processes the incoming Ajax request
		 *
		 * @param string $action The action to be performed.
		 * @param string $order_id The ID of the order to perform the action on.
		 *
		 * @return array|array[] The request result
		 */
		private function handle_request( $action, $order_id ) {
			if ( ! is_super_admin() ) {
				return array(
					'status' => 401,
					'error'  => 'not allowed',
				);
			}

			if ( 'multi' === $action ) {
				return $this->get_orders( $order_id );
			}

			$user_id = 0;

			if ( $order_id ) {
				$user_id = $this->get_user_id( $order_id );
				if ( false === $user_id ) {
					return array(
						'status' => 400,
						'error'  => 'User not found for order',
					);
				}
			}

			switch ( $action ) {
				case 'score':
					break;
				case 'set_good':
					$this->comm->post_label( $user_id, false );
					break;
				case 'set_bad':
					$this->comm->post_label( $user_id, true );
					break;
				case 'unset':
					$this->comm->delete_label( $user_id );
					break;
				case 'backfill':
					$this->events->set_backfill( $order_id );
					$this->events->create_order( $order_id );
					$this->events->send_queued_data();
					break;
				case 'order_stats':
					return $this->list_stats();
				case 'clear_all':
					return $this->clear_all();
				default:
					return array(
						'status' => 400,
						'error'  => 'unknown action: ' . $action,
					);
			}

			return $this->get_score( $order_id, $user_id );
		}

		/**
		 * Get all orders
		 *
		 * @return int[]|WP_Post[] Posts that were found
		 */
		private function get_order_posts() {
			return get_posts(
				array(
					'numberposts' => -1,
					'post_type'   => array( 'shop_order' ),
					'post_status' => array_keys( wc_get_order_statuses() ),
				)
			);
		}

		/**
		 * List the summary of stats
		 *
		 * @return array[] Stats summary
		 */
		private function list_stats() {
			$backfilled     = array();
			$not_backfilled = array();
			$posts          = $this->get_order_posts();
			$meta_key       = $this->options->get_backfill_meta_key();

			foreach ( $posts as $post ) {
				if ( '1' === get_post_meta( $post->ID, $meta_key, true ) ) {
					$backfilled[] = $post->ID;
				} else {
					$not_backfilled[] = $post->ID;
				}
			}

			return array(
				'backfilled'    => $backfilled,
				'notBackfilled' => $not_backfilled,
			);
		}

		/**
		 * Clear all stored back-fill info
		 *
		 * @return array[]
		 */
		private function clear_all() {
			$posts    = $this->get_order_posts();
			$meta_key = $this->options->get_backfill_meta_key();
			foreach ( $posts as $post ) {
				delete_post_meta( $post->ID, $meta_key );
			}

			return $this->list_stats();
		}

		/**
		 * Get all order objects referenced in the list of ids
		 *
		 * @param string $order_ids List of order ids.
		 *
		 * @return array The orders
		 */
		private function get_orders( $order_ids ) {
			$result = array();
			$ids    = explode( ',', $order_ids );

			foreach ( $ids as $order_id ) {
				$user_id = $this->get_user_id( $order_id );
				if ( false === $user_id ) {
					continue;
				}

				$result[] = $this->get_score( $order_id, $user_id );
			}
			return $result;
		}

		/**
		 * Get the score.
		 *
		 * @param string $order_id Order id.
		 * @param string $user_id User id.
		 *
		 * @return array Score information
		 */
		private function get_score( $order_id, $user_id ) {
			$backfill_meta_key = $this->options->get_backfill_meta_key();
			$is_backfilled     = '1' === get_post_meta( $order_id, $backfill_meta_key, true );
			$sift              = $this->comm->get_user_score( $user_id );

			return array(
				'order_id'      => $order_id,
				'user_id'       => $user_id,
				'is_backfilled' => $is_backfilled,
				'sift'          => $sift,
			);
		}

		/**
		 * Gets the ID of the user from the order id
		 *
		 * @param string $order_id Order ID.
		 *
		 * @return false|string The user, or false if none found
		 */
		private function get_user_id( $order_id ) {
			$meta = get_post_meta( $order_id, '_customer_user', true );

			if ( false === $meta ) {
				return false;
			}

			return '0' === $meta
				? $this->options->get_user_id_from_order_id( $order_id )
				: $this->options->get_user_id_from_user_id( $meta );
		}
	}

endif;
