<?php
/**
 * PHPUnit Compatibility Test Case for Meteoprog Weather Informers.
 *
 * Declares a base test case that extends WP_UnitTestCase and integrates
 * all Yoast PHPUnit Polyfills for maximum compatibility between
 * PHPUnit 5.7 (PHP 5.6) and modern PHPUnit versions.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Tests
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

// Safely declare all required Yoast PHPUnit Polyfill traits if Yoast is not installed.
$polyfillTraits = [
    'Yoast\\PHPUnitPolyfills\\Polyfills\\AssertClosedResource',
    'Yoast\\PHPUnitPolyfills\\Polyfills\\AssertEqualsSpecializations',
    'Yoast\\PHPUnitPolyfills\\Polyfills\\AssertFileEqualsSpecializations',
    'Yoast\\PHPUnitPolyfills\\Polyfills\\AssertIsType',
    'Yoast\\PHPUnitPolyfills\\Polyfills\\AssertObjectEquals',
    'Yoast\\PHPUnitPolyfills\\Polyfills\\AssertObjectProperty',
    'Yoast\\PHPUnitPolyfills\\Polyfills\\AssertStringContains',
    'Yoast\\PHPUnitPolyfills\\Polyfills\\AssertionRenames',
    'Yoast\\PHPUnitPolyfills\\Polyfills\\EqualToSpecializations',
    'Yoast\\PHPUnitPolyfills\\Polyfills\\ExpectExceptionMessageMatches',
    'Yoast\\PHPUnitPolyfills\\Polyfills\\ExpectExceptionObject',
];

foreach ($polyfillTraits as $trait) {
    if (!trait_exists($trait)) {
        // Extract the namespace and short trait name
        $parts = explode('\\', $trait);
        $shortName = array_pop($parts);
        $ns = implode('\\', $parts);

        // Dynamically declare an empty trait in the correct namespace
        eval("
        namespace $ns {
            trait $shortName {}
        }
        ");
    }
}

// Import all Yoast PHPUnit Polyfill traits after ensuring they exist.
use Yoast\PHPUnitPolyfills\Polyfills\AssertClosedResource;
use Yoast\PHPUnitPolyfills\Polyfills\AssertEqualsSpecializations;
use Yoast\PHPUnitPolyfills\Polyfills\AssertFileEqualsSpecializations;
use Yoast\PHPUnitPolyfills\Polyfills\AssertIsType;
use Yoast\PHPUnitPolyfills\Polyfills\AssertObjectEquals;
use Yoast\PHPUnitPolyfills\Polyfills\AssertObjectProperty;
use Yoast\PHPUnitPolyfills\Polyfills\AssertStringContains;
use Yoast\PHPUnitPolyfills\Polyfills\AssertionRenames;
use Yoast\PHPUnitPolyfills\Polyfills\EqualToSpecializations;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectExceptionMessageMatches;
use Yoast\PHPUnitPolyfills\Polyfills\ExpectExceptionObject;

/**
 * Base compatibility test case that combines WP_UnitTestCase
 * with all Yoast PHPUnit Polyfills 2.x assertions.
 */
abstract class WP_Compat_TestCase extends WP_UnitTestCase {
    use AssertClosedResource;
    use AssertEqualsSpecializations;
    use AssertFileEqualsSpecializations;
    use AssertIsType;
    use AssertObjectEquals;
    use AssertObjectProperty;
    use AssertStringContains;
    use AssertionRenames;
    use EqualToSpecializations;
    use ExpectExceptionMessageMatches;
    use ExpectExceptionObject;
}
