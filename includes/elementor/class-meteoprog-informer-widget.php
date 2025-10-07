<?php
/**
 * Elementor Integration — Meteoprog Informer Widget.
 *
 * This class provides a native Elementor widget that allows embedding
 * Meteoprog weather informers directly within the Elementor page builder.
 *
 * Compatible with PHP 5.6+ and modern Elementor versions.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Elementor
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Bail out if Elementor is not loaded
if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

/**
 * Elementor widget: Meteoprog Informer
 *
 * Compatible with PHP 5.6
 */
class Meteoprog_Informer_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Frontend renderer instance (global).
	 *
	 * @var object|null
	 */
	private $frontend;

	/**
	 * API client instance (global).
	 *
	 * @var object|null
	 */
	private $api;

	/**
	 * Constructor
	 *
	 * Elementor re-creates widgets internally and does NOT pass dependencies,
	 * so we grab required instances from $GLOBALS.
	 *
	 * @param array $data Widget data.
	 * @param array $args Widget args.
	 */
	public function __construct( $data = array(), $args = null ) {
		$this->frontend = isset( $GLOBALS['meteoprog_weather_informers_instance'] )
			? $GLOBALS['meteoprog_weather_informers_instance']
			: null;

		$this->api = isset( $GLOBALS['meteoprog_weather_informers_api'] )
			? $GLOBALS['meteoprog_weather_informers_api']
			: null;

		parent::__construct( $data, $args );
	}

	public function get_name() {
		return 'meteoprog_informer';
	}

	public function get_title() {
		return __( 'Meteoprog Informer', 'meteoprog-weather-informers' );
	}

	public function get_icon() {
		return 'wp-icon-meteoprog';
	}

	public function get_categories() {
		return array( 'meteoprog' );
	}

	public function get_keywords() {
		return array( 'meteoprog', 'weather', 'informer', 'widget' );
	}

	/**
	 * Register widget controls displayed in Elementor panel.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_informer',
			array(
				'label' => __( 'Informer Settings', 'meteoprog-weather-informers' ),
			)
		);

		// Default select option
		$options = array(
			'' => __( 'Default widget (from settings)', 'meteoprog-weather-informers' ),
		);

		// Load informer list safely (avoid fatal errors during editor AJAX calls)
		$informers = array();
		if ( $this->api && method_exists( $this->api, 'get_informers' ) ) {
			try {
				$informers = $this->api->get_informers();
			} catch ( \Exception $e ) {
				$informers = array();
			}
		}

		$site_host = strtolower( parse_url( home_url(), PHP_URL_HOST ) );

		// Build dropdown options
		if ( is_array( $informers ) ) {
			foreach ( $informers as $inf ) {
				if ( isset( $inf['informer_id'] ) ) {
					$domain      = isset( $inf['domain'] ) ? $inf['domain'] : __( 'No domain', 'meteoprog-weather-informers' );
					$domain_host = meteoprog_host_from_url( $domain );
					$match       = ( $domain_host === $site_host );

					$id     = $inf['informer_id'];
					$masked = meteoprog_mask_string( $id );

					$label = $domain . ' — ' . $masked . ' [' . ( $match ? __( 'OK', 'meteoprog-weather-informers' ) : __( 'Domain mismatch', 'meteoprog-weather-informers' ) ) . ']';
					$options[ $id ] = $label;
				}
			}
		}

		$default_id = get_option('meteoprog_default_informer_id', '' );

		// Register control in Elementor panel
		$this->add_control(
			'informer_id',
			array(
				'label'        => __( 'Select Meteoprog Weather Informer', 'meteoprog-weather-informers' ),
				'type'         => \Elementor\Controls_Manager::SELECT,
				'options'      => $options,
				'default'      => $default_id,
				'render_type'  => 'template',
				'description'  => __( 'Choose which weather informer to display.', 'meteoprog-weather-informers' ),
				'label_block'  => true,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget on frontend and inside Elementor editor.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$id = isset( $settings['informer_id'] ) ? sanitize_text_field( $settings['informer_id'] ) : '';

		// Fallback to global default informer ID
		if ( ! $id ) {
			$id = get_option('meteoprog_default_informer_id', '' );
		}

		// No informer selected → render static placeholder (no error)
		if ( ! $id ) {
			if ( function_exists( 'meteoprog_is_elementor_editor_mode' ) && meteoprog_is_elementor_editor_mode() ) {
				echo '<div class="meteoprog-block-editor">';
				echo '  <div class="meteoprog-preview-box" style="min-height:250px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;border:1px solid #ccd0d4;border-radius:4px;background:#f9f9f9;padding:12px;">';
				echo '      <div style="margin-bottom:6px;">' . esc_html__( 'No informer selected — default preview', 'meteoprog-weather-informers' ) . '</div>';
				echo '      <div style="font-size:12px;color:#666;">' . esc_html__( 'Preview is visible only on frontend', 'meteoprog-weather-informers' ) . '</div>';
				echo '  </div>';
				echo '</div>';
			}
			return;
		}

		// Domain check for preview badge
		$site_host   = strtolower( parse_url( home_url(), PHP_URL_HOST ) );
		$domain_host = '';
		$match       = false;

		try {
			$informer  = null;
			$informers = $this->api->get_informers();

			if ( is_array( $informers ) ) {
				foreach ( $informers as $inf ) {
					if ( isset( $inf['informer_id'] ) && $inf['informer_id'] === $id ) {
						$informer = $inf;
						break;
					}
				}
			}

			if ( $informer && isset( $informer['domain'] ) ) {
				$domain_host = meteoprog_host_from_url( $informer['domain'] );
				$match       = ( $domain_host === $site_host );
			}
		} catch ( \Exception $e ) {
			$informer = null;
			$match    = false;
		}

        // Editor mode → show static preview box (no loader.js)
        if ( function_exists( 'meteoprog_is_elementor_editor_mode' ) && meteoprog_is_elementor_editor_mode() ) {
            echo '<div class="meteoprog-block-editor">';
            echo '  <div style="border:1px solid #dcdcde;border-radius:6px;background:#fff;box-shadow:0 1px 2px rgba(0,0,0,0.05);padding:16px;text-align:center;">';

            // Header with inline SVG icon + title
            echo '      <div style="display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:10px;">';
            echo '          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="#007acc" style="vertical-align:middle;">';
            echo '              <path d="M6 19a4 4 0 0 1 0-8 5.5 5.5 0 0 1 10.74-1.62A4.5 4.5 0 1 1 18 19H6z"/>';
            echo '          </svg>';
            echo '          <strong>' . esc_html__( 'Meteoprog Weather Informer', 'meteoprog-weather-informers' ) . '</strong>';
            echo '      </div>';

            // Informer ID or default preview
            if ( ! $id ) {
                // No ID selected → default preview
                echo '  <div style="margin-bottom:6px;font-weight:bold;">' .
                    esc_html__( 'No informer selected — default preview', 'meteoprog-weather-informers' ) .
                    '</div>';
            } else {
                // Selected informer ID
                echo '  <div style="font-family:monospace;font-size:13px;color:#555;margin-bottom:6px;">' . esc_html( $id ) . '</div>';
            }

            // Info line
            echo '      <div style="font-size:12px;color:#666;margin-bottom:10px;">' .
                esc_html__( 'Preview is visible only on frontend', 'meteoprog-weather-informers' ) .
                '</div>';

            // Domain status badge
            if ( $id && $domain_host ) {
                $status_text  = $match ? __( 'Domain OK', 'meteoprog-weather-informers' ) : __( 'Domain mismatch', 'meteoprog-weather-informers' );
                $status_color = $match ? '#46b450' : '#dc3232';

                echo '<span style="display:inline-block;padding:4px 8px;border-radius:3px;font-size:12px;font-weight:600;background:' . esc_attr( $status_color ) . ';color:#fff;">' .
                    esc_html( $status_text ) .
                    '</span>';
            }

            echo '  </div>';
            echo '</div>';
            return;
        }


		// Frontend render — uses loader.js for real informer
		if ( $this->frontend && method_exists( $this->frontend, 'build_html' ) ) {
			echo $this->frontend->build_html( $id, true );
		}
	}
}
