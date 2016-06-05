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
	include_once( 'class-wc-siftscience-comm.php' );
	include_once( 'class-wc-siftscience-backfill.php' );

	class WC_SiftScience_Api {
		private $comm;
		private $backfill;
		private $options;

		public function __construct( WC_SiftScience_Comm $comm, WC_SiftScience_Backfill $backfill, WC_SiftScience_Options $options ) {
			$this->comm = $comm;
			$this->backfill = $backfill;
			$this->options = $options;
		}

		public function handleRequest($action, $order_id ) {
			if ( ! is_super_admin() ) {
				return array(
					'status' => 401,
					'error' => 'not allowed',
				);
			}

			$user_id = 0;
			if ( $order_id ) {
				$meta = get_post_meta( $order_id, '_customer_user', true );

				if ( false === $meta ) {
					return array(
						'status' => 400,
						'error' => 'order id not found: ' . $order_id,
					);
				}

				$user_id = $meta === '0'
					? 'SINGLE_ORDER_' . $order_id
					: 'REGISTERED_USER_' . $meta;
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
					$this->backfill->backfill( $order_id );
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

			return $this->get_score( $user_id );
		}

		private function get_order_posts() {
			return get_posts( array(
				'numberposts' => -1,
				'post_type'   => wc_get_order_types(),
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

		private function get_score( $user_id ) {
			error_log( 'getting score for: ' . $user_id );
			$sift = $this->comm->get_user_score( $user_id );

			if ( ! isset( $sift->score ) ) {
				return null;
			}

			$score = round( $sift->score * 100 );

			$label = null;
			if ( isset( $sift->latest_label ) && isset( $sift->latest_label->is_bad ) ) {
				$label = $sift->latest_label->is_bad == true ? 'bad' : 'good';
			}

			return array(
				'user_id' => $user_id,
				'score' => $score,
				'label' => $label,
			);
		}
	}

endif;
