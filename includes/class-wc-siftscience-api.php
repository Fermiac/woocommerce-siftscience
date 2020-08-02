<?php

/*
 * Author: Nabeel Sulieman
 * Description: This class handles the API request ( from the React components )
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( "WC_SiftScience_Api" ) ) :
	require_once( 'class-wc-siftscience-comm.php' );
	require_once( 'class-wc-siftscience-events.php' );
	require_once( 'class-wc-siftscience-options.php' );
	require_once( 'class-wc-siftscience-logger.php' );
	require_once( 'class-wc-siftscience-stats.php' );

	class WC_SiftScience_Api {
		private $comm;
		private $events;
		private $options;
		private $logger;
		private $stats;

		public function __construct( WC_SiftScience_Comm $comm, WC_SiftScience_Events $events,
			WC_SiftScience_Options $options, WC_SiftScience_Logger $logger, WC_SiftScience_Stats $stats ) {
			$this->comm = $comm;
			$this->events = $events;
			$this->options = $options;
			$this->logger = $logger;
			$this->stats = $stats;
		}

		public function handle_ajax() {
			try {
				$id = filter_input( INPUT_GET, 'id' );
				$action = filter_input( INPUT_GET, 'wcss_action' );
				$result = $this->handleRequest( $action, $id );

				if ( isset( $result[ 'status' ] ) ) {
					http_response_code( $result[ 'status' ] );
				}

				$response = json_encode( $result );
				$this->logger->log_info( '[ajax response] ' . $response );
				echo $response;
			} catch ( Exception $error ) {
				$this->logger->log_exception( $error );
				$this->stats->send_error( $error );
				http_response_code( 500 );
				echo json_encode( array(
					'error' => true,
					'code' => $error->getCode(),
					'message' => $error->getMessage(),
					'file' => $error->getFile(),
					'line' => $error->getLine(),
				) );
			}

			wp_die();
		}

		public function handleRequest( $action, $order_id ) {
			if ( ! is_super_admin() ) {
				return array(
					'status' => 401,
					'error' => 'not allowed',
				);
			}

			if ( 'multi' === $action ) {
				return $this->get_orders( $order_id );
			}

			$user_id = 0;
			if ( $order_id ) {
				$user_id = $this->get_user_id( $order_id );
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
						'error' => 'unknown action: ' . $action,
					);
			}

			return $this->get_score( $order_id, $user_id );
		}

		private function get_order_posts() {
			return get_posts( array(
				'numberposts' => -1,
				'post_type'   => array( 'shop_order' ),
				'post_status' => array_keys( wc_get_order_statuses() ),
			) );
		}

		private function list_stats() {
			$backfilled = array();
			$not_backfilled = array();
			$meta_key = $this->options->get_backfill_meta_key();
			$posts = $this->get_order_posts();
			foreach( $posts as $post ) {
				if ( '1' === get_post_meta( $post->ID, $meta_key, true ) ) {
					$backfilled[] = $post->ID;
				} else {
					$not_backfilled[] = $post->ID;
				}
			}

			return array(
				'backfilled' => $backfilled,
				'notBackfilled' => $not_backfilled,
			);
		}

		private function clear_all() {
			$meta_key = $this->options->get_backfill_meta_key();
			$posts = $this->get_order_posts();
			foreach( $posts as $post ) {
				delete_post_meta( $post->ID, $meta_key );
			}

			return $this->list_stats();
		}

		public function get_orders( $order_ids ) {
			$result = array();
			$ids = explode( ',', $order_ids );
			foreach( $ids as $order_id ) {
				$user_id = $this->get_user_id( $order_id );
				$result[] = $this->get_score( $order_id, $user_id );
			}
			return $result;
		}

		private function get_score( $order_id, $user_id ) {
			$backfill_meta_key = $this->options->get_backfill_meta_key();
			$is_backfilled = get_post_meta( $order_id, $backfill_meta_key, true ) === '1';
			$sift = $this->comm->get_user_score( $user_id );

			return array(
				'order_id' => $order_id,
				'user_id' => $user_id,
				'is_backfilled' => $is_backfilled,
				'sift' => $sift,
			);
		}

		private function get_user_id( $order_id ) {
			$meta = get_post_meta( $order_id, '_customer_user', true );

			if ( false === $meta ) {
				return array(
					'status' => 400,
					'error' => 'order id not found: ' . $order_id,
				);
			}

			return $meta === '0'
				? $this->options->get_user_id_from_order_id( $order_id )
				: $this->options->get_user_id_from_user_id( $meta );
		}
	}

endif;
