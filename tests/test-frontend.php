<?php
/**
 * Frontend Tests for Meteoprog Weather Widgets.
 *
 * Covers shortcode rendering, placeholder replacement,
 * loader script enqueueing, and global template function behavior.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Tests
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

class FrontendTest extends WP_Compat_TestCase {

    private $api;
    private $frontend;

    /**
     * @before
     */
    public function reset_static_cache_before_each_test() {
        Meteoprog_Informers_Frontend::flush_cached_default_id();
    }

    /**
     * @before
     * Runs automatically before each test. Replaces setUp()/set_up() to avoid
     * signature conflicts between PHPUnit 5.7 (PHP 5.6) and Yoast Polyfills.
     */
    public function prepare_environment() {
        // Create a mock object for the API dependency
        $this->api = $this->getMockBuilder(stdClass::class)->getMock();


        if ( ! defined( 'METEOPROG_PLUGIN_VERSION' ) ) {
            define( 'METEOPROG_PLUGIN_VERSION', 'test-version' );
        }

        // Instantiate the frontend class with the mocked API
        $this->frontend = new Meteoprog_Informers_Frontend($this->api);
        
        // Store the frontend instance in a global variable.
        // This is required for the global function meteoprog_informer() to work.
        $GLOBALS['meteoprog_weather_informers_instance'] = $this->frontend;
    }


    // -------------------------------------------------------------------------
    // Basic existence tests
    // -------------------------------------------------------------------------

    /**
     * Ensure the frontend class exists and can be autoloaded.
     */
    public function test_class_exists() {
        $this->assertTrue(class_exists('Meteoprog_Informers_Frontend'));
    }

    // -------------------------------------------------------------------------
    // Shortcode rendering tests
    // -------------------------------------------------------------------------

    /**
     * Test shortcode output without the "id" attribute.
     */
    public function test_shortcode_no_id() {
        $output = do_shortcode('[meteoprog_informer]');
        $this->assertStringContainsString('ID not set', $output);
    }

    /**
     * Test shortcode output with the "id" attribute.
     */
    public function test_shortcode_with_id() {
        $output = do_shortcode('[meteoprog_informer id="123"]');
        $this->assertStringContainsString('meteoprogData_123', $output);
    }

    /**
     * Test shortcode() output when no ID attribute is given
     * but a default informer ID is set in options.
     *
     * Expected behavior: the shortcode should render HTML
     * containing that default ID.
     */
    public function test_shortcode_uses_default_id_when_no_attribute() {
        // 1. Set a known default informer ID
        update_option('meteoprog_default_informer_id', 'foo');

        // 2. Run the shortcode without "id" attribute
        $output = do_shortcode('[meteoprog_informer]');

        // 3. Assert that the output contains the default ID
        $this->assertStringContainsString(
            'meteoprogData_foo',
            $output,
            'Shortcode should render informer HTML using the default informer ID.'
        );
    }

    // -------------------------------------------------------------------------
    // Placeholder replacement filter
    // -------------------------------------------------------------------------

    /**
     * Test that placeholders {meteoprog_informer_ID} are correctly replaced
     * inside the_content filter.
     */
    public function test_replace_placeholders() {
        $content  = 'Hello {meteoprog_informer_777} world';
        $filtered = apply_filters('the_content', $content);

        $this->assertNotEquals($content, $filtered);
        $this->assertStringContainsString('meteoprogData_777', $filtered);
    }

    /**
     * Test that the {meteoprog_informer} placeholder (without ID)
     * is replaced using the default informer ID.
     */
    public function test_placeholder_without_id_uses_default() {
        update_option('meteoprog_default_informer_id', 'bar');
        Meteoprog_Informers_Frontend::flush_cached_default_id();

        $content  = 'Header {meteoprog_informer} Footer';
        $filtered = apply_filters('the_content', $content);

        $this->assertStringContainsString('meteoprogData_bar', $filtered);
    }

    /**
     * Test that multiple placeholders are replaced correctly in one content string.
     */
    public function test_multiple_placeholders_replaced() {
        update_option('meteoprog_default_informer_id', 'bar');
        Meteoprog_Informers_Frontend::flush_cached_default_id();

        $content = '{meteoprog_informer_111} middle {meteoprog_informer} end {meteoprog_informer_222}';
        $filtered = apply_filters('the_content', $content);

        $this->assertStringContainsString('meteoprogData_111', $filtered);
        $this->assertStringContainsString('meteoprogData_bar', $filtered);
        $this->assertStringContainsString('meteoprogData_222', $filtered);
    }

    /**
     * Test that malformed placeholders like {meteoprog_informer_} are ignored gracefully.
     */
    public function test_malformed_placeholder_ignored() {
        $content = 'Start {meteoprog_informer_} End';
        $filtered = apply_filters('the_content', $content);

        // Expect original text or at least no fatal errors
        $this->assertStringContainsString('{meteoprog_informer_}', $filtered);
    }

    // -------------------------------------------------------------------------
    // Script enqueueing
    // -------------------------------------------------------------------------

    /**
     * Test that enqueue_loader() registers and enqueues the loader.js script
     * when build_html() is called (realistic frontend usage).
     */
    public function test_enqueue_loader_registers_script() {
        global $wp_scripts;

        if ( ! $wp_scripts ) {
            wp_scripts(); // initialize global scripts registry
        }

        wp_dequeue_script('meteoprog-loader');

        // Explicitly call enqueue_loader() — build_html no longer does this
        $this->frontend->enqueue_loader();

        $this->assertTrue(
            wp_script_is('meteoprog-loader', 'registered'),
            'Loader script should be registered when enqueue_loader() is called.'
        );

        $this->assertTrue(
            wp_script_is('meteoprog-loader', 'enqueued'),
            'Loader script should be enqueued when enqueue_loader() is called.'
        );
    }

    /**
     * Test that enqueue_loader() does NOT register or enqueue the loader script
     * when Elementor editor mode is active.
     *
     * In this scenario, we need to ensure that:
     *  - Elementor mode is simulated as active (via a mocked is_elementor_editor()).
     *  - No previously registered scripts or hooks interfere with the test.
     *  - enqueue_loader() respects Elementor mode and exits early without registering the script.
     *
     * Implementation notes:
     *  - We remove all actions attached to `wp_enqueue_scripts` to detach the original
     *    frontend instance created in prepare_environment().
     *  - We reset the global `$wp_scripts` registry to avoid false positives caused
     *    by scripts registered in previous tests. This mirrors the approach used
     *    in the WordPress Core test suite.
     *  - We use a mock object built without calling the original constructor to
     *    avoid triggering hook registrations.
     */
    public function test_enqueue_loader_skips_in_elementor_mode() {
        // Clean up any hooks and previously registered scripts
        remove_all_actions('wp_enqueue_scripts');
        $GLOBALS['wp_scripts'] = null; // Reset the global scripts registry

        // Build a mock WITHOUT calling the original constructor
        $frontendMock = $this->getMockBuilder(Meteoprog_Informers_Frontend::class)
            ->disableOriginalConstructor()
            ->setMethods(['is_elementor_editor'])
            ->getMock();

        // Inject API dependency manually to avoid undefined property notices
        $ref = new ReflectionClass(Meteoprog_Informers_Frontend::class);
        $prop = $ref->getProperty('api');
        $prop->setAccessible(true);
        $prop->setValue($frontendMock, $this->api);

        // Simulate Elementor editor mode
        $frontendMock->method('is_elementor_editor')->willReturn(true);

        // Execute the method under test
        $frontendMock->enqueue_loader();

        // Verify that no script was registered or enqueued
        $this->assertFalse(
            wp_script_is('meteoprog-loader', 'registered'),
            'Loader script should not be registered in Elementor editor mode.'
        );
        $this->assertFalse(
            wp_script_is('meteoprog-loader', 'enqueued'),
            'Loader script should not be enqueued in Elementor editor mode.'
        );
    }

    // -------------------------------------------------------------------------
    // Global template function tests
    // -------------------------------------------------------------------------

    /**
     * Test that the global function meteoprog_informer() works and uses the frontend instance.
     */
    public function test_global_function_meteoprog_informer() {
        $output = meteoprog_informer('abc');
        $this->assertStringContainsString('meteoprogData_abc', $output);
    }

    /**
     * Test that the shortcode is properly registered in the global shortcode registry.
     */
    public function test_shortcode_registered() {
        $this->assertArrayHasKey(
            'meteoprog_informer',
            $GLOBALS['shortcode_tags'],
            'Shortcode meteoprog_informer not registered'
        );
    }

    /**
     * Test that the_content filter is registered for placeholder replacement.
     */
    public function test_placeholder_filter_registered() {
        $this->assertNotFalse(
            has_filter('the_content', [$this->frontend, 'replace_placeholders']),
            'the_content filter not registered for placeholder replacement'
        );
    }

    // -------------------------------------------------------------------------
    // HTML rendering
    // -------------------------------------------------------------------------

    /**
     * Test build_html() output when the loader flag is set to true.
     * It should contain both the data placeholder and the loader script.
     */
    public function test_build_html_with_loader() {
        $output = $this->frontend->build_html('xyz');
        $this->assertStringContainsString('meteoprogData_xyz', $output);
    }

        /**
     * Test that print_data_layer() outputs the expected JavaScript block
     * with all queued informer IDs.
     */
    public function test_print_data_layer_outputs_script() {
        // Queue some IDs by rendering informer HTML
        $this->frontend->build_html('aaa');
        $this->frontend->build_html('bbb');

        ob_start();
        $this->frontend->print_data_layer();
        $output = ob_get_clean();

        $this->assertStringContainsString('window.meteoprogDataLayer', $output);
        $this->assertStringContainsString('aaa', $output);
        $this->assertStringContainsString('bbb', $output);
    }

    /**
     * Test that calling meteoprog_informer() without an ID
     * and without a saved default returns an HTML comment.
     */
    public function test_global_function_without_id_returns_comment() {
        update_option('meteoprog_default_informer_id', '');
        $output = meteoprog_informer();
        $this->assertStringContainsString('no informer ID', $output);
    }

    // -------------------------------------------------------------------------
    // Template function registration
    // -------------------------------------------------------------------------

    /**
     * Test that register_template_function() registers the global helper function meteoprog_informer().
     */
    public function test_register_template_function_creates_global_function() {
        // Force execution of the registration method
        $this->frontend->register_template_function();
        $this->assertTrue(function_exists('meteoprog_informer'));
    }

    /**
     * Test that calling meteoprog_informer() without a global instance
     * returns a "no instance" message.
     */
    public function test_global_function_without_instance() {
        unset($GLOBALS['meteoprog_weather_informers_instance']);
        $output = meteoprog_informer('abc');
        $this->assertStringContainsString('no instance', $output);
    }

    /**
     * Test resource hints behavior for CDN preconnect.
     */
    public function test_add_resource_hints() {

        $urls = $this->frontend->add_resource_hints(array(), 'preconnect');
        $this->assertNotContains(
            'https://cdn.meteoprog.net',
            $urls,
            'Preconnect hint should not be added when no informers are queued.'
        );

        $this->frontend->build_html('abc123'); 
        $urls = $this->frontend->add_resource_hints(array(), 'preconnect');
        $this->assertContains(
            'https://cdn.meteoprog.net',
            $urls,
            'Preconnect hint should be added when informers are queued.'
        );

        set_current_screen('dashboard');
        $urls_admin = $this->frontend->add_resource_hints(array(), 'preconnect');
        $this->assertNotContains(
            'https://cdn.meteoprog.net',
            $urls_admin,
            'Preconnect hint should not be added in admin screens.'
        );
    }

    /**
     * Test that external loader URL and version can be filtered
     * via 'meteoprog_loader_url' and 'meteoprog_loader_version' filters.
     */
    public function test_loader_url_and_version_filters() {
        // Reset global scripts for a clean state.
        $GLOBALS['wp_scripts'] = null;
        wp_scripts();

        add_filter( 'meteoprog_loader_url', function( $url ) {
            return 'https://example.com/custom-loader.js';
        } );

        add_filter( 'meteoprog_loader_version', function( $ver ) {
            return '9.9.9';
        } );

        $this->frontend->enqueue_loader();

        $data = wp_scripts()->get_data( 'meteoprog-loader', 'data' );

        $this->assertNotEmpty(
            $data,
            'Localized data for meteoprog-loader should not be empty.'
        );

        // Escape expected URL the same way wp_localize_script does (via json_encode).
        $expected_json = json_encode([
            'url'     => 'https://example.com/custom-loader.js',
            'version' => '9.9.9',
        ]);

        $this->assertStringContainsString(
            $expected_json,
            $data,
            'Filtered loader URL and version should appear in localized script data.'
        );
    }

    /**
     * Ensure enqueue_loader() is idempotent and does not enqueue twice.
     */
    public function test_enqueue_loader_idempotent() {
        $this->frontend->enqueue_loader();
        $data_first = wp_scripts()->get_data( 'meteoprog-loader', 'data' );

        // Call again — should not change data or register twice
        $this->frontend->enqueue_loader();
        $data_second = wp_scripts()->get_data( 'meteoprog-loader', 'data' );

        $this->assertSame(
            $data_first,
            $data_second,
            'enqueue_loader() should be idempotent and not duplicate localization or enqueue.'
        );
    }
}
