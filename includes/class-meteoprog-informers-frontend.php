<?php
/**
 * Frontend Renderer for Meteoprog Weather Widgets.
 *
 * Handles all frontend integration points:
 * - Shortcode [meteoprog_informer id="..."]
 * - Placeholder replacement {meteoprog_informer_UUID}
 * - Template helper function meteoprog_informer($id)
 * - Enqueuing loader.js via local wrapper script
 *
 * Compatible with PHP 5.6+ and WordPress 4.9+.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Frontend
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined('ABSPATH') ) {
    exit;
}

class Meteoprog_Informers_Frontend {
    private $api;
    private $opt_default_id = 'meteoprog_default_informer_id';

    private static $default_id_cache = null;
    
    public function __construct($api) {
        $this->api = $api;

        // Shortcode [meteoprog_informer id="..."]
        add_shortcode('meteoprog_informer', array($this, 'shortcode'));

        // Replace placeholders {meteoprog_informer_UUID}
        add_filter('the_content', array($this, 'replace_placeholders'));

        // Enable shortcode parsing in legacy Text Widgets
        add_filter('widget_text', 'do_shortcode');

        // Enqueue global loader.js (once per page)
        add_action('wp_enqueue_scripts', array($this, 'enqueue_loader'));

        // Template helper function meteoprog_informer($id)
        add_action('init', array($this, 'register_template_function'));
    }

     /**
     * Shortcode callback
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output or comment.
     */
    public function shortcode($atts) {
        $atts = shortcode_atts(array('id' => null), $atts);
        $id = $atts['id'] ?: $this->get_default_informer_id();
        if (!$id) return '<!-- Meteoprog informer: ID not set -->';

        return $this->build_html($id);
    }

    /**
     * Replace placeholders like {meteoprog_informer_UUID}
     * 
     * @return string HTML output or comment.
     */
        /**
     * Replace placeholders like {meteoprog_informer_UUID} or {meteoprog_informer}.
     */
    public function replace_placeholders( $content ) {
        return preg_replace_callback(
            '/\{meteoprog_informer(?:_([A-Za-z0-9\-]+))?\}/',
            function ( $matches ) {
                // If UUID is provided → use it
                if ( ! empty( $matches[1] ) ) {
                    return $this->build_html( $matches[1] );
                }

                // Otherwise fallback to default informer ID
                $default_id = $this->get_default_informer_id();
                
                if ( ! $default_id ) {
                    return '<!-- Meteoprog informer: default ID not set -->';
                }

                return $this->build_html( $default_id );
            },
            $content
        );
    }

    /**
     * Build informer HTML
     *
     * @param string $id — informer UUID
     * @param bool $with_loader — true = embed loader.js directly (for Gutenberg editor)
     */
    public function build_html($id, $with_loader = false) {
        static $data_layer_initialized = false;

        $id_js  = esc_js($id);
        $div_id = 'meteoprogData_' . $id_js;

        $html  = "\n<!-- meteoprog.com informer -->\n";

        // Push to global data layer only once per page
        if ( ! $data_layer_initialized ) {
            $html .= "<script>window.meteoprogDataLayer=window.meteoprogDataLayer||[];</script>\n";
            $data_layer_initialized = true;
        }

        $html .= "<script>window.meteoprogDataLayer.push({id:\"$id_js\"});</script>\n";
        $html .= "<div id=\"$div_id\"></div>\n";

        return $html;
    }


    /**
     * Enqueue Meteoprog loader via a local wrapper script.
     *
     * Important for WordPress.org review:
     * - We do NOT enqueue external scripts directly from PHP.
     * - Instead, we enqueue a local JS file (assets/js/loader-fallback.js).
     * - That local script then dynamically loads the external loader.js (async).
     * - This ensures plugin passes WP.org review rules about external assets.
     *
     * Loader is enqueued on all frontend pages except inside Elementor editor.
     * This guarantees informer availability everywhere (home, archives, templates)
     * without scanning content or blocks, and avoids double-loading in editor.
     */
    public function enqueue_loader() {

        // Do not enqueue inside Elementor editor
        if ( $this->is_elementor_editor() ) {
            return;
        }

        // Prevent multiple enqueues
        static $enqueued = false;
        if ( $enqueued ) {
            return;
        }
        $enqueued = true;

        $path = plugin_dir_path( dirname( __FILE__ ) ) . 'assets/js/loader-fallback.js';
        $ver  = file_exists($path) ? filemtime($path) : METEOPROG_PLUGIN_VERSION;

        wp_register_script(
            'meteoprog-loader',
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/loader-fallback.js',
            array(),
            $ver,
            true
        );
        wp_enqueue_script( 'meteoprog-loader' );
    }

    /**
     * Register global template function meteoprog_informer($id)
     */
    public function register_template_function() {
        if (!function_exists('meteoprog_informer')) {
            function meteoprog_informer($id = null) {
                $inst = isset($GLOBALS['meteoprog_weather_informers_instance'])
                    ? $GLOBALS['meteoprog_weather_informers_instance']
                    : null;

                if (!$inst) return '<!-- no instance -->';

                if (!$id) {
                    // We can't use $this here — call get_option directly
                    $id = get_option( 'meteoprog_default_informer_id', '' );
                }
                if (!$id) return '<!-- no informer ID -->';

                return $inst->build_html($id);
            }
        }
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
        return function_exists('meteoprog_is_elementor_editor_mode') 
            && meteoprog_is_elementor_editor_mode();
    }

    /**
     * Get and cache the default informer ID for this request.
     *
     * @return string
     */
    private function get_default_informer_id() {
        if ( self::$default_id_cache === null ) {
            self::$default_id_cache = get_option( $this->opt_default_id, '' );
        }
        return self::$default_id_cache;
    }

    public static function flush_cached_default_id() {
        self::$default_id_cache = null;
    }


}
