<?php
/**
 * Shortcodes Ultimate integration for Meteoprog.
 *
 * Registers a `[su_meteoprog_informer]` shortcode and integrates Meteoprog informers
 * into the Shortcodes Ultimate insert dialog. Supports live preview inside the
 * WordPress editor and falls back to default informer if no ID is provided.
 *
 * Compatible with PHP 5.6+ and modern WordPress versions.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage ShortcodesUltimate
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'meteoprog_su_informer_shortcode_handler' ) ) {
	/**
	 * Handle SU shortcode [su_meteoprog_informer].
	 *
	 * @param array  $atts     Shortcode attributes.
	 * @param string $_content Unused.
	 * @param string $_tag     Unused.
	 * @return string HTML output.
	 */
	function meteoprog_su_informer_shortcode_handler( $atts = array(), $_content = '', $_tag = '' ) {
		unset( $_content, $_tag );

		$frontend = meteoprog_get_frontend_instance();

		if ( ! $frontend ) {
			return '<!-- no frontend instance -->';
		}

		$atts = shortcode_atts(
			array( 'id' => get_option( 'meteoprog_default_informer_id', '' ) ),
			is_array( $atts ) ? $atts : array(),
			'su_meteoprog_informer'
		);

		$id = sanitize_text_field( $atts['id'] );

		// Admin preview → static box.
		if ( is_admin() ) {
			ob_start();

			$site_host   = strtolower( wp_parse_url( home_url(), PHP_URL_HOST ) );
			$domain_host = '';
			$match       = false;

			try {
				$api = meteoprog_get_api_instance();

				if ( $api && method_exists( $api, 'get_informers' ) ) {
					$informers = $api->get_informers();
					if ( is_array( $informers ) ) {
						foreach ( $informers as $inf ) {
							if ( isset( $inf['informer_id'] ) && $inf['informer_id'] === $id ) {
								if ( isset( $inf['domain'] ) ) {
									$domain_host = meteoprog_host_from_url( $inf['domain'] );
									$match       = ( $domain_host === $site_host );
								}
								break;
							}
						}
					}
				}
			} catch ( \Exception $e ) {
				$match = false;
			}
			?>
			<div class="meteoprog-block-editor" style="margin:10px 0;">
				<div style="border:1px solid #dcdcde;border-radius:6px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,0.05);padding:16px;text-align:center;">
					<div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:10px;">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="#007acc">
							<path d="M6 19a4 4 0 0 1 0-8 5.5 5.5 0 0 1 10.74-1.62A4.5 4.5 0 1 1 18 19H6z"/>
						</svg>
						<strong><?php echo esc_html__( 'Meteoprog Weather Informer', 'meteoprog-weather-informers' ); ?></strong>
					</div>

					<?php if ( ! $id ) : ?>
						<div style="margin-bottom:6px;font-weight:bold;">
							<?php echo esc_html__( 'No informer selected — default preview', 'meteoprog-weather-informers' ); ?>
						</div>
					<?php else : ?>
						<div style="font-family:monospace;font-size:13px;color:#555;margin-bottom:6px;"><?php echo esc_html( $id ); ?></div>
					<?php endif; ?>

					<div style="font-size:12px;color:#666;margin-bottom:10px;">
						<?php echo esc_html__( 'Preview is visible only on frontend', 'meteoprog-weather-informers' ); ?>
					</div>

					<?php if ( $id && $domain_host ) : ?>
						<?php
							$status_text  = $match ? __( 'Domain OK', 'meteoprog-weather-informers' ) : __( 'Domain mismatch', 'meteoprog-weather-informers' );
							$status_color = $match ? '#46b450' : '#dc3232';
						?>
						<span style="display:inline-block;padding:4px 8px;border-radius:3px;font-size:12px;font-weight:600;background:<?php echo esc_attr( $status_color ); ?>;color:#fff;">
							<?php echo esc_html( $status_text ); ?>
						</span>
					<?php endif; ?>
				</div>
			</div>
			<?php
			return ob_get_clean();
		}

		// Enqueue loader exactly when widget HTML is generated.
		$frontend->enqueue_loader();

		return $frontend->build_html( $id );
	}
}

