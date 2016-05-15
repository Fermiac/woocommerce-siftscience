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

		public function __construct( WC_SiftScience_Comm $comm, WC_SiftScience_Backfill $backfill ) {
			$this->comm = $comm;
			$this->backfill = $backfill;
		}

		public function handleRequest( $action, $id ) {
			if ( ! is_super_admin() ) {
				return array(
					'status' => 401,
					'error' => 'not allowed',
				);
			}

			if ( null === $id || null === $action ) {
				return array(
					'status' => 400,
					'error' => 'invalid request',
				);
			}

			$meta = get_post_meta( $id );
			if ( false === $meta ) {
				return array(
					'status' => 400,
					'error' => 'order id not found: ' . $id,
				);
			}

			if ( ! isset( $meta['_customer_user'] ) ) {
				return array(
					'status' => 400,
					'error' => 'customer info not found in order: ' . $id,
				);
			}

			$user_id = $meta['_customer_user'][0];

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
					$this->backfill->backfill( $id );
					break;
				default:
					return array(
						'status' => 400,
						'error' => 'unknown action: ' . $action,
					);
			}

			return $this->get_score( $user_id );
		}

		private function get_score( $user_id ) {
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
