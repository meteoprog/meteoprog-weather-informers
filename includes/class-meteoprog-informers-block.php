<?php
/**
 * Gutenberg Block Integration for Meteoprog Weather Widgets.
 *
 * Registers the Meteoprog Gutenberg block, enqueues editor assets, and
 * exposes a REST API endpoint for dynamic informer selection.
 *
 * Compatible with PHP 5.6+ and WordPress 4.9+ (Gutenberg block support ≥ 5.0).
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Block
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Meteoprog_Informers_Block
 *
 * Registers a dynamic Gutenberg block for Meteoprog informers, provides an editor
 * integration, and exposes a REST endpoint for listing informers.
 */
class Meteoprog_Informers_Block {

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	const REST_NAMESPACE = 'meteoprog/v1';

	/**
	 * REST route for informers.
	 *
	 * @var string
	 */
	const REST_ROUTE_INFORMERS = '/informers';

	/**
	 * Block name (namespace/name).
	 *
	 * @var string
	 */
	const BLOCK_NAME = 'meteoprog/informer';

	/**
	 * Frontend renderer instance.
	 *
	 * @var Meteoprog_Informers_Frontend
	 */
	private $frontend;

	/**
	 * API wrapper instance.
	 *
	 * @var Meteoprog_Informers_API
	 */
	private $api;

	/**
	 * Constructor.
	 *
	 * @param Meteoprog_Informers_Frontend $frontend Frontend renderer.
	 * @param Meteoprog_Informers_API      $api      API wrapper.
	 */
	public function __construct( $frontend, $api ) {
		$this->frontend = $frontend;
		$this->api      = $api;

		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor' ) );
		add_action( 'rest_api_init', array( $this, 'rest' ) );
	}

	/**
	 * Register Gutenberg block for Meteoprog Informer.
	 *
	 * This method hooks into the `init` action and uses the Block API to
	 * register the dynamic Meteoprog Informer block. Rendering is delegated
	 * to {@see Meteoprog_Informers_Block::render_block()}.
	 *
	 * Compatibility notes:
	 * - `register_block_type()` was introduced in WordPress 5.0.
	 * - On WordPress < 5.0, this method exits silently (no registration).
	 * - When Elementor editor mode is active, registration is skipped to
	 *   prevent duplicate rendering or UI conflicts inside Elementor.
	 *
	 * @return void
	 */
	public function register_block() {

		// Skip if Gutenberg API is not available (e.g., WordPress 4.9).
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Guard against duplicate registration.
		if ( class_exists( 'WP_Block_Type_Registry' ) ) {
			$registry = WP_Block_Type_Registry::get_instance();
			if ( $registry && method_exists( $registry, 'is_registered' ) ) {
				if ( $registry->is_registered( self::BLOCK_NAME ) ) {
					return;
				}
			}
		}

		// Skip registration inside Elementor editor context to avoid conflicts.
		if ( $this->is_elementor_editor() ) {
			return;
		}

		// Register block type with dynamic render callback.
		register_block_type(
			self::BLOCK_NAME,
			array(
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'id' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);
	}

	/**
	 * Render callback for the Gutenberg block.
	 *
	 * Executes in both the editor (live preview) and the frontend.
	 * Uses either the provided block attribute `id` or falls back
	 * to the default informer ID stored in plugin options.
	 *
	 * - If an ID is found → renders informer HTML via frontend handler.
	 * - If no ID is found → outputs a simple warning box (⚠).
	 *
	 * @param array $atts Block attributes.
	 * @return string HTML for block output.
	 */
	public function render_block( $atts ) {
		// Sanitize block attribute.
		$id = isset( $atts['id'] ) ? sanitize_text_field( $atts['id'] ) : '';

		// Fallback to default if attribute not set.
		if ( '' === $id ) {
			$id = $this->get_default_informer_id();
		}

		// Warning if still empty.
		if ( '' === $id ) {
			return __( '<!-- Meteoprog Weather Widget: default ID not set -->', 'meteoprog-weather-informers' );
		}

		// Enqueue loader exactly when widget HTML is generated.
		$this->frontend->enqueue_loader();

		// Final render (Gutenberg + frontend).
		return $this->frontend->build_html( $id );
	}

	/**
	 * Get and cache the default informer ID.
	 *
	 * @return string Default informer ID (or empty string).
	 */
	private function get_default_informer_id() {
		static $default_id = null;
		if ( null === $default_id ) {
			$default_id = get_option( 'meteoprog_default_informer_id', '' );
		}
		return $default_id;
	}

	/**
	 * Enqueue JS/CSS for Gutenberg editor.
	 *
	 * Skips execution in non-admin contexts, CLI, or if Gutenberg is unavailable.
	 *
	 * @return void
	 */
	public function enqueue_editor() {

		// Skip if Gutenberg API is not available (e.g., WP 4.9).
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Avoid fatal in CLI, REST, or if not in admin.
		if ( ! is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			return;
		}

		// Prevent double enqueue in case of multiple calls.
		static $enqueued = false;
		if ( $enqueued ) {
			return;
		}
		$enqueued = true;

		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		// Only enqueue in block editor.
		$current_screen = get_current_screen();
		if ( ! $current_screen || ( method_exists( $current_screen, 'is_block_editor' ) && ! $current_screen->is_block_editor() ) ) {
			return;
		}

		$base_path = plugin_dir_path( __DIR__ );
		$base_url  = plugin_dir_url( __DIR__ );

		$js_path  = $base_path . 'assets/block/js/block.js';
		$css_path = $base_path . 'assets/block/css/block-editor.css';
		$js_url   = $base_url . 'assets/block/js/block.js';
		$css_url  = $base_url . 'assets/block/css/block-editor.css';

		// Build dependency array dynamically.
		$deps = array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-i18n' );

		// Prefer modern 'wp-block-editor'; fallback to legacy 'wp-editor' if needed.
		if ( wp_script_is( 'wp-block-editor', 'registered' ) ) {
			$deps[] = 'wp-block-editor';
		} elseif ( wp_script_is( 'wp-editor', 'registered' ) ) {
			$deps[] = 'wp-editor';
		}

		// Register the block script.
		wp_register_script(
			'meteoprog-block',
			$js_url,
			$deps,
			file_exists( $js_path ) ? filemtime( $js_path ) : METEOPROG_PLUGIN_VERSION,
			true
		);

		// Pass default informer ID to the block.
		wp_localize_script(
			'meteoprog-block',
			'MeteoprogSettings',
			array(
				'defaultInformerId' => $this->get_default_informer_id(),
			)
		);

		wp_enqueue_script( 'meteoprog-block' );

		// Load translations if available.
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				'meteoprog-block',
				'meteoprog-weather-informers',
				$base_path . 'languages'
			);
		}

