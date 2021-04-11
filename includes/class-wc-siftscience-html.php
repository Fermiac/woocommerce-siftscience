<?php
/**
 * This class generates html for subsections as stated in the admin file.
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
		 * @param Array  $sections the sections to be displayed.
		 * @param String $admin_id The admin Id.
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
			$this->enqueue_style( 'stats-table' );
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
		public function display_debugging_info( $ssl_data, string $ssl_url, string $log_url, string $logs ) {
			$this->enqueue_style( 'debug-info' );
			$can_copy_logs = false;
			$can_copy_ssl  = false !== $ssl_data;

			if ( empty( $logs ) ) {
				$logs = 'None';
			} else {
				$can_copy_logs = true;
			}

			$clipboard_img_src = '';
			if ( true === $can_copy_logs || true === $can_copy_ssl ) {
				$clipboard_img_src = plugin_dir_url( __DIR__ ) . 'dist/images/clipboard.png';
				$this->enqueue_script( 'wc-siftsci-copy', 'copy' );
			}
			?>
			<h2 class="debug-header">
				<?php
				if ( true === $can_copy_logs ) :
					?>
					<img 
						alt="" 
						onclick="copyInfo( this, 'debugLog' )" 
						title="Copy to clipboard" 
						src="<?php echo esc_url( $clipboard_img_src ); ?>" 
					/>
					<span class="debug-header-text">Logs</span>
					<a class="page-title-action" href="<?php echo esc_url( $log_url ); ?>">Clear Logs</a>
					<?php
				else :
					?>
					Logs
					<?php
				endif;
				?>
			</h2>
			<textarea id="debugLog" class="debug-info" readonly="readonly"><?php echo esc_textarea( $logs ); ?></textarea>
			<h2 class="debug-header">
				<?php
				if ( true === $can_copy_ssl ) :
					?>
				<img 
					alt="" 
					onclick="copyInfo( this, 'debugSSL' )" 
					title="Copy to clipboard" 
					src="<?php echo esc_url( $clipboard_img_src ); ?>" 
				/>
				<span class="debug-header-text">SSL</span>
			</h2>
			<textarea id="debugSSL" class="debug-info" readonly="readonly"><?php echo esc_textarea( $ssl_data ); ?></textarea>
					<?php
				else :
					?>
				SSL
				<a class="page-title-action" href="<?php echo esc_url( $ssl_url ); ?>">Check SSL</a>
			</h2>
			<div>
				<p>Starting in <em>September 2020</em>, Sift.com will require <strong>TLS1.2</strong>. Click "Check SSL" to test your store.</p>
			</div>
					<?php
			endif;
		}

		/**
		 * Enqueues a CSS from from the dist/css directory
		 *
		 * @param string $css_name The name of the CSS file without extension.
		 */
		public function enqueue_style( $css_name ) {
			$src = plugin_dir_url( __DIR__ ) . "dist/css/$css_name.css";
			wp_enqueue_style( 'wc-sift-' . $css_name, $src, array(), time() );
		}

		/**
		 * Enqueues the a javascript file for inclusion in page
		 *
		 * @param string $name Name of the script to enqueue.
		 * @param string $file_name the javascript Filename without extension.
		 * @param array  $deps Array of dependencies.
		 */
		public function enqueue_script( $name, $file_name, $deps = array() ) {
			$version = time(); // TODO: Make this switchable for dev purposes.
			$src     = plugin_dir_url( __DIR__ ) . "dist/js/$file_name.js";
			wp_enqueue_script( $name, $src, $deps, $version, true );
		}
	}
endif;
