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
			?>

		<ul class="subsubsub">
			<?php
			$i = count( $sections );
			foreach ( $sections as $id => $label ) :
				$url   = admin_url( 'admin.php?page=wc-settings&tab=' . $admin_id . '&section=' . sanitize_title( $id ) );
				$class = $selected_section === $id ? 'current' : '';

				?>

			<li>
				<a 
					href="<?php echo esc_attr( $url ); ?>" 
					class="<?php echo esc_attr( $class ); ?>"> 
					<?php echo esc_textarea( $label ) . PHP_EOL; ?>
				</a>
			</li>
				<?php
				if ( 0 < --$i ) {
					echo '|';
				}
			endforeach;
			?>
			</ul>
			<br class="clear" />
			<?php
		}
		/**
		 * This function displayes a booystrap notice for improveing plugin.
		 *
		 * @param stting $yes_anchor   the enabled anchor.
		 * @param string $no_anchor    the disabled anchor.
		 */
		public function display_improve_message( $yes_anchor, $no_anchor ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p> 
					Please help improve Sift for WooCommerce by enabling Stats and Error Reporting.
					<?php echo wp_kses( "$yes_anchor, $no_anchor,", array( 'a' => array( 'href' => array() ) ) ); ?>
					<a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">more info</a>. 
				</p>
			</div>
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
		/**
		 * This function displayes Reporting header receiving the anonymous id from the admin class
		 *
		 * @param String $anonymous_id the id sent from admin genarated by options.
		 * @param String $reset_anchor anchor tag to reset the anonymous id.
		 */
		public function display_reporting_text( $anonymous_id, $reset_anchor ) {
			?>

			<h2>Notice</h2>
			<p> 
				Help us improve this plugin by automatically reporting errors and statistics.<br /> 
				All information is anonymous and cannot be traced back to your site.<br />
				For details, <a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">click here</a>.
			</p>
			<p> Your <em>anonymous id</em> is: <?php echo wp_kses( $anonymous_id . ' ' . $reset_anchor, array( 'a' => array( 'href' => array() ) ) ); ?></p>

			<?php
		}

	}

endif;
