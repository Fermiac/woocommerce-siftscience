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

		public static function tool_tip( $inner, $text ) {
			return "<span style='display: block;' class='tips' data-tip='$text'>$inner</span>";
		}

		public static function div( $content, $params ) {
			$divTags = self::tag_params( $params );
			return "<div$divTags>$content</div>";
		}

		private static function tag_params( $params, $str_enclose = '"' ) {
			$result = '';
			foreach ( $params as $k => $v ) {
				$result .= " $k=$str_enclose$v$str_enclose";
			}
			return $result;
		}

		public static function enqueue_script( $script_name, $data = null, $version = false ) {
			wp_enqueue_script( $script_name,
				plugins_url( "tools/$script_name.js", dirname( __FILE__ ) ),
				array( 'jquery' ), $version, true );

			$var_name = str_replace('-', '_', $script_name);

			if ( $data !== null ) {
				wp_localize_script( $script_name, "_${var_name}_input_data", $data );
			}
		}
	}

endif;
