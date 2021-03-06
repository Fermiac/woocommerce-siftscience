<?php
/**
 * Class for creating wc elements according to WooCommerce library.
 *
 * @author Nabeel Sulieman, Rami Jamleh
 * @package sift-for-woocommerce
 * @license GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_SiftScience_Element' ) ) :

	/**
	 * Class for adding WooCommerce elements.
	 */
	class WC_SiftScience_Element {
		/**
		 * The logger service
		 *
		 * @var WC_SiftScience_Logger
		 */
		private $logger;

		public const TITLE      = 'title';
		public const TEXT       = 'text';
		public const NUMBER     = 'number';
		public const SELECT     = 'select';
		public const CHECKBOX   = 'checkbox';
		public const SECTIONEND = 'sectionend';
		public const CUSTOM     = 'custom';

		/**
		 * WC_SiftScience_Element constructor.
		 *
		 * @param WC_SiftScience_Logger $logger Logger service.
		 */
		public function __construct( WC_SiftScience_Logger $logger ) {
			$this->logger = $logger;
		}

		/**
		 * This function sets HTML element attributes according to woocommearce provided library.
		 * desc_tip Mixed [bool:false] (default)
		 *     field type of checkbox; the desc text is going next to the control
		 *     field type of select, number or text; the desc text is going underneath control
		 * desc_tip Mixed [bool:true]
		 *     [X] field type of checkbox; the desc text is going underneath control
		 *     field type of select, number or text; a question mark pop-up appears before control with desc text
		 * desc_tip Mixed [string]
		 *     field type of checkbox; the desc_tip text is going underneath control
		 *     field type of select, number or text; a question mark pop-up appears before control with desc_tip text
		 * desc_tip is added in element options [X] sanitized to False.
		 *
		 * @param string $type            Element type name.
		 * @param string $id              HTMLElement ID.
		 * @param string $label           Element label.
		 * @param string $desc            Description text.
		 * @param array  $element_options Element special options.
		 *
		 * @return array $element_options An array of attributes.
		 * @since 1.1.0
		 */
		public function create( $type, $id, $label = '', $desc = '', $element_options = array() ) {

			// $element_options must have level 2 array['options'] for the select element.
			if ( self::SELECT === $type ) {
				if ( ! isset( $element_options['options'] ) || empty( $element_options['options'] ) ) {
					$this->logger->log_error( 'Drop down ' . $id . ' cannot be empty!' );
					return;
				}
			}

			switch ( func_num_args() ) {
				case 5:
					if ( ! is_array( $element_options ) ) { // elsment_options must be an array.
						$this->logger->log_error( 'element_options must be an array' );
						return;
					}
					// array flattener.
					$custom_attributes = array( 'min', 'max', 'step' );
					$custom_attributes = array_intersect_key( $element_options, array_flip( $custom_attributes ) ); // Gets the new values.

					if ( ! empty( $custom_attributes ) ) {
						$element_options = array_diff_key( $element_options, $custom_attributes ); // Unsets those specific keys.

						$element_options['custom_attributes'] = $custom_attributes; // sets array level 2.
					}

					if ( self::CHECKBOX === $type ) {
						if ( isset( $element_options['desc_tip'] ) ) {
							// if desc_tip is not a string or empty it is sanitized to false.
							$desc_tip = $element_options['desc_tip']; // Temporary storege.

							$element_options['desc_tip'] = ( is_string( $desc_tip ) && ! empty( $desc_tip ) ) ? $desc_tip : false;
						}
					}
					// since element_options exists so there must be a description.
				case 4:
					$desc = ( empty( $desc ) ) ? '[Empty description]' : $desc;
					// since description exists so there must be a label.
				case 3:
					$label = ( empty( $label ) ) ? '[Empty lable]' : $label;
			}

			switch ( $type ) {
				case self::CUSTOM:
					$type = 'wc_sift_' . $id; // this is the custom type name needed by WooCommerce.
					add_action( 'woocommerce_admin_field_' . $type, array( $this, 'custom_inline_element' ) );
					// This intentionally falls through to the next section.

				case self::TEXT:
				case self::SELECT:
				case self::NUMBER:
				case self::CHECKBOX:
				case self::TITLE:
					$element_options['desc']  = $desc;
					$element_options['title'] = $label;
					// Title and desc are added all What's left [id and type].

				case self::SECTIONEND:
					$element_options['id']   = $id;
					$element_options['type'] = $type;
					break;

				default:
					$this->logger->log_error( $type . ' is not a valid type!' );
					break;
			}

			return $element_options;
		}

		/**
		 * This function constructs a custom inline element.
		 *
		 * @param Array $data The data array of this setting line.
		 */
		public function custom_inline_element( $data ) {
			$title   = $data['title'];
			$content = $data['desc'];
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<?php echo esc_html( $title ); ?>
				</th>
				<td class="forminp">
					<?php echo wp_kses( $content, array( 'a' => array( 'href' => array() ) ) ); ?>
				</td>
			</tr>
			<?php
		}

		/**
		 *
		 * Adds batch_upload element, the div must have the ID of batch-upload.
		 */
		public function add_batch_table() {
			?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" class="titledesc">Batch Upload</th>
						<td class="forminp forminp-text">
							<div id="batch-upload"></div>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		}
	}
endif;
