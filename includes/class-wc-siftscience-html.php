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
			$img_path = plugins_url( "woocommerce-siftscience/images/$img" );
			return "<img src='$img_path' alt='$alt' width='20px' height='20px' />";
		}

		public static function tool_tip( $inner, $text ) {
			return "<span style='display: block;' class='tips' data-tip='$text'>$inner</span>";
		}

		public static function score( $value ) {
			$color = $value > 90 ? 'red' : ( $value > 80 ? 'orange' : 'green' );

			$style = self::css_style( array(
				'color'            => 'white',
				'text-align'       => 'center',
				'background-color' => $color,
				'border'           => '1px solid black',
				'width'            => '20px',
				'height'           => '20px',
				'margin'           => '0px',
				//'display' => 'inline',
			) );

			return "<div style='$style'>$value</div>";
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

		private static function css_style( $properties ) {
			$style = '';
			foreach ( $properties as $k => $v ) {
				$style .= "$k: $v;";
			}
			return $style;
		}

		public static function score_label_icons( $vertical = true ) {
			global $post;
			$id = $post->ID;
			$hide = 'none';
			$float = $vertical ? 'none' : 'left';
			echo( $vertical ? '' : '<div style="float: inline; height: 22px;">' );
			echo self::create_icon_div( $id, 'siftsci_error', 'get', 'error.png', 'error', 'Something went wrong. Click to try again.', $hide, $float );
			echo self::create_score_div( $id, 'siftsci_score', 99, 'Click to go to SiftScience page.', $hide, $float );
			echo self::create_icon_div( $id, 'siftsci_backfill', 'backfill', 'upload.png', 'backfill', 'Click to send info to SiftScience...', $hide, $float );
			echo self::create_icon_div( $id, 'siftsci_good_gray', 'set_good', 'good-gray.png', 'good', 'Click to mark as good...', $hide, $float );
			echo self::create_icon_div( $id, 'siftsci_good', 'unset', 'good.png', 'good', 'Click to undo mark as good...', $hide, $float );
			echo self::create_icon_div( $id, 'siftsci_bad_gray', 'set_bad', 'bad-gray.png', 'bad', 'Click to mark as bad...', $hide, $float );
			echo self::create_icon_div( $id, 'siftsci_bad', 'unset', 'bad.png', 'bad', 'Click to undo mark as bad...', $hide, $float );
			echo self::create_icon_div( $id, 'siftsci_spinner', 'none', 'spinner.gif', 'loading', 'Loading SiftScience Data...', 'block', $float );
			echo( $vertical ? '' : '</div>' );
		}

		private static function create_score_div( $id, $pre, $val, $tt, $visible, $float ) {
			$score = WC_SiftScience_Html::score( $val );
			$content = WC_SiftScience_Html::tool_tip( $score, $tt );
			$meta = get_post_meta( $id );
			$user = urlencode( $meta['_customer_user'][0] );
			$url = "https://siftscience.com/console/users/" . urlencode( $user );
			$tagParams = array(
				'id'      => "{$pre}_$id",
				'onclick' => "window.open('$url');",
				'class'   => $pre,
				'style'   => "width: 24px; float: $float; display: $visible;",
			);

			return WC_SiftScience_Html::div( $content, $tagParams );
		}

		private static function create_icon_div( $id, $pre, $action, $img, $alt, $tt, $visible = 'block', $float = 'none' ) {
			$icon = WC_SiftScience_Html::icon( $img, $alt );
			$content = WC_SiftScience_Html::tool_tip( $icon, $tt );

			$tagParams = array(
				'id'      => "{$pre}_$id",
				'onclick' => "SiftScienceOrder.callApi($id,'$action');",
				'class'   => $pre,
				'style'   => "width: 24px; display: $visible; float: $float;",
			);

			return WC_SiftScience_Html::div( $content, $tagParams );
		}

		public static function enqueue_script( $scriptName, $data = null ) {
			wp_enqueue_script( $scriptName,
				plugins_url( "woocommerce-siftscience/tools/$scriptName.js" ),
				array( 'jquery' ), false, true );

			$varname = str_replace('-', '_', $scriptName);

			if ( $data !== null ) {
				wp_localize_script( $scriptName, "_${varname}_input_data", $data );
			}
		}
	}

endif;
