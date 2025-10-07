<?php
/**
 * Elementor Integration — Bootstrapper.
 *
 * Initializes Elementor integration:
 * - Registers Meteoprog widget category.
 * - Registers Elementor widget after core init.
 * - Enqueues shared block styles inside Elementor editor.
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

/**
 * Elementor integration bootstrapper.
 *
 * Responsibilities:
 * - Enqueue shared block/editor CSS inside Elementor editor (for consistent preview UI).
 * - Register Meteoprog widget category and widget itself.
 * - Load the widget class ONLY after Elementor is fully initialized.
 * - Keep frontend CSS empty: real widget UI is rendered by loader.js (same behavior as Gutenberg).
 *
 * Notes:
 * - Compatible with PHP 5.6.
 * - Uses short array syntax (supported since PHP 5.4).
 * - The widget pulls dependencies (API/Frontend) via $GLOBALS set in the main plugin bootstrap.
 */
class Meteoprog_Informers_Elementor {

	/**
	 * Frontend renderer instance (kept for future extension).
	 *
	 * @var object
	 */
	private $frontend;

	/**
	 * API client instance (kept for future extension).
	 *
	 * @var object
	 */
	private $api;

	/**
	 * Constructor.
	 *
	 * Hooks summary:
	 * - elementor/editor/after_enqueue_styles: load editor-only styles from Gutenberg block for consistent look.
	 * - wp_enqueue_scripts: NO frontend styles here; loader.js handles actual UI on the site.
	 * - elementor/init: defer widget/category registration until Elementor core is ready.
	 *
	 * @param object $frontend Frontend renderer.
	 * @param object $api      API client.
	 */
	public function __construct( $frontend, $api ) {
		$this->frontend = $frontend;
		$this->api      = $api;

		// Editor-only styles for visual parity with Gutenberg block editor.
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'enqueue_editor_styles' ) );

		// No frontend CSS required: actual informer UI is built by loader.js (see Frontend::build_html()).
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );

		// Register widget + category only after Elementor finished bootstrapping.
		add_action( 'elementor/init', array( $this, 'register_hooks' ) );
	}

	/**
	 * Register Elementor hooks after Elementor has initialized.
	 *
	 * Important:
	 * - The widget class is required here (not earlier) to ensure Elementor base classes are loaded.
	 * - Avoids fatal errors on sites where Elementor is deactivated/updated.
	 * - Uses both modern (Elementor ≥ 3.5) and legacy (Elementor < 3.5) widget registration hooks
	 *   for maximum compatibility with older Elementor versions (2.x–3.4).
	 */
	public function register_hooks() {

		// Load widget class only now, when Elementor core is guaranteed to be available.
		require_once plugin_dir_path( __FILE__ ) . '/elementor/class-meteoprog-informer-widget.php';

		// Register the actual widget and a dedicated "Meteoprog" category.

		// Modern hook (Elementor ≥ 3.5).
		add_action( 'elementor/widgets/register', array( $this, 'register_widget' ) );

		// Fallback for Elementor < 3.5 (2.x–3.4).
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widget' ) );

		// Register custom "Meteoprog" category in the Elementor panel.
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
	}

	/**
	 * Register Meteoprog widget with Elementor.
	 *
	 * Note:
	 * - We do NOT pass $frontend/$api into the constructor. Elementor re-instantiates widgets via AJAX,
	 *   so the widget pulls dependencies from $GLOBALS within its own constructor for reliability.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Widgets manager instance.
	 */
	public function register_widget( $widgets_manager ) {
		$widgets_manager->register( new \Meteoprog_Informer_Elementor_Widget() );
	}

	/**
	 * Register a custom "Meteoprog" category in the Elementor panel.
	 *
	 * @param \Elementor\Elements_Manager $elements_manager Elements manager instance.
	 */
	public function register_category( $elements_manager ) {
		if ( method_exists( $elements_manager, 'add_category' ) ) {
			$elements_manager->add_category(
				'meteoprog',
				array(
					'title' => __( 'Meteoprog Widgets', 'meteoprog-weather-informers' ),
					'icon'  => 'fa fa-cloud', // Optional: can be replaced with a custom SVG-based admin icon.
				)
			);
		}
	}

	/**
	 * Load Gutenberg block editor CSS inside Elementor editor for consistent in-editor preview.
	 *
	 * Why:
	 * - Keeps the editor-side preview box (borders, spacing, typography) identical to Gutenberg.
	 * - Does not affect frontend: real UI comes from loader.js on the public site.
	 */
	public function enqueue_editor_styles() {

		$css_path = plugin_dir_path( __DIR__ ) . 'assets/block/css/block-editor.css';
		$css_url  = plugin_dir_url( __DIR__ ) . 'assets/block/css/block-editor.css';

		$ver = file_exists( $css_path ) ? filemtime( $css_path ) : METEOPROG_PLUGIN_VERSION;

		wp_enqueue_style(
			'meteoprog-block-editor',
			$css_url,
			array(),
			$ver
		);
	}


	/**
	 * Frontend styles are intentionally not enqueued.
	 *
	 * Reason:
	 * - The frontend output is a lightweight container; the actual widget UI is rendered by loader.js.
	 * - This matches Gutenberg behavior and avoids redundant CSS on public pages.
	 */
	public function enqueue_frontend_styles() {
		// Intentionally left empty (no CSS needed on frontend).
	}
}
