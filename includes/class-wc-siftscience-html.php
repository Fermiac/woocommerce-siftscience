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
		 * The allowed HTML tags
		 *
		 * @var Array $allowed_tags Set from admin for escaping output.
		 */
		private $allowed_tags;

		public const WC_TITLE_ELEMENT      = 'title';
		public const WC_TEXT_ELEMENT       = 'text';
		public const WC_NUMBER_ELEMENT     = 'number';
		public const WC_SELECT_ELEMENT     = 'select';
		public const WC_CHECKBOX_ELEMENT   = 'checkbox';
		public const WC_SECTIONEND_ELEMENT = 'sectionend';

		/**
		 * A setter for allowed_html field
		 *
		 * @param Array $tags the tags set frp WC_SiftScience_admin.
		 */
		public function set_allowed_tags( $tags ) {
			$this->allowed_tags = $tags;
		}

		/**
		 * This function displayes sections in a bar separated list in regards of the current section
		 *
		 * @param Array  $sections     the sections to be displayed.
		 * @param String $admin_id     The admin Id.
		 *
		 * @global String $current_section
		 */
		public function display_sections( $sections, $admin_id ) {
			global $current_section;
			$selected_section = empty( $current_section ) ? 'main' : $current_section;

			$tabs = array();
			foreach ( $sections as $id => $label ) {
				$url    = admin_url( 'admin.php?page=wc-settings&tab=' . $admin_id . '&section=' . sanitize_title( $id ) );
				$class  = $selected_section === $id ? 'current' : '';
				$tabs[] = '<a href="' . $url . '" class="' . $class . '">' . $label . '</a>';
			}

			$tabs_html = '<li>' . join( ' | </li><li>', $tabs ) . '</li>';
			echo wp_kses( '<ul class="subsubsub">' . $tabs_html . '</ul><br class="clear" />', $this->allowed_tags );
		}
		/**
		 * This function displayes a booystrap notice for improveing plugin.
		 *
		 * @param stting $yes_anchor   the enabled anchor.
		 * @param string $no_anchor    the disabled anchor.
		 */
		public function display_improve_message( $yes_anchor, $no_anchor ) {?>
			<div class="notice notice-info is-dismissible">
				<p> 
					Please help improve Sift for WooCommerce by enabling Stats and Error Reporting.
					<?php echo wp_kses( "$yes_anchor, $no_anchor,", $this->allowed_tags ); ?>
					<a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">more info</a>. 
				</p>
			</div>
			<?php
		}

		/**
		 * Echoing the style rule for the next sibbling of checkbox label to display inline
		 *
		 * @param string $label_for same of The ID of the checkbox [html validation].
		 */
		public function styling_checkbox_label( $label_for ) {
			?>
			<style type="text/css">
				label[for=<?php echo wp_kses( $label_for, array() ); ?>]+*{
					display:inline;
				}
			</style>
			<?php
		}

		/**
		 *
		 * Thos function echos sift control for backfilling orders the div id must be batch-upload.
		 */
		public function display_batch_table() {
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
