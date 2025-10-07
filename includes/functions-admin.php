<?php
/**
 * Admin Functions for Meteoprog Weather Informers.
 *
 * Contains WordPress admin-related logic: plugin action links,
 * hidden Tools submenu for data removal, and admin page rendering.
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
 * Adds custom action links below the plugin name in the Plugins list.
 */
function meteoprog_plugin_action_links( $links ) {
	$settings_url = admin_url( 'options-general.php?page=meteoprog-informers' );
	$remove_url   = admin_url( 'tools.php?page=meteoprog-remove-data' ); // IMPORTANT: tools.php as parent.

	// Guard against null for PHP 8.1+ (esc_url expects string).
	$settings_url = is_string( $settings_url ) ? $settings_url : '';
	$remove_url   = is_string( $remove_url ) ? $remove_url : '';

	if ( $settings_url !== '' ) {
		$settings_link = '<a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Settings', 'meteoprog-weather-informers' ) . '</a>';
		array_unshift( $links, $settings_link );
	}

	if ( $remove_url !== '' ) {
		$remove_link = '<a href="' . esc_url( $remove_url ) . '" style="color:red;">' . esc_html__( 'Delete Data', 'meteoprog-weather-informers' ) . '</a>';
		$links[]     = $remove_link;
	}

	return $links;
}

/**
 * Registers a hidden Tools submenu page for permanent data removal.
 */
function meteoprog_add_remove_data_page() {
	add_submenu_page(
		'tools.php', // real parent to avoid header/title deprecations.
		__( 'Remove Meteoprog Plugin Data', 'meteoprog-weather-informers' ),
		__( 'Remove Meteoprog Plugin Data', 'meteoprog-weather-informers' ),
		'manage_options',
		'meteoprog-remove-data',
		'meteoprog_render_remove_data_page'
	);
}
