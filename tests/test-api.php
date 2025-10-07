<?php
/**
 * API Tests for Meteoprog Weather Widgets.
 *
 * Covers API key handling, caching, debug mode, HTTP requests, and user agent logic.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Tests
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

class ApiTest extends WP_Compat_TestCase {


    // -------------------------------------------------------------------------
    // Constructor behavior
    // -------------------------------------------------------------------------

    /**
     * Test that constructor enables debug mode when METEOPROG_DEBUG is defined and true.
     */
    public function test_constructor_enables_debug_mode_via_constant() {
        if (!defined('METEOPROG_DEBUG')) {
            define('METEOPROG_DEBUG', true);
        }

        $api = new Meteoprog_Informers_API();

        $ref = new ReflectionClass($api);
        $prop = $ref->getProperty('debug');
        $prop->setAccessible(true);

        $this->assertTrue($prop->getValue($api));
    }

    /**
     * Constructor should respect the METEOPROG_DEBUG_API_KEY constant:
     * - If defined, it should set the WP option with that value.
     * - It should also disable debug mode.
     *
     * NOTE:
     * In PHP, constants cannot be redefined once set. If the CI or Docker environment
     * defines METEOPROG_DEBUG_API_KEY as empty or false, the constructor will *not*
     * update the option — which is correct. In that case, this test must be skipped
     * to avoid false negatives.
     */
    public function test_constructor_respects_debug_api_key_constant() {
        // If the constant is defined but false/empty — skip the test to avoid environment conflicts.
        if (defined('METEOPROG_DEBUG_API_KEY') && ! METEOPROG_DEBUG_API_KEY) {
            $this->markTestSkipped('METEOPROG_DEBUG_API_KEY is defined as false/empty in the environment; cannot override in tests.');
        }

        // Define the constant only if not already defined.
        $expected = 'forced-key-xyz';
        if (!defined('METEOPROG_DEBUG_API_KEY')) {
            define('METEOPROG_DEBUG_API_KEY', $expected);
        } else {
            // If already defined (non-empty), use the actual environment value.
            $expected = METEOPROG_DEBUG_API_KEY;
        }

        // Prevent filters from interfering with constructor behavior.
        add_filter('meteoprog_debug_mode', function($v) { return $v; }, 1000, 1);

        // Ensure the option is cleared before instantiation.
        delete_option('meteoprog_api_key');

        $api = new Meteoprog_Informers_API();

        // The constructor should write the constant's value into the option.
        $this->assertSame($expected, get_option('meteoprog_api_key'));

        // And debug mode should be disabled when METEOPROG_DEBUG_API_KEY is defined.
        $ref  = new ReflectionClass($api);
        $prop = $ref->getProperty('debug');
        $prop->setAccessible(true);
        $this->assertFalse($prop->getValue($api));

        remove_all_filters('meteoprog_debug_mode');
    }

    // -------------------------------------------------------------------------
    // Basic class existence and methods
    // -------------------------------------------------------------------------

    /**
     * Test that the API class exists and can be autoloaded.
     */
    public function test_api_class_exists() {
        $this->assertTrue(class_exists('Meteoprog_Informers_API'));
    }

    /**
     * Test that the key API methods are available on the class.
     */
    public function test_api_methods_available() {
        $api = new Meteoprog_Informers_API();

        $this->assertTrue(method_exists($api, 'get_informers'));
        $this->assertTrue(method_exists($api, 'get_api_key'));
        $this->assertTrue(method_exists($api, 'clear_cache'));
        $this->assertTrue(method_exists($api, 'validate_key'));
    }

    // -------------------------------------------------------------------------
    // API key handling
    // -------------------------------------------------------------------------

    /**
     * Test that get_api_key() returns the API key from WordPress options.
     */
    public function test_get_api_key_option() {
        update_option('meteoprog_api_key', 'mykey123');

        $api = new Meteoprog_Informers_API();
        $this->assertEquals('mykey123', $api->get_api_key());
    }

    // -------------------------------------------------------------------------
    // cache_key()
    // -------------------------------------------------------------------------

    /**
     * Test that cache_key() generates a deterministic key based on the stored API key.
     */
    public function test_cache_key_generates_expected_value() {
        update_option('meteoprog_api_key', 'unique-key-123');
        $api = new Meteoprog_Informers_API();

        $ref = new ReflectionClass($api);
        $method = $ref->getMethod('cache_key');
        $method->setAccessible(true);

        $key = $method->invoke($api);
        $expected = 'meteoprog_informers_cache_' . md5('unique-key-123');

        $this->assertEquals($expected, $key);
    }

    // -------------------------------------------------------------------------
    // Cache handling
    // -------------------------------------------------------------------------

    /**
     * Test that clear_cache() removes the transient associated with the API cache.
     */
    public function test_clear_cache_removes_transient() {
        $api = new Meteoprog_Informers_API();

        // Use Reflection to get the actual cache key
        $ref = new ReflectionClass($api);
        $method = $ref->getMethod('cache_key');
        $method->setAccessible(true);
        $key = $method->invoke($api);

        // Set a transient with that key and verify it's there
        set_transient($key, ['foo' => 'bar']);
        $this->assertNotFalse(get_transient($key));

        // Call clear_cache() and verify it's removed
        $api->clear_cache();
        $this->assertFalse(get_transient($key));
    }


    // -------------------------------------------------------------------------
    // get_informers() behavior
    // -------------------------------------------------------------------------

    /**
     * Test that get_informers() returns an array from the hardcoded debug data.
     * In debug mode, no HTTP requests are made.
     */
    public function test_get_informers_returns_array_in_debug_mode() {
        $api = new Meteoprog_Informers_API();

        // Force debug mode ON
        $ref = new ReflectionClass($api);
        $prop = $ref->getProperty('debug');
        $prop->setAccessible(true);
        $prop->setValue($api, true);

        $informers = $api->get_informers();
        $this->assertIsArray($informers);
        $this->assertNotEmpty($informers, 'Informers array should not be empty in debug mode');

        $first = $informers[0];
        $this->assertArrayHasKey('informer_id', $first);
        $this->assertArrayHasKey('domain', $first);
    }

    /**
     * Test that get_informers() returns the cached value and does not re-fetch.
     */
    public function test_get_informers_uses_cache_if_available() {
        $api = new Meteoprog_Informers_API();

        // Disable debug mode so cached value is actually used
        $ref = new ReflectionClass($api);
        $prop = $ref->getProperty('debug');
        $prop->setAccessible(true);
        $prop->setValue($api, false);

        // Get actual cache key
        $method = $ref->getMethod('cache_key');
        $method->setAccessible(true);
        $key = $method->invoke($api);

        $dummy = [['id' => 'cached']];
        set_transient($key, $dummy);

        $result = $api->get_informers();
        $this->assertEquals($dummy, $result);
    }

    /**
     * get_informers() should write the fetched informer list into transient cache.
     *
     * This test:
     * - Forces debug mode OFF before constructing the API object.
     * - Sets a fake API key so fetch_from_api() proceeds normally.
     * - Mocks a remote HTTP response.
     * - Verifies that the transient cache contains the mocked informer list.
     */
    public function test_get_informers_sets_cache_after_fetch() {
        // 1. Force debug mode OFF globally BEFORE instantiation.
        add_filter('meteoprog_debug_mode', function() { return false; }, 1000);

        // 2. Set a fake API key — required for fetch_from_api() to run.
        update_option('meteoprog_api_key', 'test-key');

        // 3. Mock HTTP response that fetch_from_api() should return.
        add_filter('pre_http_request', function() {
            return array(
                'body'     => json_encode(array('informers' => array(array('id' => 'ok')))),
                'response' => array('code' => 200),
            );
        });

        // 4. Create API instance after forcing debug OFF.
        $api = new Meteoprog_Informers_API();

        // 5. Get cache key using Reflection.
        $ref    = new ReflectionClass($api);
        $method = $ref->getMethod('cache_key');
        $method->setAccessible(true);
        $key = $method->invoke($api);

        // 6. Clear transient before test.
        delete_transient($key);

        // 7. Trigger fetching and caching.
        $api->get_informers();

        // 8. Verify cached content matches mocked response.
        $cached = get_transient($key);
        $this->assertNotFalse($cached, 'Transient cache should be created after fetching informers.');
        $this->assertEquals(
            array(array('id' => 'ok')),
            $cached,
            'The cached value should match the mocked fetched informer list.'
        );

        // 9. Cleanup.
        remove_all_filters('pre_http_request');
        remove_all_filters('meteoprog_debug_mode');
    }

    // -------------------------------------------------------------------------
    // validate_key() behavior
    // -------------------------------------------------------------------------

    /**
     * Test that validate_key() returns true when the remote responds with informers.
     */
    public function test_validate_key_returns_true_for_valid_key() {
        add_filter('pre_http_request', function() {
            return array(
                'body'     => json_encode(array('informers' => array(array('id' => 'ok')))),
                'response' => array('code' => 200),
            );
        });

        $api = new Meteoprog_Informers_API();

        // Disable debug mode to actually call fetch_from_api()
        $ref = new ReflectionClass($api);
        $prop = $ref->getProperty('debug');
        $prop->setAccessible(true);
        $prop->setValue($api, false);

        $this->assertTrue($api->validate_key('valid-key'));

        remove_all_filters('pre_http_request');
    }

    /**
     * Test that validate_key() returns false when the remote request fails or returns empty.
     */
    public function test_validate_key_returns_false_for_invalid_key() {
        add_filter('pre_http_request', function() {
            return array(
                'body'     => '{}',
                'response' => array('code' => 403),
            );
        });

        $api = new Meteoprog_Informers_API();

        // Disable debug mode to actually call fetch_from_api()
        $ref = new ReflectionClass($api);
        $prop = $ref->getProperty('debug');
        $prop->setAccessible(true);
        $prop->setValue($api, false);

        $this->assertFalse($api->validate_key('bad-key'));

        remove_all_filters('pre_http_request');
    }

    // -------------------------------------------------------------------------
    // fetch_from_api() behavior
    // -------------------------------------------------------------------------

    /**
     * Test that fetch_from_api() returns empty array on WP_Error response.
     */
    public function test_fetch_from_api_returns_empty_on_wp_error() {
        add_filter('pre_http_request', function() {
            return new WP_Error('fail');
        });

        $api = new Meteoprog_Informers_API();
        $ref = new ReflectionClass($api);
        $method = $ref->getMethod('fetch_from_api');
        $method->setAccessible(true);

        $result = $method->invoke($api, 'testkey');
        $this->assertEquals([], $result);

        remove_all_filters('pre_http_request');
    }

    /**
     * Test that fetch_from_api() returns empty array on non-200 response.
     */
    public function test_fetch_from_api_returns_empty_on_non_200() {
        add_filter('pre_http_request', function() {
            return array('body' => '{}', 'response' => array('code' => 403));
        });

        $api = new Meteoprog_Informers_API();
        $ref = new ReflectionClass($api);
        $method = $ref->getMethod('fetch_from_api');
        $method->setAccessible(true);

        $result = $method->invoke($api, 'testkey');
        $this->assertEquals([], $result);

        remove_all_filters('pre_http_request');
    }

    /**
     * Test that fetch_from_api() returns informers when response contains "informers" key.
     */
    public function test_fetch_from_api_returns_informers_when_present() {
        add_filter('pre_http_request', function() {
            return array(
                'body'     => json_encode(array('informers' => array(array('id' => 'ok')))),
                'response' => array('code' => 200),
            );
        });

        $api = new Meteoprog_Informers_API();
        $ref = new ReflectionClass($api);
        $method = $ref->getMethod('fetch_from_api');
        $method->setAccessible(true);

        $result = $method->invoke($api, 'testkey');
        $this->assertEquals(array(array('id' => 'ok')), $result);

        remove_all_filters('pre_http_request');
    }

    /**
     * Test that fetch_from_api() returns array data when JSON is indexed array.
     */
    public function test_fetch_from_api_returns_array_when_json_is_indexed() {
        add_filter('pre_http_request', function() {
            return array(
                'body'     => json_encode(array(array('id' => 'indexed'))),
                'response' => array('code' => 200),
            );
        });

        $api = new Meteoprog_Informers_API();
        $ref = new ReflectionClass($api);
        $method = $ref->getMethod('fetch_from_api');
        $method->setAccessible(true);

        $result = $method->invoke($api, 'testkey');
        $this->assertEquals(array(array('id' => 'indexed')), $result);

        remove_all_filters('pre_http_request');
    }

    /**
     * Test that fetch_from_api() returns [] immediately when no API key is stored.
     */
    public function test_fetch_from_api_returns_empty_when_no_key() {
        delete_option('meteoprog_api_key');

        $api = new Meteoprog_Informers_API();
        $ref = new ReflectionClass($api);
        $method = $ref->getMethod('fetch_from_api');
        $method->setAccessible(true);

        $result = $method->invoke($api, null);
        $this->assertEquals([], $result);
    }

    // -------------------------------------------------------------------------
    // load_from_array() behavior
    // -------------------------------------------------------------------------

    /**
     * Test that load_from_array() returns the expected hardcoded structure.
     */
    public function test_load_from_array_returns_expected_structure() {
        $api = new Meteoprog_Informers_API();
        $ref = new ReflectionClass($api);
        $method = $ref->getMethod('load_from_array');
        $method->setAccessible(true);

        $result = $method->invoke($api);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        $first = $result[0];
        $this->assertArrayHasKey('informer_id', $first);
        $this->assertArrayHasKey('domain', $first);
        $this->assertArrayHasKey('active', $first);
    }

    // -------------------------------------------------------------------------
    // get_user_agent() behavior
    // -------------------------------------------------------------------------

    /**
     * Test that get_user_agent() returns a string containing the plugin name and URL.
     */
    public function test_get_user_agent_contains_expected_string() {
        $api = new Meteoprog_Informers_API();

        $ref = new ReflectionClass($api);
        $method = $ref->getMethod('get_user_agent');
        $method->setAccessible(true);

        $ua = $method->invoke($api);

        $this->assertStringContainsString('MeteoprogWPPlugin/', $ua);
        $this->assertStringContainsString('https://meteoprog.com', $ua);
    }

    /**
     * Test that get_user_agent() caches the UA string on subsequent calls.
     */
    public function test_get_user_agent_is_cached() {
        $api = new Meteoprog_Informers_API();

        $ref = new ReflectionClass($api);
        $method = $ref->getMethod('get_user_agent');
        $method->setAccessible(true);

        $first = $method->invoke($api);
        $second = $method->invoke($api);

        $this->assertSame($first, $second);
    }
}
