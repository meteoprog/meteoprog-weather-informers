<?php
/**
 * Plugin Bootstrap for Meteoprog Weather Informers.
 *
 * Initializes text domain, core API classes, frontend and admin integrations,
 * and optional builder integrations (Elementor, Shortcodes Ultimate).
 * This file is loaded from the main plugin file during the 'plugins_loaded' hook.
 *
 * Compatible with PHP 5.6+ and WordPress 4.9+.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Core
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main plugin bootstrap.
 *
 * Initializes text domain, core API classes, frontend and admin integrations,
 * and optional builder integrations (Elementor, Shortcodes Ultimate).
 *
 * This function is hooked into 'plugins_loaded' to ensure all WordPress APIs
 * and plugin dependencies are fully available before initializing.
 */
function meteoprog_plugin_bootstrap() {

	// -------------------------------------------------------------------------
	// Fix: Elementor calls get_plugins() without including plugin.php on old WP.
	// -------------------------------------------------------------------------
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	load_plugin_textdomain(
		'meteoprog-weather-informers',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);

	$api      = new Meteoprog_Informers_API();
	$frontend = new Meteoprog_Informers_Frontend( $api );
	$admin    = new Meteoprog_Informers_Admin( $api, $frontend );
	$block    = new Meteoprog_Informers_Block( $frontend, $api );

	// Store instance globally (used by helper function meteoprog_informer()).
	$GLOBALS['meteoprog_weather_informers_instance'] = $frontend;
	$GLOBALS['meteoprog_weather_informers_api']      = $api;

	// Elementor integration (optional).
	if ( class_exists( '\Elementor\Plugin' ) ) {
		require_once __DIR__ . '/includes/class-meteoprog-informers-elementor.php';
		new Meteoprog_Informers_Elementor( $frontend, $api );
	}

	// Shortcodes Ultimate integration (optional).
	if ( defined( 'SU_PLUGIN_VERSION' ) || function_exists( 'su_shortcode_init' ) ) {
		require_once __DIR__ . '/includes/integrations/integration-shortcodes-ultimate.php';
	}
}
