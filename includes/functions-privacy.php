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

	/* translators: This is the privacy policy text shown in WP Admin (Settings > Privacy). */
	$text = __(
		"This plugin connects to Meteoprog services to render weather informers.\n\n" .
		"• Outgoing requests: The plugin requests informer metadata from https://billing.meteoprog.com via HTTPS. Your site domain is sent in the 'X-Site-Domain' header to identify the requesting site. The Authorization header includes your informer API key saved in WordPress options.\n\n" .
		"• Frontend: The plugin asynchronously loads a JavaScript file from https://cdn.meteoprog.net to render the widget. When loading that file, the third-party service may receive visitor IP addresses and may set cookies as part of content delivery.\n\n" .
		"For more information, please review the Meteoprog legal documents:\n" .
		"• Privacy Policy: https://billing.meteoprog.com/p/privacy_policy\n" .
		"• User Agreement: https://billing.meteoprog.com/p/user_agreement\n" .
		"• Legal Information: https://billing.meteoprog.com/p/legal_information\n" .
		'• Refund Policy: https://billing.meteoprog.com/p/refund_policy',
		'meteoprog-weather-informers'
	);

	$content = wp_kses_post( wpautop( $text ) );

	wp_add_privacy_policy_content(
		__( 'Meteoprog Weather Widget', 'meteoprog-weather-informers' ),
		$content
	);
}
