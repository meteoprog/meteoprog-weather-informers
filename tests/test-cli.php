<?php
/**
 * CLI Tests for Meteoprog Weather Widgets.
 *
 * Covers WP-CLI command registration, class inheritance,
 * and availability of subcommands for API key and informer management.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Tests
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

class CliTest extends WP_Compat_TestCase {

    // -------------------------------------------------------------------------
    // Basic CLI class tests
    // -------------------------------------------------------------------------

    /**
     * Test that the CLI class exists when WP-CLI is available.
     * Otherwise, the test is skipped.
     */
    public function test_cli_class_exists() {
        if (defined('WP_CLI') && WP_CLI) {
            $this->assertTrue(
                class_exists('Meteoprog_Informers_CLI'),
                'CLI class not found'
            );
        } else {
            $this->markTestSkipped('WP-CLI not available in this test run');
        }
    }

    /**
     * Test that the CLI class extends WP_CLI_Command.
     */
    public function test_cli_class_extends_wp_cli_command() {
        if (defined('WP_CLI') && WP_CLI) {
            $this->assertTrue(
                is_subclass_of('Meteoprog_Informers_CLI', 'WP_CLI_Command'),
                'CLI class does not extend WP_CLI_Command'
            );
        } else {
            $this->markTestSkipped('WP-CLI not available in this test run');
        }
    }

    // -------------------------------------------------------------------------
    // Command registration
    // -------------------------------------------------------------------------

    /**
     * Test that the root CLI command "meteoprog-weather-informers" is registered.
     */
    public function test_cli_command_is_registered() {
        if (defined('WP_CLI') && WP_CLI) {
            $commands = \WP_CLI::get_root_commands();
            $this->assertArrayHasKey(
                'meteoprog-weather-informers',
                $commands,
                'CLI root command not registered'
            );
        } else {
            $this->markTestSkipped('WP-CLI not available in this test run');
        }
    }

    // -------------------------------------------------------------------------
    // Subcommands
    // -------------------------------------------------------------------------

    /**
     * Test that the expected subcommand methods exist in the CLI class.
     */
    public function test_cli_subcommands_exist() {
        if (defined('WP_CLI') && WP_CLI) {
            $methods = get_class_methods('Meteoprog_Informers_CLI');

            $expected = [
                'set_key',
                'get_key',
                'set_default',
                'get_default',
                'refresh',
                'clear_cache',
            ];

            foreach ($expected as $sub) {
                $this->assertContains(
                    $sub,
                    $methods,
                    "Subcommand method '$sub' not found in Meteoprog_Informers_CLI"
                );
            }
        } else {
            $this->markTestSkipped('WP-CLI not available in this test run');
        }
    }
}