/**
 * SU shortcode: [su_meteoprog_informer id="..."]
 */
add_action(
	'init',
	function () {
		add_shortcode( 'su_meteoprog_informer', 'meteoprog_su_informer_shortcode_handler' );
	}
);

if ( ! function_exists( 'meteoprog_su_register_informer_shortcode' ) ) {
	/**
	 * Add item to SU insert dialog (server-side <select> for informer ID).
	 *
	 * If the Meteoprog API is available, builds a <select> list with all informers.
	 * Otherwise falls back to a text input field.
	 *
	 * @param array $shortcodes Existing SU shortcodes.
	 * @return array Modified SU shortcodes.
	 */
	function meteoprog_su_register_informer_shortcode( $shortcodes ) {

		// Build <select> values.
		$values     = array();
		$values[''] = __( 'Default widget (from settings)', 'meteoprog-weather-informers' );

		$api       = meteoprog_get_api_instance();
		$site_host = strtolower( wp_parse_url( home_url(), PHP_URL_HOST ) );
		$has_list  = false;

		if ( $api && method_exists( $api, 'get_informers' ) ) {
			try {
				$informers = $api->get_informers();

				if ( is_array( $informers ) && ! empty( $informers ) ) {
					foreach ( $informers as $inf ) {
						if ( empty( $inf['informer_id'] ) ) {
							continue;
						}

						$id              = (string) $inf['informer_id'];
						$informer_domain = isset( $inf['domain'] )
							? $inf['domain']
							: __( 'No domain', 'meteoprog-weather-informers' );
						$domain_host     = function_exists( 'meteoprog_host_from_url' )
							? meteoprog_host_from_url( $informer_domain )
							: $informer_domain;
						$masked          = function_exists( 'meteoprog_mask_string' )
							? meteoprog_mask_string( $id )
							: $id;
						$match           = ( $domain_host === $site_host );

						$label         = $informer_domain . ' — ' . $masked . ' [' .
							( $match
								? __( 'OK', 'meteoprog-weather-informers' )
								: __( 'Domain mismatch', 'meteoprog-weather-informers' ) ) . ']';
						$values[ $id ] = $label;
						$has_list      = true;
					}
				}
			} catch ( \Exception $e ) {
				$has_list = false;
			}
		}

		// Select when list is available, otherwise text input.
		$id_attr = $has_list
			? array(
				'type'    => 'select',
				'values'  => $values,
				'default' => '',
				'name'    => __( 'Informer ID', 'meteoprog-weather-informers' ),
				'desc'    => __( 'Select informer or use default from settings.', 'meteoprog-weather-informers' ),
			)
			: array(
				'type'    => 'text',
				'default' => '',
				'name'    => __( 'Informer ID', 'meteoprog-weather-informers' ),
				'desc'    => __( 'Enter informer ID (or leave empty to use default).', 'meteoprog-weather-informers' ),
			);

		// Key without "su_": SU will insert [su_meteoprog_informer ...].
		$shortcodes['meteoprog_informer'] = array(
			'name'        => __( 'Meteoprog Weather', 'meteoprog-weather-informers' ),
			'type'        => 'other',
			'group'       => 'Meteoprog',
			'atts'        => array( 'id' => $id_attr ),
			'has_content' => false,
			'desc'        => __( 'Display Meteoprog weather informer', 'meteoprog-weather-informers' ),
			'icon'        => 'cloud',
		);

		return $shortcodes;
	}
}

/**
 * Add item to SU insert dialog (server-side <select> for informer ID).
 * If API not available, falls back to text input.
 */
add_filter( 'su/data/shortcodes', 'meteoprog_su_register_informer_shortcode' );
