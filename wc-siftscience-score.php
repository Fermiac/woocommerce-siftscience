<?php

/*
 * Author: Nabeel Sulieman
 * Description: Asynchronous API for getting SiftScience scores and reporting labels
 * License: GPL2
 */

include_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php' );

if ( ! is_super_admin() ) {
	http_response_code( 401 );
	echo json_encode( array( 'error' => 'not allowed' ) );
	die;
}

if ( ! class_exists( 'WC_SiftScience_Score' ) ) :
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-comm.php' );
	include_once( dirname( __FILE__ ) . '/includes/class-wc-siftscience-backfill.php' );

	class WC_SiftScience_Score {
		private $comm;
		private $backfill;

		public function __construct() {
			$this->comm = new WC_SiftScience_Comm;
			$this->backfill = new WC_SiftScience_Backfill;
		}

		public function execute() {
			$id = filter_input( INPUT_GET, 'id' );
			$action = filter_input( INPUT_GET, 'action' );

			$result = null;
			if ( $id === null || $action === null ) {
				http_response_code( 400 );
				$result = array( 'error' => 'invalid request' );
			} else {
				$result = $this->process_request( $action, $id );
			}

			echo json_encode( $result );
		}

		private function process_request( $action, $id ) {
            if ($action != 'backfill' && ! $this->backfill->is_backfilled( $id ) ) {
                $result = $this->default_result();
                $result['backfill']['display'] = 'block';
                return $result;
            }

			switch ( $action ) {
				case 'get':
					return $this->get_order_info( $id );
				case 'backfill':
					$this->backfill->backfill( $id );
					return $this->get_order_info( $id );
				case 'set_good';
					return $this->set_label( $id, false );
				case 'set_bad':
					return $this->set_label( $id, true );
				case 'unset';
					return $this->unset_label( $id );
				default:
					http_response_code( 400 );
					return array( 'error' => 'unknown action' );
			}
		}

		private function set_label( $id, $isBad ) {
			$user_id = $this->get_user_from_post( $id );
			$this->comm->post_label( $user_id, $isBad );
			return $this->get_order_info( $id );
		}

		private function unset_label( $id ) {
			$user_id = $this->get_user_from_post( $id );
			$this->comm->delete_label( $user_id );
			return $this->get_order_info( $id );
		}

		private function get_order_info( $id ) {
			$user_id = $this->get_user_from_post( $id );
			$sift = $this->comm->get_user_score( $user_id );

			$result = $this->default_result();

			$score = round( $sift->score * 100 );
			$result['score']['value'] = $score;
			$result['score']['color'] = $this->get_score_color( $score );
			$result['score']['display'] = 'block';

			$show = array( 'good_gray', 'bad_gray' );
			if ( isset( $sift->latest_label ) && isset( $sift->latest_label->is_bad ) ) {
				$show = $sift->latest_label->is_bad == true ?
					array( 'good_gray', 'bad' ) :
					array( 'good', 'bad_gray' );
			}

			$result[$show[0]] = $result[$show[1]] = array( 'display' => 'block' );

			return $result;
		}

		private function get_user_from_post( $id ) {
			$meta = get_post_meta( $id );
			return $meta['_customer_user'][0];
		}

		private function get_score_color( $score ) {
			return ( $score > 90 ) ? 'green' :
				( ( $score > 40 ) ? 'orange' : 'red' );
		}

		private function default_result() {
			return array(
				'spinner'   => array( 'display' => 'none' ),
				'error'     => array( 'display' => 'none' ),
				'score'     => array( 'display' => 'none' ),
				'backfill'  => array( 'display' => 'none' ),
				'good_gray' => array( 'display' => 'none' ),
				'good'      => array( 'display' => 'none' ),
				'bad_gray'  => array( 'display' => 'none' ),
				'bad'       => array( 'display' => 'none' ),
			);
		}
	}
endif;

( new WC_SiftScience_Score )->execute();
