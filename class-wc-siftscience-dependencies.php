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

	/**
	 * Class WC_SiftScience_Dependencies
	 */
	class WC_SiftScience_Dependencies {
		/**
		 * Cache for already created instances
		 *
		 * @var array
		 */
		private $cache = array();

		/**
		 * Requires all php files (recursively) in the path.
		 *
		 * @param string $dir The directory to scan for php files.
		 */
		public function require_all_php_files( $dir ) {
			foreach ( scandir( $dir ) as $f ) {
				if ( '.' === $f || '..' === $f ) {
					continue;
				}

				$full_name = $dir . '/' . $f;

				if ( is_dir( $full_name ) ) {
					$this->require_all_php_files( $full_name );
					continue;
				}

				// Check if the filename ends in .php.
				if ( false === strpos( $f, '.php', strlen( $f ) - 4 ) ) {
					continue;
				}

				require_once $full_name;
			}
		}

		/**
		 * Returns an instance of the specified type
		 *
		 * @param string $class The class type needed.
		 *
		 * @return object
		 */
		public function get( $class ) {
			if ( ! class_exists( $class ) ) {
				return null;
			}

			if ( isset( $this->cache[ $class ] ) ) {
				return $this->cache[ $class ];
			}

			$result = $this->build( $class );

			$this->cache[ $class ] = $result;
			return $result;
		}

		/**
		 * Uses reflection to build an instance of a class
		 *
		 * @param string $class Type to build.
		 *
		 * @return object An instance of the type specified
		 */
		private function build( $class ) {
			$r = null;
			try {
				$r = new ReflectionClass( $class );
			} catch ( ReflectionException $ex ) {
				// @codingStandardsIgnoreStart
				error_log( "WC_SiftScience_Dependencies: Failed to create ReflectionClass for [$class]" );
				// @codingStandardsIgnoreEnd

				return null;
			}

			$c = $r->getConstructor();

			if ( null === $c ) {
				return $r->newInstanceWithoutConstructor();
			}

			$args = array();
			foreach ( $c->getParameters() as $p ) {
				$args[] = $this->get( (string) $p->getType() );
			}

			return $r->newInstanceArgs( $args );
		}
	}
endif;
