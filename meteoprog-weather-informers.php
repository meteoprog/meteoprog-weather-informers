<?php
/**
 * Plugin Name: Meteoprog Weather Widget
 * Plugin URI: https://billing.meteoprog.com
 * Description: Embed Meteoprog weather widgets on your WordPress site using a free API key. Supports shortcodes, placeholders, Gutenberg block, Elementor, and Shortcodes Ultimate.
 * Version: 1.0
 * Author: meteoprog
 * Author URI: https://profiles.wordpress.org/meteoprog/
 * Requires at least: 4.9
 * Tested up to: 6.8
 * Requires PHP: 5.6
 * Text Domain: meteoprog-weather-informers
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package    MeteoprogWeatherInformers
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

/**
 * Technical Notes for Reviewers
 *
 * Compatibility:
 * - Fully compatible with PHP 5.6 – 8.3
 * - Minimum WordPress version: 4.9
 * - Tested against WordPress 4.9, 5.8, 5.9, 6.2.2, 6.6.2, 6.7.3, 6.8.3, latest (key historical and current stable releases)
 *
 * Coding Standards:
 * - The codebase follows the official WordPress Coding Standards (WPCS)
 * - PHPCS is configured and used to validate all PHP files before release
 *
 * Automated Testing:
 * - All plugin functionality is covered by unit tests for each supported PHP & WordPress combination
 * - Tests are executed inside Docker containers using wp scaffold plugin-tests + yoast/phpunit-polyfills
 * - The full test matrix and Makefile are available in the GitHub repository
 *
 * Backward Compatibility:
 * - The plugin maintains compatibility with legacy environments (PHP 5.6 and WordPress 4.9)
 * - Backward compatibility is verified via dedicated test suites
 *
 * External Libraries:
 * - The plugin does not bundle any external PHP libraries or SDKs
 * - All network communication uses standard WordPress HTTP APIs (wp_remote_get)
 *
 * Data Collection:
 * - The plugin does not collect, store, or transmit any personal data or usage telemetry
 *
 * Release Workflow:
 * - Planned publication to the WordPress.org SVN repository is automated from the GitHub repository using CI/CD pipelines
 * - This ensures clean, reproducible, and traceable releases with no manual file uploads
 *
 * Translations:
 * - Currently the plugin includes only the English (en_US) base strings
 * - Additional translations will be added after the plugin is published via translate.wordpress.org (GlotPress)
 *
 * Privacy:
 * - The plugin registers suggested privacy policy content for site administrators using wp_add_privacy_policy_content()
 * - This content describes all outgoing API requests and data handling related to Meteoprog services
 *
 * Dependencies:
 * - No runtime Composer dependencies; only dev tools (PHPUnit, Yoast Polyfills) used for testing
 *
 * Build Process:
 * - Distribution ZIP files are generated automatically using wp dist-archive inside a clean container
 * - Development files are excluded from the release build
 *
 * Repository:
 * https://github.com/meteoprog/meteoprog-weather-informers
 *
 * This note is included for transparency during the plugin review process.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------------
// Plugin Constants
// -----------------------------------------------------------------------------

// Used for stable cache-busting of assets in production environments.
// Fallback for enqueue when filemtime() is not available (e.g. on CDN).
define( 'METEOPROG_PLUGIN_VERSION', '1.0' );

// Absolute path to the main plugin file (used for reference in includes and hooks).
define( 'METEOPROG_PLUGIN_FILE', __FILE__ );

// Filesystem path to the plugin directory (no trailing slash).
define( 'METEOPROG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Public URL to the plugin directory (with trailing slash).
define( 'METEOPROG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Debug mode helper.
 *
 * Uncomment the line below to enable debug mode.
 * In this mode, the plugin will load informers from a local JSON file
 * (`/assets/test/test-informers.json`) instead of making HTTP API requests.
 */
if ( ! defined( 'METEOPROG_DEBUG' ) ) {
	define( 'METEOPROG_DEBUG', 0 );
}

// -----------------------------------------------------------------------------
// Autoload classes
// -----------------------------------------------------------------------------

require_once __DIR__ . '/includes/functions-helpers.php';
require_once __DIR__ . '/includes/functions-admin.php';
require_once __DIR__ . '/includes/functions-privacy.php';
require_once __DIR__ . '/includes/functions-uninstall.php';
require_once __DIR__ . '/includes/class-meteoprog-informers-api.php';
require_once __DIR__ . '/includes/class-meteoprog-informers-frontend.php';
require_once __DIR__ . '/includes/class-meteoprog-informers-admin.php';
require_once __DIR__ . '/includes/class-meteoprog-informers-block.php';
require_once __DIR__ . '/includes/class-meteoprog-informers-widget.php';


// Load WP-CLI commands (only runs in CLI context).
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once __DIR__ . '/includes/class-meteoprog-informers-cli.php';
}


// -----------------------------------------------------------------------------
// Plugin bootstrap
// -----------------------------------------------------------------------------

require_once __DIR__ . '/plugin.php';
add_action( 'plugins_loaded', 'meteoprog_plugin_bootstrap' );


// -----------------------------------------------------------------------------
// Privacy Policy: Add suggested content for site administrators
// -----------------------------------------------------------------------------

add_action( 'admin_init', 'meteoprog_register_privacy_policy' );


// -----------------------------------------------------------------------------
// Uninstall: remove only transients (keep options unless user confirms via UI)
// -----------------------------------------------------------------------------

register_uninstall_hook( __FILE__, 'meteoprog_informers_on_uninstall' );


// -----------------------------------------------------------------------------
// Settings + Delete Data links in the Plugins screen
// -----------------------------------------------------------------------------

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'meteoprog_plugin_action_links' );


// -----------------------------------------------------------------------------
// Admin page: Confirm full plugin data removal (hidden under Tools, then removed)
// -----------------------------------------------------------------------------

add_action( 'admin_menu', 'meteoprog_add_remove_data_page' );


// -----------------------------------------------------------------------------
// Permanently delete all plugin data (called only after explicit confirmation)
// -----------------------------------------------------------------------------

// Hide the submenu item so page is accessible only via direct URL.
add_action( 'admin_head', 'meteoprog_hide_remove_data_page' );
