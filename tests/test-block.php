<?php
/**
 * Gutenberg Block Tests for Meteoprog Weather Widgets.
 *
 * Covers:
 * - Block registration
 * - Render callback behavior
 * - REST API endpoint
 * - Elementor mode skip
 *
 * Editor asset tests are intentionally omitted because in PHPUnit environments
 * the current screen rarely reports as a block editor, which would cause
 * false negatives.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Tests
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

class BlockTest extends WP_Compat_TestCase {

    private $frontend;
    private $api;
    private $block;

    /**
     * Reset Gutenberg block registry between tests to avoid cross-test contamination.
     *
     * @before
     */
    public function reset_block_registry() {
        if ( class_exists( 'WP_Block_Type_Registry' ) ) {
            $registry = WP_Block_Type_Registry::get_instance();
            if ( method_exists( $registry, 'get_all_registered' ) ) {
                foreach ( array_keys( $registry->get_all_registered() ) as $name ) {
                    $registry->unregister( $name );
                }
            }
        }
    }

    /**
     * Per-test environment init compatible with PHPUnit 5.7 / PHP 5.6.
     *
     * Loads Gutenberg core APIs, prepares fake assets, and sets up mocks.
     *
     * @before
     */
    public function prepare_environment() {
        global $wp_scripts, $wp_styles;
        if ( ! $wp_scripts ) { $wp_scripts = new \WP_Scripts(); }
        if ( ! $wp_styles )  { $wp_styles  = new \WP_Styles(); }

        if ( ! function_exists('register_block_type') ) {
            $this->markTestSkipped('Gutenberg not available');
        }


        // Load Gutenberg core APIs manually for test environment (WP ≥ 5.0 only)
        $blocks_file = ABSPATH . 'wp-includes/blocks.php';
        $registry_file = ABSPATH . 'wp-includes/class-wp-block-type-registry.php';

        if ( file_exists( $blocks_file ) ) {
            if ( ! function_exists( 'register_block_type' ) ) {
                require_once $blocks_file;
            }
        }

        if ( file_exists( $registry_file ) ) {
            if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
                require_once $registry_file;
            }
        }

        WP_Block_Type_Registry::get_instance();

        // Stub Elementor to avoid accidental editor mode during tests.
        if ( ! function_exists('meteoprog_is_elementor_editor_mode') ) {
            function meteoprog_is_elementor_editor_mode() { return false; }
        }

        // Create minimal fake JS/CSS assets to make filemtime() work.
        $plugin_dir = dirname(__DIR__);
        $js_path    = $plugin_dir . '/assets/block/js/block.js';
        $css_path   = $plugin_dir . '/assets/block/css/block-editor.css';
        if ( ! file_exists( $js_path ) ) {
            @mkdir( dirname( $js_path ), 0777, true );
            @file_put_contents( $js_path, '// test js' );
        }
        if ( ! file_exists( $css_path ) ) {
            @mkdir( dirname( $css_path ), 0777, true );
            @file_put_contents( $css_path, '/* test css */' );
        }

        // Mocks for frontend and API layers.
        $this->frontend = $this->getMockBuilder(stdClass::class)
                               ->setMethods(['build_html'])
                               ->getMock();
        $this->api = $this->getMockBuilder(stdClass::class)
                          ->setMethods(['get_informers'])
                          ->getMock();

        // Instantiate the block under test.
        $this->block = new Meteoprog_Informers_Block($this->frontend, $this->api);
    }

    // -------------------------------------------------------------------------
    // Block registration & rendering
    // -------------------------------------------------------------------------

    /**
     * Block should be registered in the Gutenberg registry after calling
     * register_block() directly. We do not rely on `init` in tests.
     */
    public function test_block_registration_works() {
        if ( ! function_exists('register_block_type') ) {
            $this->markTestSkipped('Gutenberg not available');
        }

        $this->block->register_block();

        $registry = WP_Block_Type_Registry::get_instance();
        $this->assertTrue(
            $registry->is_registered('meteoprog/informer'),
            'Block meteoprog/informer should be registered after calling register_block().'
        );
    }

    /**
     * Render callback should show a warning box when no ID is provided.
     */
    public function test_render_callback_no_id_shows_warning() {
        if ( ! function_exists('register_block_type') ) {
            $this->markTestSkipped('Gutenberg not available');
        }

        $this->block->register_block();
        $block = WP_Block_Type_Registry::get_instance()->get_registered('meteoprog/informer');
        $this->assertNotNull($block, 'Block should be registered');
        $this->assertNotNull($block->render_callback, 'Render callback should be set');

        $output = call_user_func($block->render_callback, []);
        $this->assertNotEmpty($output);
        $this->assertStringContainsString('No informer selected.', $output);
    }

    /**
     * Render callback should delegate to frontend when an ID is provided.
     */
    public function test_render_callback_with_id_uses_frontend() {
        if ( ! function_exists('register_block_type') ) {
            $this->markTestSkipped('Gutenberg not available');
        }

        $this->frontend->method('build_html')
                       ->with('123')
                       ->willReturn('<div>Informer 123</div>');

        $output = $this->block->render_block(['id' => '123']);
        $this->assertEquals('<div>Informer 123</div>', $output);
    }

    // -------------------------------------------------------------------------
    // REST API
    // -------------------------------------------------------------------------

    /**
     * Helper to build REST request for the informers endpoint.
     *
     * @return WP_REST_Request
     */
    private function make_informers_request() {
        return new WP_REST_Request(
            'GET',
            sprintf(
                '/%s%s',
                Meteoprog_Informers_Block::REST_NAMESPACE,
                Meteoprog_Informers_Block::REST_ROUTE_INFORMERS
            )
        );
    }

    /**
     * REST endpoint should return informer data for authorized users.
     */
    public function test_rest_endpoint_returns_data() {
        $user_id = $this->factory->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);

        $this->api->method('get_informers')->willReturn([
            ['informer_id' => 'abc', 'domain' => 'https://test.com']
        ]);

        // Register route properly on rest_api_init.
        add_action( 'rest_api_init', [ $this->block, 'rest' ] );
        do_action( 'rest_api_init' );

        $request  = $this->make_informers_request();
        $response = rest_do_request($request);

        $this->assertEquals(200, $response->get_status());
        $this->assertArrayHasKey('informer_id', $response->get_data()[0]);
    }

    /**
     * REST endpoint should require authentication.
     */
    public function test_rest_endpoint_requires_permission() {
        wp_set_current_user(0);

        // Register route properly on rest_api_init.
        add_action( 'rest_api_init', [ $this->block, 'rest' ] );
        do_action( 'rest_api_init' );

        $request  = $this->make_informers_request();
        $response = rest_do_request($request);

        $this->assertTrue(in_array($response->get_status(), [401, 403], true));
    }

    // -------------------------------------------------------------------------
    // Elementor skip mode
    // -------------------------------------------------------------------------

    /**
     * Block registration should be skipped inside Elementor editor mode.
     */
    public function test_register_block_skips_in_elementor_mode() {
        if ( ! function_exists('register_block_type') ) {
            $this->markTestSkipped('Gutenberg not available');
        }

        // Fresh registry for isolation is already ensured by @before reset_block_registry.
        $mock = $this->getMockBuilder(Meteoprog_Informers_Block::class)
            ->setConstructorArgs([$this->frontend, $this->api])
            ->setMethods(['is_elementor_editor'])
            ->getMock();

        $mock->method('is_elementor_editor')->willReturn(true);
        $mock->register_block();

        $this->assertFalse(
            WP_Block_Type_Registry::get_instance()->is_registered('meteoprog/informer'),
            'Block should not be registered in Elementor editor mode.'
        );
    }
}
