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
		 * This function displayes sections in a bar separated list in regards of the current section
		 *
		 * @param Array  $sections     the sections to be displayed.
		 * @param String $admin_id     The admin Id.
		 * @param Array  $allowed_html the alloed HTML tags.
		 *
		 * @global String $current_section
		 */
		public function display_sections( $sections, $admin_id, $allowed_html ) {
			global $current_section;
			$selected_section = empty( $current_section ) ? 'main' : $current_section;

			$tabs = array();
			foreach ( $sections as $id => $label ) {
				$url    = admin_url( 'admin.php?page=wc-settings&tab=' . $admin_id . '&section=' . sanitize_title( $id ) );
				$class  = $selected_section === $id ? 'current' : '';
				$tabs[] = '<a href="' . $url . '" class="' . $class . '">' . $label . '</a>';
			}

			$tabs_html = '<li>' . join( ' | </li>', $tabs ) . '</li>';
			echo wp_kses( '<ul class="subsubsub">' . $tabs_html . '</ul><br class="clear" />', $allowed_html );
		}

	}

endif;
