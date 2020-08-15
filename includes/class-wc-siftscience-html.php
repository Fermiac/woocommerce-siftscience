<?php
/**
 * Author: Nabeel Sulieman
 * Description: This class contains helpers for generating the visual components of the plugin.
 * License: GPL2
 *
 * @package SiftScience
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Html' ) ) :

	/**
	 * Class for HTML helpers
	 */
	class WC_SiftScience_Html {
		/**
		 * Create a tag
		 *
		 * @param array  $params Param field.
		 * @param string $str_enclose string to enclose inside.
		 *
		 * @return string resulting html
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
