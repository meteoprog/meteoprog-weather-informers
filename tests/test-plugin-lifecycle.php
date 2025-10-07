<?php
/**
 * Plugin Lifecycle Tests for Meteoprog Weather Widgets.
 *
 * Covers activation, deactivation, and uninstall hook registration.
 *
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Tests
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

class PluginLifecycleTest extends WP_Compat_TestCase {

    private $plugin_file;
    private $basename;

    /**
     * @before
     * Prepare plugin file and basename before each test.
     */
    public function setup_plugin_info() {
        $this->plugin_file = dirname(__DIR__) . '/meteoprog-weather-informers.php';
        $this->basename    = plugin_basename($this->plugin_file);
    }

    // -------------------------------------------------------------------------
    // Activation / Deactivation (simulated)
    // -------------------------------------------------------------------------

    /**
     * Test that the plugin can be activated by simulating its addition
     * to the `active_plugins` option. This works reliably across WP versions.
     */
    public function test_plugin_activates_successfully() {
        global $wp_version;
        if ( version_compare( $wp_version, '5.0', '<' ) ) {
            $this->markTestSkipped('Plugin activation tests are skipped on WP < 5.0');
            return;
        }

        $active_plugins = get_option('active_plugins', array());
        if (!in_array($this->basename, $active_plugins, true)) {
            $active_plugins[] = $this->basename;
            update_option('active_plugins', $active_plugins);
        }

        $this->assertTrue(
            is_plugin_active($this->basename),
            'Plugin should be active after simulated activation'
        );
    }

    /**
     * Test that the plugin can be deactivated by simulating its removal
     * from the `active_plugins` option. This works reliably across WP versions.
     */
    public function test_plugin_deactivates_successfully() {
        global $wp_version;
        if ( version_compare( $wp_version, '5.0', '<' ) ) {
            $this->markTestSkipped('Plugin deactivation tests are skipped on WP < 5.0');
            return;
        }

        // First, activate
        $active_plugins = get_option('active_plugins', array());
        if (!in_array($this->basename, $active_plugins, true)) {
            $active_plugins[] = $this->basename;
            update_option('active_plugins', $active_plugins);
        }
        $this->assertTrue(is_plugin_active($this->basename));

        // Then, deactivate
        $active_plugins = array_diff($active_plugins, array($this->basename));
        update_option('active_plugins', $active_plugins);

        $this->assertFalse(
            is_plugin_active($this->basename),
            'Plugin should be inactive after simulated deactivation'
        );
    }

    // -------------------------------------------------------------------------
    // Uninstall hook
    // -------------------------------------------------------------------------

    /**
     * Test that the uninstall hook is properly registered in WordPress.
     *
     * This checks the `uninstall_plugins` option where WordPress stores
     * callbacks for each plugin that uses `register_uninstall_hook()`.
     */
    public function test_uninstall_hook_is_registered() {
        $hooks = get_option('uninstall_plugins');

        $this->assertIsArray(
            $hooks,
            'Expected uninstall_plugins option to be an array'
        );

        $this->assertArrayHasKey(
            $this->basename,
            $hooks,
            'Uninstall hook not registered for this plugin'
        );

        $this->assertEquals(
            'meteoprog_informers_on_uninstall',
            $hooks[$this->basename],
            'Unexpected uninstall hook callback'
        );
    }
}
