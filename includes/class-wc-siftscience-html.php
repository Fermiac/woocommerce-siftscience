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

		public static function icon( $img, $alt ) {
			$img_path = plugins_url( "images/$img", dirname( __FILE__ ) );
			return "<img src='$img_path' alt='$alt' width='20px' height='20px' />";
		}

		public static function tool_tip( $inner, $text ) {
			return "<span style='display: block;' class='tips' data-tip='$text'>$inner</span>";
		}

		public static function div( $content, $params ) {
			$divTags = self::tag_params( $params );
			return "<div$divTags>$content</div>";
		}

		private static function tag_params( $params, $strEnclose = '"' ) {
			$result = '';
			foreach ( $params as $k => $v ) {
				$result .= " $k=$strEnclose$v$strEnclose";
			}
			return $result;
		}

		public static function enqueue_script( $scriptName, $data = null, $version = false ) {
			wp_enqueue_script( $scriptName,
				plugins_url( "tools/$scriptName.js", dirname( __FILE__ ) ),
				array( 'jquery' ), $version, true );

			$var_name = str_replace('-', '_', $scriptName);

			if ( $data !== null ) {
				wp_localize_script( $scriptName, "_${$var_name}_input_data", $data );
			}
		}
	}

endif;
