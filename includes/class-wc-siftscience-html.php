<?php
/**
 * This class contains helpers for generating the visual components of the plugin.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Html' ) ) :
	/**
	 * Helper class for generating html
	 *
	 * Class WC_SiftScience_Html
	 */
	class WC_SiftScience_Html {
		/**
		 * Generates HTML attribute tags from the key-values of an array
		 *
		 * @param array  $params The attributes to convert.
		 * @param string $str_enclose Single or double quotes.
		 *
		 * @return string The attributes to be inserted in the HTML tag
		 */
		private function tag_params( $params, $str_enclose = '"' ) {
			$result = '';
			foreach ( $params as $k => $v ) {
				$result .= " $k=$str_enclose$v$str_enclose";
			}
			return $result;
		}
	}

endif;
