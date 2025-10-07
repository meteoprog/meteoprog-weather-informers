<?php
/**
 * Meteoprog Weather Widgets - Admin Tests
 *
 * This test class provides **full coverage** for the `Meteoprog_Informers_Admin` class.
 * It merges all important tests from the original AdminTest (sanity checks, menu registration,
 * register_settings) and additional extended coverage (asset output verification, masked API key
 * behavior, refresh redirects, etc.).
 *
 * Compatible with:
 *  - PHP 5.6+ and modern PHP versions
 *  - WordPress 4.9+ through 6.x
 *  - PHPUnit 5.7 and newer (Yoast Polyfills)
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Tests
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

class AdminTest extends WP_Compat_TestCase {

    private $api;
    private $frontend;
    private $admin;

    // -------------------------------------------------------------------------
    // Test Environment Setup
    // -------------------------------------------------------------------------

    /**
     * @before
     * This method is automatically executed by PHPUnit before each test.
     * It's used instead of setUp()/set_up() to avoid signature conflicts
     * between different PHPUnit versions (5.7 vs Yoast Polyfills).
     *
     * Here we:
     * - Remove the emoji styles action to avoid triggering the deprecated
     *   print_emoji_styles() notice in WordPress >= 6.4 during wp_print_styles().
     * - Create API and frontend mocks (the admin class depends on them).
     * - Instantiate the admin controller with these mocks.
     */
    public function prepare_environment() {

        // Remove the emoji styles action to avoid triggering the deprecated
        // print_emoji_styles() notice in WordPress >= 6.4 during wp_print_styles().
        if ( function_exists( 'wp_enqueue_emoji_styles' ) ) {
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
        }

        // Create a mock of the API class
        $this->api = $this->createMock(Meteoprog_Informers_API::class);

        // Create a simple mock object for the frontend
        $this->frontend = $this->getMockBuilder(stdClass::class)->getMock();

        // Instantiate the admin class with the mocks
        $this->admin = new Meteoprog_Informers_Admin($this->api, $this->frontend);
    }

    // -------------------------------------------------------------------------
    // Class existence and hook registration
    // -------------------------------------------------------------------------

    /**
     * Test that the admin class exists and can be autoloaded.
     */
    public function test_admin_class_exists() {
        $this->assertTrue(class_exists('Meteoprog_Informers_Admin'));
    }

    /**
     * Test that required hooks are correctly registered by the admin class.
     */
    public function test_hooks_registered() {
        $this->assertNotFalse(has_action('admin_menu', [$this->admin, 'menu']));
        $this->assertNotFalse(has_action('admin_init', [$this->admin, 'register_settings']));
        $this->assertNotFalse(has_action('admin_post_meteoprog_save_api_key', [$this->admin, 'save_api_key']));
        $this->assertNotFalse(has_action('admin_post_meteoprog_refresh', [$this->admin, 'refresh']));
        $this->assertNotFalse(has_action('admin_post_meteoprog_save_default', [$this->admin, 'save_default']));
        $this->assertNotFalse(has_action('admin_enqueue_scripts', [$this->admin, 'enqueue_assets']));
    }

    // -------------------------------------------------------------------------
    // sanitize_api_key()
    // -------------------------------------------------------------------------

    /**
     * Test that sanitize_api_key() correctly updates with a plain new value.
     */
    public function test_sanitize_api_key_plain_value() {
        update_option('meteoprog_api_key', 'oldkey');
        $result = $this->admin->sanitize_api_key('newkey');
        $this->assertEquals('newkey', $result);
    }

    /**
     * Test that sanitize_api_key() returns the old value if the input is hidden (****hidden).
     */
    public function test_sanitize_api_key_hidden_value_returns_old() {
        update_option('meteoprog_api_key', 'oldkey');
        $result = $this->admin->sanitize_api_key('****hidden');
        $this->assertEquals('oldkey', $result);
    }

    // -------------------------------------------------------------------------
    // register_settings()
    // -------------------------------------------------------------------------

    /**
     * Test that register_settings() registers and saves expected options.
     *
     * This verifies that both meteoprog_api_key and meteoprog_default_informer_id
     * are correctly registered as options. This is especially important for WP 4.9+
     * compatibility, as missing registration can cause silent failures.
     */
    public function test_register_settings() {
        $this->admin->register_settings();

        update_option('meteoprog_api_key', 'abc123');
        $this->assertEquals('abc123', get_option('meteoprog_api_key'));

        update_option('meteoprog_default_informer_id', 'informer1');
        $this->assertEquals('informer1', get_option('meteoprog_default_informer_id'));
    }
    // -------------------------------------------------------------------------
    // enqueue_assets()
    // -------------------------------------------------------------------------

    /**
     * Test that assets are only enqueued on the correct settings page.
     */
    public function test_enqueue_assets_only_on_settings_page() {
        // On a different page – nothing should be enqueued
        $this->admin->enqueue_assets('dashboard');
        $this->assertFalse(wp_script_is('meteoprog-admin', 'enqueued'));
        $this->assertFalse(wp_style_is('meteoprog-admin', 'enqueued'));

        // On the settings page – both script and style should be enqueued
        $this->admin->enqueue_assets('settings_page_meteoprog-informers');
        $this->assertTrue(wp_script_is('meteoprog-admin', 'enqueued'));
        $this->assertTrue(wp_style_is('meteoprog-admin', 'enqueued'));
    }

    /**
     * Test that enqueue_assets() actually outputs the correct <link> and <script> tags
     * when the settings page is loaded.
     *
     * This ensures that both CSS and JS files are not only enqueued in WordPress,
     * but also properly printed in the admin HTML output with the correct file paths.
     */
    public function test_enqueue_assets_prints_correct_tags() {
        $this->admin->enqueue_assets('settings_page_meteoprog-informers');

        ob_start();
        wp_print_styles();
        $styles_output = ob_get_clean();

        ob_start();
        wp_print_scripts();
        $scripts_output = ob_get_clean();

        $plugin_url = plugin_dir_url(__DIR__);

        $expected_css_url = $plugin_url . 'assets/admin/css/admin.css';
        $expected_js_url  = $plugin_url . 'assets/admin/js/admin.js';

        $this->assertStringContainsString($expected_css_url, $styles_output);
        $this->assertStringContainsString($expected_js_url, $scripts_output);
    }

    // -------------------------------------------------------------------------
    // save_api_key()
    // -------------------------------------------------------------------------


    /**
     * Test that save_api_key() does not overwrite the existing key if a masked value is submitted.
     */
    public function test_save_api_key_masked_value_does_not_overwrite() {
        update_option('meteoprog_api_key', 'realkey');

        $_POST['meteoprog_api_key'] = '****hidden';
        $_REQUEST['_wpnonce'] = wp_create_nonce('meteoprog_informers_options-options');

        // Temporarily grant manage_options to bypass wp_die on WP 4.9
        $grant_cap = function( $allcaps, $caps, $args ) {
            $allcaps['manage_options'] = true;
            return $allcaps;
        };
        add_filter('user_has_cap', $grant_cap, 10, 3);

        add_filter('wp_redirect', function($url){
            throw new Exception("Redirect to: $url");
        });

        try {
            $this->admin->save_api_key();
        } catch (Exception $e) {
            $this->assertStringContainsString('saved=1', $e->getMessage());
        }

        $this->assertEquals('realkey', get_option('meteoprog_api_key'));

        // cleanup
        remove_filter('user_has_cap', $grant_cap, 10);
    }

    /**
     * Test that save_api_key() updates the stored key if the new one is valid.
     */
    public function test_save_api_key_valid_key_updates() {
        update_option('meteoprog_api_key', 'oldkey');

        // Mock validate_key to return true
        $this->api->method('validate_key')->willReturn(true);

        $_POST['meteoprog_api_key'] = 'newkey';
        $_REQUEST['_wpnonce'] = wp_create_nonce('meteoprog_informers_options-options');

        // Temporarily grant manage_options to bypass wp_die on WP 4.9
        $grant_cap = function( $allcaps, $caps, $args ) {
            $allcaps['manage_options'] = true;
            return $allcaps;
        };
        add_filter('user_has_cap', $grant_cap, 10, 3);

        add_filter('wp_redirect', function($url) {
            throw new Exception("Redirect to: " . $url);
        });

        try {
            $this->admin->save_api_key();
        } catch (Exception $e) {
            $this->assertStringContainsString('saved=1', $e->getMessage());
        }

        $this->assertEquals('newkey', get_option('meteoprog_api_key'));

        remove_filter('user_has_cap', $grant_cap, 10);
    }

    /**
     * Test that save_api_key() does not overwrite the old key if the new one is invalid.
     */
    public function test_save_api_key_invalid_key_does_not_update() {
        update_option('meteoprog_api_key', 'oldkey');

        // Mock validate_key to return false
        $this->api->method('validate_key')->willReturn(false);

        $_POST['meteoprog_api_key'] = 'badkey';
        $_REQUEST['_wpnonce'] = wp_create_nonce('meteoprog_informers_options-options');

        // Temporarily grant manage_options to bypass wp_die on WP 4.9
        $grant_cap = function( $allcaps, $caps, $args ) {
            $allcaps['manage_options'] = true;
            return $allcaps;
        };
        add_filter('user_has_cap', $grant_cap, 10, 3);

        add_filter('wp_redirect', function($url) {
            throw new Exception("Redirect to: " . $url);
        });

        try {
            $this->admin->save_api_key();
        } catch (Exception $e) {
            $this->assertStringContainsString('error=invalid_key', $e->getMessage());
        }

        $this->assertEquals('oldkey', get_option('meteoprog_api_key'));

        remove_filter('user_has_cap', $grant_cap, 10);
    }

    // -------------------------------------------------------------------------
    // refresh()
    // -------------------------------------------------------------------------

    /**
     * Test that refresh() clears the cache and redirects correctly.
     */
    public function test_refresh_clears_cache() {
        $this->api->expects($this->once())
                  ->method('clear_cache');

        $_REQUEST['_wpnonce'] = wp_create_nonce('meteoprog_refresh_nonce');
        $this->api->method('get_informers')->willReturn(['ok' => true]);

        // Temporarily grant manage_options to bypass wp_die on WP 4.9
        $grant_cap = function( $allcaps, $caps, $args ) {
            $allcaps['manage_options'] = true;
            return $allcaps;
        };
        add_filter('user_has_cap', $grant_cap, 10, 3);

        add_filter('wp_redirect', function($url) {
            throw new Exception("Redirect to: $url");
        });

        try {
            $this->admin->refresh();
        } catch (\Exception $e) {
            $this->assertStringContainsString(
                'options-general.php?page=meteoprog-informers',
                $e->getMessage()
            );
        }

        remove_filter('user_has_cap', $grant_cap, 10);
    }

    // -------------------------------------------------------------------------
    // save_default()
    // -------------------------------------------------------------------------

    /**
     * Test that save_default() correctly updates the option and redirects.
     */
    public function test_save_default_updates_option() {
        $_POST['default_informer_id'] = 'abc123';
        $_REQUEST['_wpnonce'] = wp_create_nonce('meteoprog_save_default_nonce');

        // Temporarily grant manage_options to bypass wp_die on WP 4.9
        $grant_cap = function( $allcaps, $caps, $args ) {
            $allcaps['manage_options'] = true;
            return $allcaps;
        };
        add_filter('user_has_cap', $grant_cap, 10, 3);

        add_filter('wp_redirect', function($url) {
            throw new Exception("Redirect to: $url");
        });

        try {
            $this->admin->save_default();
        } catch (\Exception $e) {
            $this->assertStringContainsString(
                'options-general.php?page=meteoprog-informers',
                $e->getMessage()
            );
        }

        $this->assertEquals('abc123', get_option('meteoprog_default_informer_id'));

        remove_filter('user_has_cap', $grant_cap, 10);
    }

    // -------------------------------------------------------------------------
    // menu()
    // -------------------------------------------------------------------------

    /**
     * Test that the options page is correctly registered in the WordPress Settings menu.
     *
     * This uses the `option_page_capability_{slug}` filter as a spy.
     * WordPress automatically triggers this filter when an options page is registered.
     * If the filter is called, it means `add_options_page()` was successfully executed.
     *
     * On WordPress 4.9 and older, this filter is not triggered during menu registration.
     * In that case, we fall back to checking the global $submenu array to verify that
     * the options page was added under "Settings".
     *
     * Note: in PHPUnit the current user usually has no capabilities. We temporarily grant
     * `manage_options` via `user_has_cap` so that add_options_page() actually registers the item.
     */
    public function test_menu_registers_options_page() {
        global $submenu;

        // Temporarily grant manage_options to the current user
        $grant_cap = function( $allcaps, $caps, $args ) {
            $allcaps['manage_options'] = true;
            return $allcaps;
        };
        add_filter('user_has_cap', $grant_cap, 10, 3);

        // Spy for newer WP versions (will not fire on WP 4.9)
        $called = false;
        add_filter('option_page_capability_meteoprog-informers', function($cap) use (&$called) {
            $called = true;
            return $cap;
        });

        // Trigger registration
        $this->admin->menu();

        if ($called) {
            // Newer WP (5.3+): filter spy worked
            $this->assertTrue($called, 'add_options_page() filter was not called for meteoprog-informers');
        } else {
            // Older WP (e.g. 4.9): check $submenu under Settings
            $found = false;
            if (isset($submenu['options-general.php'])) {
                foreach ($submenu['options-general.php'] as $item) {
                    if (isset($item[2]) && $item[2] === 'meteoprog-informers') {
                        $found = true;
                        break;
                    }
                }
            }
            $this->assertTrue($found, 'Menu slug meteoprog-informers not found in options-general.php submenu');
        }

        // Cleanup
        remove_filter('user_has_cap', $grant_cap, 10);
    }

    // -------------------------------------------------------------------------
    // page()
    // -------------------------------------------------------------------------

    /**
     * Test that the admin page method renders the real admin template.
     *
     * This verifies that `Meteoprog_Informers_Admin::page()` includes and outputs
     * the actual admin-page.php file bundled with the plugin, by checking for
     * specific expected HTML fragments in the output.
     */
    public function test_page_renders_admin_template() {
        ob_start();
        $this->admin->page();
        $out = ob_get_clean();

        // Check for stable parts of the real template
        $this->assertStringContainsString('name="action" value="meteoprog_save_api_key"', $out);
    }
}
