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
		public const WC_CUSTOM_ELEMENT     = 'custom';

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
					<?php echo esc_html( $label ) . PHP_EOL; ?>
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
		 * This function displayes a bootstrap notice for improveing plugin.
		 *
		 * @param stting $enabled_link the enabled url.
		 * @param string $disabled_link the disabled url.
		 */
		public function display_improve_message( $enabled_link, $disabled_link ) {
			?>
			<div class="notice notice-info is-dismissible">
				<p> 
					Please help improve Sift for WooCommerce by enabling Stats and Error Reporting.
					<a href="<?php echo esc_url( $enabled_link ); ?>">Enable</a>,
					<a href="<?php echo esc_url( $disabled_link ); ?>">Disable</a>,
					<a target="_blank" href="https://github.com/Fermiac/woocommerce-siftscience/wiki/Statistics-Collection">More info</a>. 
				</p>
			</div>
			<?php
		}

		/**
		 * This function is to cdisplay a notice so the user shoulr update their plugin
		 *
		 * @param string $settings_url the link to update plugin.
		 */
		public function disply_update_notice( $settings_url ) {
			?>
			<div class="notice notice-error is-dismissible">
				<p>Sift configuration is invalid. <a href="<?php echo esc_url( $settings_url ); ?>">please update</a>.</p>
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
		 * This function displays a custom row in the admin settings page
		 *
		 * @param Array $data The data array of this setting line.
		 */
		public function display_custom_settings_row( $data ) {
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
		 * This function displays the stats of time and method calls.
		 *
		 * @param Array  $stats          the data stored in  WC_SiftScience_Options::STATS.
		 * @param String $clear_url      this url is used to clear stats.
		 */
		public function display_stats_tables( $stats, $clear_url ) {
			?>
				<h2>Statistics</h2>
				<style type="text/css">
					div.stats table:not(:last-child){
						border-bottom:1px solid #000;
						padding-bottom: 3px;
					}
					div.stats table{
						margin-bottom:5px;
					}
				</style>
			<?php
			foreach ( $stats as $outer_k => $outer_v ) :
				$class_name = substr( $outer_k, 0, stripos( $outer_k, ':' ) );
				$method     = substr( $outer_k, strripos( $outer_k, ':' ) );

				?>
				<div class="stats">
					<table style="width: 300px;">
						<thead>
							<tr>
								<th scope="colgroup" colspan="2" style="text-align:left">
									<span style="color:#00a0d2"> <?php echo esc_html( $class_name ); ?>::</span><?php echo esc_html( $method ); ?>
								</th>
							</tr>
						</thead>
						<tbody>
					<?php
					foreach ( array_reverse( $outer_v ) as $inner_k => $inner_v ) :
						?>
						<tr>
							<td style="width:50px">
								<?php echo esc_html( $inner_k ); ?> 
							</td>
							<td>
								<?php echo esc_html( $inner_v ); ?>  
							</td>
						</tr>
						<?php
						endforeach; // inner.
					?>
					</tbody>
				</table>
					<?php
					endforeach; // Outer.
			?>
				</div>
					<a href="<?php echo esc_url( $url ); ?>" class="button-primary woocommerce-save-button">Clear Stats</a>
			<?php
		}
	}
endif;
