<?php
/*
 * Author: Nabeel Sulieman
 * Description: This class contains helpers for generating the visual components of the plugin.
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_SiftScience_Html' ) ) :

	class WC_SiftScience_Html {

		/*public static function tool_tip( $inner, $text ) {
			return "<span style='display: block;' class='tips' data-tip='$text'>$inner</span>";
		}

		public static function div( $content, $params ) {
			$divTags = self::tag_params( $params );
			return "<div$divTags>$content</div>";
		}*/

		private static function tag_params( $params, $str_enclose = '"' ) {
			$result = '';
			foreach ( $params as $k => $v ) {
				$result .= " $k=$str_enclose$v$str_enclose";
			}
			return $result;
		}
	}

endif;
