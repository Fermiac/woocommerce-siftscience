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

		public const WC_TITLE_ELEMENT      = 'title';
		public const WC_TEXT_ELEMENT       = 'text';
		public const WC_NUMBER_ELEMENT     = 'number';
		public const WC_SELECT_ELEMENT     = 'select';
		public const WC_CHECKBOX_ELEMENT   = 'checkbox';
		public const WC_SECTIONEND_ELEMENT = 'sectionend';
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

			$tabs_html = '<li>' . join( ' | </li><li>', $tabs ) . '</li>';
			echo wp_kses( '<ul class="subsubsub">' . $tabs_html . '</ul><br class="clear" />', $allowed_html );
		}
		/**
		 * This function displayes a booystrap notice for improveing plugin.
		 *
		 * @param stting $yes_anchor   the enabled anchor.
		 * @param string $no_anchor    the disabled anchor.
		 * @param Array  $allowed_html the alloed HTML tags.
		 */
		public function display_improve_message( $yes_anchor, $no_anchor, $allowed_html ) {

			$message = 'Please help improve Sift for WooCommerce by enabling Stats and Error Reporting.';

			$details_anchor = '<a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">more info</a>';

			$improve = <<<IMPROVE
			<div class="notice notice-error is-dismissible">
				<p> $message $yes_anchor, $no_anchor, $details_anchor. </p>
			</div>
IMPROVE;
			echo wp_kses( $improve, $allowed_html );
		}

		/**
		 * Echoing the style rule for the next sibbling of checkbox label to display inline
		 *
		 * @param string $label_for same of The ID of the checkbox html validation.
		 * @param Array  $allowed_html the alloed HTML tags.
		 */
		public function styling_checkbox_label( $label_for, $allowed_html ) {
			$html = '<style type="text/css">label[for="%1$s"]+p{display:inline}</style>';
			echo wp_kses( sprintf( $html, $label_for ), $allowed_html );
		}

	}

endif;
