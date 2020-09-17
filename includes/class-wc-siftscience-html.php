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
		 * The logger service
		 *
		 * @var WC_SiftScience_Logger
		 */
		private $logger;

		public const WC_TITLE_ELEMENT      = 'title';
		public const WC_TEXT_ELEMENT       = 'text';
		public const WC_NUMBER_ELEMENT     = 'number';
		public const WC_SELECT_ELEMENT     = 'select';
		public const WC_CHECKBOX_ELEMENT   = 'checkbox';
		public const WC_SECTIONEND_ELEMENT = 'sectionend';
		public const WC_CUSTOM_ELEMENT     = 'custom';

		/**
		 * WC_SiftScience_Html constructor.
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
		 * desc_tip is added in element options [X] sanitized.
		 *
		 * @param string $type            Element type name.
		 * @param string $id              HtmlElement ID.
		 * @param string $label           Element label.
		 * @param string $desc            Description text.
		 * @param array  $element_options Element special options.
		 *
		 * @return array $element_options An array of attributes.
		 * @since 1.1.0
		 */
		public function create_element( $type, $id, $label = '', $desc = '', $element_options = array() ) {

			// $element_options must have level 2 options array forb the select element.
			if ( self::WC_SELECT_ELEMENT === $type ) {
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

					if ( isset( $element_options['desc_tip'] ) ) {
						if ( self::WC_CHECKBOX_ELEMENT === $type ) {
							// if desc_tip is not a string or empty it is sanitized to false.
							$desc_tip = $element_options['desc_tip']; // Temporary storege.

							$element_options['desc_tip'] = ( is_string( $desc_tip ) && ! empty( $desc_tip ) ) ? $desc_tip : false;
						}
					}
					// element_options exists so there is a description.
				case 4:
					$desc = ( empty( $desc ) ) ? '[Empty description]' : $desc;
					// description exists so there is a label.
				case 3:
					$label = ( empty( $label ) ) ? '[Empty lable]' : $label;
			}

			switch ( $type ) {
				case self::WC_CUSTOM_ELEMENT:
					$type = 'wc_sift_' . $id; // this is the custom type name needed by WooCommerce.
					add_action( 'woocommerce_admin_field_' . $type, array( $this, 'display_custom_settings_row' ) );
					// This intentionally falls through to the next section.

				case self::WC_TEXT_ELEMENT:
				case self::WC_SELECT_ELEMENT:
				case self::WC_NUMBER_ELEMENT:
				case self::WC_CHECKBOX_ELEMENT:
				case self::WC_TITLE_ELEMENT:
					$element_options['desc']  = $desc;
					$element_options['title'] = $label;
					// Title and desc are added all What's left [id and type].

				case self::WC_SECTIONEND_ELEMENT:
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
		 * This function displays the stats of time and method calls.
		 *
		 * @param Array  $stats the data stored in  WC_SiftScience_Options::STATS.
		 * @param String $url   this url is used to clear stats.
		 */
		public function display_stats_tables( $stats, $url ) {
			?>
			<h2>
				Statistics
				<a class="page-title-action" href="<?php echo esc_url( $url ); ?>">Clear Stats</a>
			</h2>
			<?php
			foreach ( $stats as $outer_k => $outer_v ) :
				$class_name = substr( $outer_k, 0, stripos( $outer_k, ':' ) );
				$method     = substr( $outer_k, strripos( $outer_k, ':' ) + 1 );
				?>
				<div class="stats">
					<table>
						<thead>
							<tr>
								<th scope="colgroup" colspan="2">
									<span><?php echo esc_html( $class_name ); ?></span>::<?php echo esc_html( $method ); ?>()
								</th>
							</tr>
						</thead>
						<tbody>
					<?php
					foreach ( array_reverse( $outer_v ) as $inner_k => $inner_v ) :
						?>
						<tr>
							<td>
								<?php echo esc_html( $inner_k ),':'; ?>
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
			<style type="text/css">
				div.stats table:not(:last-child){
					border-bottom: 1px solid rgba( 0, 0, 0, .5 );
					padding-bottom: 3px;
					margin-bottom: 5px;
				}
				div.stats table{
					width: 300px;
				}
				div.stats tbody tr td:first-child{
					width: 50px;
				}
				div.stats th{
					text-align:left;
				}
				div.stats th span{
					color: #00A0D2;
				}
			</style>
			<?php
		}

		/**
		 * This function displays debugging info for ssl and logs in HTML format
		 *
		 * @param Mixed  $ssl_data get_transient from admin returns this data.
		 * @param String $ssl_url  an action button to check ssl vertion and this is it's url.
		 * @param String $log_url  an action button to clear logs this is it's url.
		 * @param String $logs     the logs retrieved gtom debug DOT log file.
		 */
		public function display_debugging_info( $ssl_data, $ssl_url, $log_url, $logs ) {
			?>
			<h2>
				SSL Check
				<a class="page-title-action" href="<?php echo esc_url( $ssl_url ); ?>">Test SSL</a>
			</h2>
			<div>
				<p>Starting in September 2020, Sift.com will require TLS1.2. Click "Test SSL" to test your store.</p>
			</div>
			<?php
			if ( false !== $ssl_data ) :
				?>
				<div>
					<pre><?php echo esc_html( $ssl_data ); ?></pre>
				</div>
				<?php
			endif;
			?>
			<h2>
				Logs
				<a class="page-title-action" href="<?php echo esc_url( $log_url ); ?>">Clear Logs</a>
			</h2>
			<div>
				<pre class="wrap"><?php echo esc_html( $logs ); ?></pre>
			</div>
			<style type="text/css">
				h2, pre{
					width: 60%; 
				}


				pre.wrap {
					white-space: pre-wrap;
				}
				pre{
					height:300px; 
					background-color: rgba(255,255,255,.5);
					overflow: auto;
					border: 1px solid rgba( 0, 0, 0, .5);
					padding-left: 3px !important; 
				}

				h2{
					border-bottom: 1px solid rgba( 0, 0, 0, .5);
					padding-bottom: 10px;
				}
			</style>
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
