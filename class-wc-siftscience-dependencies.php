<?php
/**
 * This class builds all the components in the order needed for dependencies
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Dependencies' ) ) :

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
		public static function require_all_php_files( $dir ) {
			foreach ( scandir( $dir ) as $file ) {
				if ( '.' === $file || '..' === $file ) {
					continue;
				}

				$full_name = $dir . '/' . $file;

				if ( is_dir( $full_name ) ) {
					self::require_all_php_files( $full_name );
					continue;
				}

				// Check if the filename ends in .php.
				if ( false === strpos( $file, '.php', strlen( $file ) - 4 ) ) {
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
			$reflection_class = null;
			try {
				$reflection_class = new ReflectionClass( $class );
			} catch ( ReflectionException $ex ) {
				// @codingStandardsIgnoreStart
				error_log( "WC_SiftScience_Dependencies: Failed to create ReflectionClass for [$class]" );
				// @codingStandardsIgnoreEnd

				return null;
			}

			$constructor = $reflection_class->getConstructor();

			if ( null === $constructor ) {
				return $reflection_class->newInstanceWithoutConstructor();
			}

			$args = array();

			$php_version = (float) ( PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION );
			if ( 7.1 > $php_version ) {
				foreach ( $constructor->getParameters() as $param ) {
					$args[] = $this->get( (string) $param->getType() );
				}
			} else {
				foreach ( $constructor->getParameters() as $param ) {
					$args[] = $this->get( $param->getType()->getName() );
				}
			}

			return $reflection_class->newInstanceArgs( $args );
		}
	}
endif;
