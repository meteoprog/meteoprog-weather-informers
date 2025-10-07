<?php
/**
 * PHPUnit Bootstrap — Standard (Non-CLI)
 *
 * Sets up the environment for running PHPUnit tests with the WordPress core
 * test suite. Used in local development and CI environments.
 *
 * @package    MeteoprogWeatherInformers
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

// 0. Map environment variables into PHP constants for plugin configuration.
//    This allows passing METEOPROG_DEBUG and METEOPROG_DEBUG_API_KEY
//    via Docker or CI without modifying wp-tests-config.php.

$debugEnv = getenv('METEOPROG_DEBUG');
if ($debugEnv !== false && !defined('METEOPROG_DEBUG')) {
    define('METEOPROG_DEBUG', (int)$debugEnv === 1);
}

$apiKeyEnv = getenv('METEOPROG_DEBUG_API_KEY');
if ($apiKeyEnv !== false && !defined('METEOPROG_DEBUG_API_KEY')) {
    define('METEOPROG_DEBUG_API_KEY', $apiKeyEnv);
}

// 1. Load Composer's autoloader for all dependencies.
//    (e.g., PHPUnit, Yoast Polyfills, and other dev libraries)
require dirname(__DIR__) . '/vendor/autoload.php';

// 2. Load the WordPress test suite bootstrap.
//    This initializes WordPress, sets up the test database,
//    defines ABSPATH, and loads the core testing functions.
require __DIR__ . '/bootstrap.php';

// 3. Load the compatibility test case class.
//    This provides a unified base test class that works with
//    both old (PHPUnit 5.7) and modern PHPUnit versions.
require __DIR__ . '/WP_Compat_TestCase.php';