		// Enqueue editor CSS.
		wp_enqueue_style(
			'meteoprog-block-editor',
			$css_url,
			array(),
			file_exists( $css_path ) ? filemtime( $css_path ) : METEOPROG_PLUGIN_VERSION
		);
	}

	/**
	 * Register REST endpoint: /wp-json/meteoprog/v1/informers.
	 *
	 * REST API was introduced in WP 4.7 (safe for WP >= 4.9).
	 *
	 * @return void
	 */
	public function rest() {
		if ( did_action( 'rest_api_init' ) && isset( $GLOBALS['wp_rest_server'] ) ) {
			// Prevent duplicate route registration during tests or multiple inits.
			static $already_registered = false;
			if ( $already_registered ) {
				return;
			}
			$already_registered = true;
		}

		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE_INFORMERS,
			array(
				'methods'             => 'GET',
				'show_in_index'       => false,
				'callback'            => array( $this, 'rest_informers_callback' ),
				'permission_callback' => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * REST API callback for the informers endpoint.
	 *
	 * Returns the list of available informers from the Meteoprog API wrapper.
	 * Executed when a GET request is made to `/wp-json/meteoprog/v1/informers`.
	 *
	 * @return array List of informers.
	 */
	public function rest_informers_callback() {
		return $this->api->get_informers();
	}

	/**
	 * Determine whether the current request is running inside the Elementor editor.
	 *
	 * This method simply wraps the global meteoprog_is_elementor_editor_mode() helper
	 * to make the behavior easily mockable in unit tests without overriding global functions.
	 *
	 * @return bool True if Elementor editor mode is active, false otherwise.
	 */
	protected function is_elementor_editor() {
		return function_exists( 'meteoprog_is_elementor_editor_mode' )
			&& meteoprog_is_elementor_editor_mode();
	}
}
