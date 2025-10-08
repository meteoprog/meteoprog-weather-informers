<?php
/**
 * Helper Functions Tests for Meteoprog Weather Widgets.
 *
 * Covers core helper functions:
 * - meteoprog_mask_string()
 * - meteoprog_host_from_url()
 * - meteoprog_is_elementor_editor_mode()
 * - meteoprog_clear_cache() and API cache clearing.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Tests
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

class HelpersTest extends WP_Compat_TestCase {

    // -------------------------------------------------------------------------
    // meteoprog_mask_string()
    // -------------------------------------------------------------------------

    public function test_mask_key_short_string_returns_original() {
        $this->assertEquals('abc123', meteoprog_mask_string('abc123'));
    }

    public function test_mask_string_long_string_masks_middle() {
        $key    = '1234567890abcdef1234567890';
        $masked = meteoprog_mask_string($key);

        $this->assertStringStartsWith('1234', $masked);
        $this->assertStringEndsWith('7890', $masked);
        $this->assertStringContainsString('*', $masked);
    }

    // -------------------------------------------------------------------------
    // meteoprog_host_from_url()
    // -------------------------------------------------------------------------

    public function test_host_from_full_url() {
        $this->assertEquals('example.com', meteoprog_host_from_url('https://example.com/path?x=1'));
    }

    public function test_host_from_plain_domain() {
        $this->assertEquals('example.com', meteoprog_host_from_url('example.com'));
    }

    public function test_host_from_empty_returns_empty() {
        $this->assertEquals('', meteoprog_host_from_url(''));
    }

    // -------------------------------------------------------------------------
    // meteoprog_is_elementor_editor_mode()
    // -------------------------------------------------------------------------

    public function test_elementor_editor_mode_returns_false_by_default() {
        // Elementor not loaded in test env
        $this->assertFalse(meteoprog_is_elementor_editor_mode());
    }

    // -------------------------------------------------------------------------
    // meteoprog_clear_cache()
    // -------------------------------------------------------------------------
    public function test_helpers_function_and_class_exist() {
  
        $this->assertTrue(function_exists('meteoprog_clear_cache'), 'meteoprog_clear_cache() not found');

        $this->assertTrue(class_exists('Meteoprog_Informers_API'), 'Meteoprog_Informers_API class not found');

        $this->assertTrue(method_exists('Meteoprog_Informers_API', 'clear_cache'), 'clear_cache() method not found in Meteoprog_Informers_API');
    }

    public function test_clear_cache_deletes_transient() {
        $api = new Meteoprog_Informers_API();

        // Reflect to get the actual cache key
        $ref    = new ReflectionClass($api);
        $method = $ref->getMethod('cache_key');
        $method->setAccessible(true);
        $key    = $method->invoke($api);

        // Set a transient, call meteoprog_clear_cache(), and verify it's gone
        set_transient($key, 'foo');
        $this->assertNotFalse(get_transient($key));

        meteoprog_clear_cache();
        $this->assertFalse(get_transient($key));
    }

    /**
     * Ensure that helper functions return null by default
     * when no global frontend or API instances are set.
     */
    public function test_get_frontend_and_api_instance_returns_null_by_default() {
        $this->assertNull(meteoprog_get_frontend_instance());
        $this->assertNull(meteoprog_get_api_instance());
    }
}
