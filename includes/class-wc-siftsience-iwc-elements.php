<?php
/**
 * This interface has wc_elements in constants
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! interface_exists( 'Iwc-elements' ) ) :
	interface Iwc_Elements {
		public const WC_TITLE_ELEMENT      = 'title';
		public const WC_TEXT_ELEMENT       = 'text';
		public const WC_NUMBER_ELEMENT     = 'number';
		public const WC_SELECT_ELEMENT     = 'select';
		public const WC_CHECKBOX_ELEMENT   = 'checkbox';
		public const WC_SECTIONEND_ELEMENT = 'sectionend';
	}
endif;
