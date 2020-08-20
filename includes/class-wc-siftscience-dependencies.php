<?php
/**
 * This class builds all the components in the order needed for dependencies
 *
 * @package siftscience
 * @author Nabeel Sulieman, Rami Jamleh
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Format_Order' ) ) :

	require_once dirname( __FILE__ ) . '/class-wc-siftscience-options.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-logger.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-stats.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-instrumentation.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-comm.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-html.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-format.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-api.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-events.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-admin.php';
	require_once dirname( __FILE__ ) . '/class-wc-siftscience-orders.php';
	require_once dirname( __FILE__ ) . '/third-party/class-wc-siftscience-stripe.php';

	/**
	 * Class WC_SiftScience_Dependencies
	 */
	class WC_SiftScience_Dependencies {
		private $cache = array();

		public function __construct() {
			$options = new WC_SiftScience_Options();
			$logger = new WC_SiftScience_Logger( $options );
			$cache[ 'WC_SiftScience_Options' ] = $options;
			$cache[ 'WC_SiftScience_Logger' ] = $logger;
		}

		public function get( $class ) {
			if ( ! class_exists( $class ) ) {
				$this->logger->log_error( "Class {$class} does not exist" );
				return null;
			}

			if ( ! isset( $this->cache[ $class ] ) ) {
				$this->cache[ $class ] = $this->build( $class );
			}

			return $this->cache[ $class ];
		}

		private function build( $class ) {
			$r = new ReflectionClass( $class );
			$c = $r->getConstructor();

			$args = array();
			foreach ( $c->getParameters() as $p ) {
				$t = ( string ) $p->getType();
				$args[] = $this->get( $t );
			}

			return $r->newInstance( $args );
		}
	}
endif;
