<?php
/**
 * Privacy Policy Integration for Meteoprog Weather Informers.
 *
 * Registers suggested privacy policy content for site administrators,
 * describing external API requests and data handling performed by the plugin.
 *
 * Compatible with PHP 5.6+ and WordPress 4.9+ (GDPR tools introduced in 4.9.6+).
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
 * Register suggested privacy policy content for site administrators.
 *
 * @return void
 */
function meteoprog_register_privacy_policy() {
	if ( ! function_exists( 'wp_add_privacy_policy_content' ) ) {
		return;
	}

	/* This is the privacy policy text shown in WP Admin (Settings > Privacy). */

	$content = '<p>' . __( 'This plugin connects to Meteoprog services to render weather informers.', 'meteoprog-weather-informers' ) . '</p>';

	$content .= '<h4>' . __( 'Outgoing requests', 'meteoprog-weather-informers' ) . '</h4>';
	$content .= '<p>' . __(
		'The plugin requests informer metadata from <code>https://billing.meteoprog.com</code> via HTTPS. 
Your site domain is sent in the <code>X-Site-Domain</code> header to identify the requesting site. 
The Authorization header includes your informer API key saved in WordPress options.',
		'meteoprog-weather-informers'
	) . '</p>';

	$content .= '<h4>' . __( 'Frontend', 'meteoprog-weather-informers' ) . '</h4>';
	$content .= '<p>' . __(
		'The plugin asynchronously loads a JavaScript file from <code>https://cdn.meteoprog.net</code> to render the widget. 
When loading that file, the third-party service may receive visitor IP addresses and may set technical cookies required for content delivery or security. 
These cookies are managed by Meteoprog and are subject to their privacy policy.',
		'meteoprog-weather-informers'
	) . '</p>';

	$content .= '<h4>' . __( 'Legal Information', 'meteoprog-weather-informers' ) . '</h4>';
	$content .= '<ul>';
	$content .= '<li><a href="https://billing.meteoprog.com/p/privacy_policy" target="_blank" rel="noopener noreferrer">' . __( 'Privacy Policy', 'meteoprog-weather-informers' ) . '</a></li>';
	$content .= '<li><a href="https://billing.meteoprog.com/p/user_agreement" target="_blank" rel="noopener noreferrer">' . __( 'User Agreement', 'meteoprog-weather-informers' ) . '</a></li>';
	$content .= '<li><a href="https://billing.meteoprog.com/p/legal_information" target="_blank" rel="noopener noreferrer">' . __( 'Legal Information', 'meteoprog-weather-informers' ) . '</a></li>';
	$content .= '<li><a href="https://billing.meteoprog.com/p/refund_policy" target="_blank" rel="noopener noreferrer">' . __( 'Refund Policy', 'meteoprog-weather-informers' ) . '</a></li>';
	$content .= '</ul>';

	wp_add_privacy_policy_content(
		__( 'Meteoprog Weather Widget', 'meteoprog-weather-informers' ),
		wp_kses_post( $content )
	);
}
