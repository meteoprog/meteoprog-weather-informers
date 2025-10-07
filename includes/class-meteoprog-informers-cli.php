<?php
/**
 * WP-CLI Commands for Meteoprog Weather Widgets.
 *
 * Registers custom WP-CLI commands for managing Meteoprog Informers:
 * - set/get API key
 * - set/get default informer
 * - refresh or clear cache
 *
 * Example usage:
 *   wp meteoprog-weather-informers set-key YOUR_API_KEY
 *
 * Compatible with PHP 5.6+ and WP-CLI.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage CLI
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Manage Meteoprog Weather Informers from the command line.
	 */
	class Meteoprog_Informers_CLI extends WP_CLI_Command {

		/**
		 * Option key for storing API key.
		 *
		 * @var string
		 */
		private $opt_api_key = 'meteoprog_api_key';

		/**
		 * Option key for storing default informer ID.
		 *
		 * @var string
		 */
		private $opt_default_id = 'meteoprog_default_informer_id';

		/**
		 * Set API key.
		 *
		 * ## OPTIONS
		 *
		 * <key>
		 * : The API key for Meteoprog Informers.
		 *
		 * ## EXAMPLES
		 *
		 *     wp meteoprog-weather-informers set-key 1234567890abcdef
		 *
		 * @subcommand set-key
		 *
		 * @param array $args Command arguments.
		 */
		public function set_key( $args ) {
			$key = sanitize_text_field( isset( $args[0] ) ? $args[0] : '' );
			if ( ! $key ) {
				WP_CLI::error( 'API key is required.' );
			}
			update_option( $this->opt_api_key, $key );
			WP_CLI::success( 'API key saved: ' . meteoprog_mask_string( $key ) );
		}

		/**
		 * Get current API key (masked).
		 *
		 * ## EXAMPLES
		 *
		 *     wp meteoprog-weather-informers get-key
		 *
		 * @subcommand get-key
		 */
		public function get_key() {
			$key = get_option( $this->opt_api_key, '' );
			if ( ! $key ) {
				WP_CLI::warning( 'API key is not set.' );
				return;
			}
			WP_CLI::line( 'Current API key: ' . meteoprog_mask_string( $key ) );
		}

		/**
		 * Set default informer ID.
		 *
		 * ## OPTIONS
		 *
		 * <id>
		 * : The informer ID to use as default.
		 *
		 * ## EXAMPLES
		 *
		 *     wp meteoprog-weather-informers set-default 123e4567-e89b-12d3-a456-426614174000
		 *
		 * @subcommand set-default
		 *
		 * @param array $args Command arguments.
		 */
		public function set_default( $args ) {
			$id = sanitize_text_field( isset( $args[0] ) ? $args[0] : '' );
			if ( ! $id ) {
				WP_CLI::error( 'Informer ID is required.' );
			}
			update_option( $this->opt_default_id, $id );
			WP_CLI::success( "Default informer set to: $id" );
		}

		/**
		 * Get default informer ID.
		 *
		 * ## EXAMPLES
		 *
		 *     wp meteoprog-weather-informers get-default
		 *
		 * @subcommand get-default
		 */
		public function get_default() {
			$id = get_option( $this->opt_default_id, '' );
			if ( ! $id ) {
				WP_CLI::warning( 'No default informer set.' );
				return;
			}
			WP_CLI::line( "Default informer ID: $id" );
		}

		/**
		 * Refresh informer list (clear cache and re-fetch).
		 *
		 * ## EXAMPLES
		 *
		 *     wp meteoprog-weather-informers refresh
		 *
		 * @subcommand refresh
		 */
		public function refresh() {
			meteoprog_clear_cache();
			$api = new Meteoprog_Informers_API();
			$api->get_informers(); // Force reload.
			WP_CLI::success( 'Informer list refreshed.' );
		}

		/**
		 * Clear cache only.
		 *
		 * ## EXAMPLES
		 *
		 *     wp meteoprog-weather-informers clear-cache
		 *
		 * @subcommand clear-cache
		 */
		public function clear_cache() {
			meteoprog_clear_cache();
			WP_CLI::success( 'Cache cleared.' );
		}
	}

	// Register CLI command for Meteoprog Weather Informers.
	WP_CLI::add_command( 'meteoprog-weather-informers', 'Meteoprog_Informers_CLI' );
}
