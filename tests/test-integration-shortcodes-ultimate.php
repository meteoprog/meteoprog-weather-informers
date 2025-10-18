<?php
/**
 * Shortcodes Ultimate Integration Tests for Meteoprog Weather Informers.
 *
 * Covers:
 * - Shortcode registration
 * - Frontend and admin rendering of [su_meteoprog_informer]
 * - Fallback behavior when no frontend is set
 * - su/data/shortcodes filter modifications (select vs text input)
 *
 * We explicitly re-include the integration file and run `do_action('init')`
 * in @before to mimic real WordPress bootstrapping.
 * This ensures the shortcode is properly registered for each test case,
 * even on WP 4.9 where plugin loading is not automatic in PHPUnit context.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Tests
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

class ShortcodesUltimateIntegrationTest extends WP_Compat_TestCase {

    private $frontend;
    private $apiMock;

    /**
     * @before
     * Runs before each test.
     * Sets up mocks, loads the integration file, and triggers `init`
     * to register the [su_meteoprog_informer] shortcode.
     */
    public function prepare_environment() {

        // --- Frontend mock ---

        // Mocks for frontend and API layers.
        $this->frontend = $this->getMockBuilder(Meteoprog_Informers_Frontend::class)
            ->disableOriginalConstructor()
            ->setMethods(['enqueue_loader', 'build_html'])
            ->getMock();


        $this->frontend->method('build_html')->willReturn('<div>Informer HTML</div>');
        $GLOBALS['meteoprog_weather_informers_instance'] = $this->frontend;

        // --- API mock ---
        $this->apiMock = $this->getMockBuilder(stdClass::class)
            ->setMethods(['get_informers'])
            ->getMock();

        $this->apiMock->method('get_informers')->willReturn(array(
            array(
                'informer_id' => 'abc123',
                'domain'      => 'https://example.com',
            ),
        ));
        $GLOBALS['meteoprog_weather_informers_api'] = $this->apiMock;

        // --- Load integration file and register shortcode ---
        // Use include (not require_once) to re-run for each test
        include dirname(__DIR__) . '/includes/integrations/integration-shortcodes-ultimate.php';

        // Trigger 'init' to actually register the shortcode
        do_action('init');
    }

    // -------------------------------------------------------------------------
    // Shortcode registration
    // -------------------------------------------------------------------------

    /**
     * Test that [su_meteoprog_informer] shortcode is registered.
     */
    public function test_shortcode_registered() {
        $this->assertArrayHasKey(
            'su_meteoprog_informer',
            $GLOBALS['shortcode_tags'],
            'Shortcode su_meteoprog_informer not registered'
        );
    }

    // -------------------------------------------------------------------------
    // Shortcode frontend rendering
    // -------------------------------------------------------------------------

    /**
     * Test that shortcode calls build_html() on frontend.
     */
    public function test_shortcode_renders_frontend() {
        // Switch context to frontend
        set_current_screen('front');

        $output = do_shortcode('[su_meteoprog_informer id="abc123"]');

        $this->assertStringContainsString('<div>Informer HTML</div>', $output);
    }

    /**
     * Test shortcode returns fallback message when frontend instance is missing.
     */
    public function test_shortcode_no_frontend_instance() {
        unset($GLOBALS['meteoprog_weather_informers_instance']);
        $output = do_shortcode('[su_meteoprog_informer id="abc123"]');
        $this->assertStringContainsString('<!-- no frontend instance -->', $output);
    }

    // -------------------------------------------------------------------------
    // Admin preview rendering
    // -------------------------------------------------------------------------

    /**
     * Test that shortcode renders static preview HTML in admin context.
     */
    public function test_shortcode_renders_admin_preview() {
        // Force admin context
        set_current_screen('edit-post');
        $output = do_shortcode('[su_meteoprog_informer id="abc123"]');
        $this->assertStringContainsString('Meteoprog Weather Informer', $output);
        $this->assertStringContainsString('abc123', $output);
    }

    // -------------------------------------------------------------------------
    // su/data/shortcodes filter
    // -------------------------------------------------------------------------

    /**
     * Test that su/data/shortcodes filter adds a select list when informers exist.
     */
    public function test_su_data_shortcodes_filter_with_informers() {
        $shortcodes = apply_filters('su/data/shortcodes', array());
        $this->assertArrayHasKey(
            'meteoprog_informer',
            $shortcodes,
            'Shortcodes Ultimate filter did not register meteoprog_informer'
        );

        $atts = $shortcodes['meteoprog_informer']['atts'];
        $this->assertArrayHasKey('id', $atts);
        $this->assertEquals('select', $atts['id']['type']);
    }

    /**
     * Test that su/data/shortcodes filter falls back to text input when API is missing.
     */
    public function test_su_data_shortcodes_filter_without_api() {
        unset($GLOBALS['meteoprog_weather_informers_api']);
        $shortcodes = apply_filters('su/data/shortcodes', array());

        $atts = $shortcodes['meteoprog_informer']['atts'];
        $this->assertEquals('text', $atts['id']['type']);
    }
}
