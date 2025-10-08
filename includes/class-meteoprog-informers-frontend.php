<?php
/**
 * Frontend Renderer for Meteoprog Weather Widgets.
 *
 * Handles all frontend integration points:
 * - Shortcode [meteoprog_informer id="..."].
 * - Placeholder replacement {meteoprog_informer_UUID}.
 * - Template helper function meteoprog_informer($id).
 * - Enqueuing loader.js via local wrapper script.
 *
 * Compatible with PHP 5.6+ and WordPress 4.9+.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Frontend
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Meteoprog_Informers_Frontend
 *
 * Responsible for rendering Meteoprog weather informers on the frontend
 * via shortcodes, placeholders, and template functions.
 */
class Meteoprog_Informers_Frontend {

	/**
	 * API instance.
	 *
	 * @var Meteoprog_Informers_API
	 */
	private $api;

	/**
	 * Option name for default informer ID.
	 *
	 * @var string
	 */
	private $opt_default_id = 'meteoprog_default_informer_id';

	/**
	 * Cached default informer ID.
	 *
	 * @var string|null
	 */
	private static $default_id_cache = null;

	/**
	 * Queued informer IDs for the global data layer.
	 *
	 * @var string[]
	 */
	private $queued_ids = array();

	/**
	 * Constructor.
	 *
	 * @param Meteoprog_Informers_API $api API instance.
	 */
	public function __construct( $api ) {
		$this->api = $api;

		// Register shortcode [meteoprog_informer id="..."].
		add_shortcode( 'meteoprog_informer', array( $this, 'shortcode' ) );

		// Register content filter for {meteoprog_informer_UUID} placeholders.
		add_filter( 'the_content', array( $this, 'replace_placeholders' ) );

		// Enable shortcode parsing in legacy Text Widgets.
		add_filter( 'widget_text', 'do_shortcode' );

		// Register global template helper function meteoprog_informer($id).
		add_action( 'init', array( $this, 'register_template_function' ) );

		// Print data layer in the <head> only if informers are present on the page.
		add_action( 'wp_head', array( $this, 'print_data_layer' ) );

		// Add preconnect/dns-prefetch resource hints for CDN if informers are present.
		add_filter( 'wp_resource_hints', array( $this, 'add_resource_hints' ), 10, 2 );
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output or comment.
	 */
	public function shortcode( $atts ) {
		$atts = shortcode_atts( array( 'id' => null ), $atts );
		$id   = ( ! empty( $atts['id'] ) ) ? $atts['id'] : $this->get_default_informer_id();

		if ( ! $id ) {
			return '<!-- Meteoprog Weather Widget: ID not set -->';
		}

		// Enqueue loader exactly when widget HTML is generated.
		$this->enqueue_loader();

		return $this->build_html( $id );
	}

	/**
	 * Replace placeholders like {meteoprog_informer_UUID} or {meteoprog_informer}.
	 *
	 * @param string $content Post content.
	 * @return string Filtered content.
	 */
	public function replace_placeholders( $content ) {
		return preg_replace_callback(
			'/\{meteoprog_informer(?:_([A-Za-z0-9\-]+))?\}/',
			function ( $matches ) {
				// If UUID is provided → use it.
				if ( ! empty( $matches[1] ) ) {

					// Enqueue loader exactly when widget HTML is generated.
					$this->enqueue_loader();

					return $this->build_html( $matches[1] );
				}

				// Otherwise fallback to default informer ID.
				$default_id = $this->get_default_informer_id();

				if ( ! $default_id ) {
					return '<!-- Meteoprog Weather Widget: default ID not set -->';
				}

				// Enqueue loader exactly when widget HTML is generated.
				$this->enqueue_loader();

				return $this->build_html( $default_id );
			},
			$content
		);
	}

	/**
	 * Print the Meteoprog data layer in the document <head>.
	 *
	 * This method outputs a single <script> block that initializes the global
	 * window.meteoprogDataLayer array and pushes all queued informer IDs into it.
	 * It runs once per page and only if at least one informer was rendered via build_html().
	 *
	 * @return void
	 */
	public function print_data_layer() {
		if ( empty( $this->queued_ids ) ) {
			return;
		}

		static $printed = false;
		if ( $printed ) {
			return;
		}
		$printed = true;

		echo "<!-- Meteoprog Weather Widget: Data Layer -->\n";
		echo "<script id=\"meteoprog-data-layer\">\n";
		echo "window.meteoprogDataLayer = window.meteoprogDataLayer || [];\n";

		foreach ( $this->queued_ids as $id_js ) {
			// Push each informer ID into the global data layer..
			echo 'window.meteoprogDataLayer.push({ id: "' . esc_js( $id_js ) . "\" });\n";
		}
		echo "</script>\n";
	}

	/**
	 * Build the HTML container for a Meteoprog informer.
	 *
	 * This method does not enqueue any scripts — it only generates the HTML markup
	 * for the informer container and registers the informer ID for inclusion in
	 * the global data layer (printed in the document <head>).
	 *
	 * The loader script should be enqueued separately before calling this method.
	 *
	 * @param string $id Informer UUID.
	 * @return string HTML markup for the informer container.
	 */
	public function build_html( $id ) {

		$id_js  = esc_js( $id );
		$div_id = 'meteoprogData_' . $id_js;

		// Register informer ID for the head data layer.
		if ( ! in_array( $id_js, $this->queued_ids, true ) ) {
			$this->queued_ids[] = $id_js;
		}

		$html  = "\n<!-- Meteoprog Weather Widget -->\n";
		$html .= '<div id="' . esc_attr( $div_id ) . "\"></div>\n";

		return $html;
	}

	/**
	 * Enqueue the Meteoprog loader script via a local wrapper file.
	 *
	 * The loader is enqueued only on frontend requests where at least one informer
	 * is rendered (via shortcode, placeholder, or template function), and never
	 * inside the Elementor editor.
	 *
	 * Instead of embedding the external loader directly, a local wrapper script
	 * (assets/js/loader-fallback.js) is enqueued. This wrapper asynchronously
	 * loads the external Meteoprog loader, whose URL and version are passed from
	 * PHP to JavaScript using wp_localize_script(). Both values are filterable via
	 * the 'meteoprog_loader_url' and 'meteoprog_loader_version' filters.
	 *
	 * This approach allows site owners to proxy or override the CDN URL to meet
	 * CSP or corporate security requirements, while remaining fully compliant
	 * with WordPress.org plugin review guidelines.
	 *
	 * @return void
	 */
	public function enqueue_loader() {

		// Do not enqueue inside Elementor editor.
		if ( $this->is_elementor_editor() ) {
			return;
		}

		// Avoid duplicate enqueue if it's already enqueued.
		if ( wp_script_is( 'meteoprog-loader', 'enqueued' ) ) {
			return;
		}

		// Register the script if it's not registered yet.
		if ( ! wp_script_is( 'meteoprog-loader', 'registered' ) ) {
			$path = plugin_dir_path( __DIR__ ) . 'assets/js/loader-fallback.js';
			$ver  = file_exists( $path ) ? filemtime( $path ) : METEOPROG_PLUGIN_VERSION;

			wp_register_script(
				'meteoprog-loader',
				plugin_dir_url( __DIR__ ) . 'assets/js/loader-fallback.js',
				array(),
				$ver,
				true
			);
		}

		// Pass external loader URL and version via localization (filterable).
		$external_url = apply_filters(
			'meteoprog_loader_url',
			'https://cdn.meteoprog.net/informerv4/1/loader.js'
		);

		$external_version = apply_filters(
			'meteoprog_loader_version',
			METEOPROG_PLUGIN_VERSION
		);

		wp_localize_script(
			'meteoprog-loader',
			'MeteoprogLoaderConfig',
			array(
				'url'     => esc_url_raw( $external_url ),
				'version' => esc_attr( $external_version ),
			)
		);

		wp_enqueue_script( 'meteoprog-loader' );
	}

	/**
	 * Add resource hints (e.g., preconnect) for the external Meteoprog CDN.
	 *
	 * This improves performance by initiating early connections to the CDN,
	 * but only on frontend requests where at least one informer is actually used.
	 *
	 * @param string[] $urls          URLs for resource hints.
	 * @param string   $relation_type Relation type (e.g. preconnect, dns-prefetch).
	 * @return string[] Filtered URLs.
	 */
	public function add_resource_hints( $urls, $relation_type ) {
		if ( is_admin() ) {
			return $urls;
		}

		// Add preconnect only if there are queued informer IDs on this page.
		if ( 'preconnect' === $relation_type && ! empty( $this->queued_ids ) ) {
			$urls[] = 'https://cdn.meteoprog.net';
		}

		return $urls;
	}

	/**
	 * Register global template function meteoprog_informer($id).
	 *
	 * This allows developers to call meteoprog_informer() in theme templates.
	 */
	public function register_template_function() {
		if ( ! function_exists( 'meteoprog_informer' ) ) {
			/**
			 * Template helper function for rendering informer by ID.
			 *
			 * @param string|null $id Informer ID (optional). Defaults to saved option.
			 * @return string HTML output.
			 */
			function meteoprog_informer( $id = null ) {
				$frontend = meteoprog_get_frontend_instance();

				if ( ! $frontend ) {
					return '<!-- no instance -->';
				}

				if ( ! $id ) {
					// We can't use $this here — call get_option() directly.
					$id = get_option( 'meteoprog_default_informer_id', '' );
				}
				if ( ! $id ) {
					return '<!-- no informer ID -->';
				}

				// Enqueue loader exactly when widget HTML is generated.
				$frontend->enqueue_loader();

				return $frontend->build_html( $id );
			}
		}
	}

	/**
	 * Determine whether the current request is running inside the Elementor editor.
	 *
	 * @return bool True if Elementor editor mode is active, false otherwise.
	 */
	protected function is_elementor_editor() {
		return function_exists( 'meteoprog_is_elementor_editor_mode' )
			&& meteoprog_is_elementor_editor_mode();
	}

	/**
	 * Get and cache the default informer ID for this request.
	 *
	 * @return string Default informer ID.
	 */
	private function get_default_informer_id() {
		if ( null === self::$default_id_cache ) {
			self::$default_id_cache = get_option( $this->opt_default_id, '' );
		}
		return self::$default_id_cache;
	}

	/**
	 * Flush cached default informer ID.
	 *
	 * @return void
	 */
	public static function flush_cached_default_id() {
		self::$default_id_cache = null;
	}
}
