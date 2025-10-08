<?php
/**
 * API Controller for Meteoprog Weather Widgets.
 *
 * Handles all communication with the Meteoprog Informer API: fetching informer lists,
 * validating API keys, caching responses, and supporting local debug mode via JSON files.
 *
 * Compatible with PHP 5.6+ and WordPress 4.9+.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage API
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Meteoprog_Informers_API
 *
 * Handles communication with the Meteoprog Informer API:
 * - Fetching informer lists.
 * - Validating API keys.
 * - Caching responses.
 * - Supporting local debug mode.
 */
class Meteoprog_Informers_API {

	/**
	 * Meteoprog API endpoint URL.
	 *
	 * @var string
	 */
	private $api_url = 'https://billing.meteoprog.com/api/informers';

	/**
	 * Option key for storing API key.
	 *
	 * @var string
	 */
	private $opt_api_key = 'meteoprog_api_key';

	/**
	 * Debug mode: force load informers from hardcoded array instead of HTTP requests.
	 *
	 * @var bool
	 */
	private $debug = false;

	/**
	 * Constructor.
	 *
	 * Initializes debug mode and applies filters.
	 */
	public function __construct() {

		// Enable debug mode if METEOPROG_DEBUG is defined.
		if ( defined( 'METEOPROG_DEBUG' ) && METEOPROG_DEBUG ) {
			$this->debug = true;
		}

		// If METEOPROG_DEBUG_API_KEY is defined, force real API requests.
		if ( defined( 'METEOPROG_DEBUG_API_KEY' ) && METEOPROG_DEBUG_API_KEY ) {

			// Store the key in WP options so fetch_from_api() can use it.
			update_option( $this->opt_api_key, METEOPROG_DEBUG_API_KEY );

			// Disable debug mode to allow real HTTP requests.
			$this->debug = false;
		}

		// Allow overriding debug mode via filter if needed.
		$this->debug = apply_filters( 'meteoprog_debug_mode', $this->debug );
	}

	/**
	 * Get list of informers.
	 *
	 * Uses cache (WordPress transients) to avoid too many API requests.
	 * In debug mode it loads informers from a hardcoded array instead of calling the API.
	 *
	 * @return array List of informers.
	 */
	public function get_informers() {
		$cached = get_transient( $this->cache_key() );

		// When debug mode is enabled, ignore cache and use hardcoded array.
		if ( true === $this->debug ) {
			$cached = false;
		}

		if ( false !== $cached ) {
			return $cached;
		}

		if ( true === $this->debug ) {
			$list = $this->load_from_array();
		} else {
			$list = $this->fetch_from_api();
		}

		set_transient( $this->cache_key(), $list, 3 * MINUTE_IN_SECONDS );
		return $list;
	}

	/**
	 * Build cache key for informer list.
	 *
	 * @return string Cache key.
	 */
	private function cache_key() {
		return 'meteoprog_informers_cache_' . md5( $this->get_api_key() );
	}

	/**
	 * Clear informer list cache.
	 */
	public function clear_cache() {
		delete_transient( $this->cache_key() );
	}

	/**
	 * Load informers from hardcoded array (used in debug mode).
	 *
	 * @return array
	 */
	private function load_from_array() {
		return array(
			array(
				'created_at'  => '2025-09-30T19:07:37.000000Z',
				'informer_id' => '11111111-1111-1111-aa3a-5bb2d44d4fd1',
				'domain'      => 'https://www.wordpress.org',
				'active'      => 1,
			),
			array(
				'created_at'  => '2025-09-30T19:07:37.000000Z',
				'informer_id' => '22222222-2222-2222-bbf0-ee43197fdd39',
				'domain'      => 'https://localhost',
				'active'      => 1,
			),
			array(
				'created_at'  => '2025-09-30T19:07:37.000000Z',
				'informer_id' => '33333333-3333-3333-acf3-2b1c6d6f3b35',
				'domain'      => 'http://example.com',
				'active'      => 0,
			),
			array(
				'created_at'  => '2025-09-30T19:07:37.000000Z',
				'informer_id' => '44444444-4444-4444-acf3-2b1c6d6f3b35',
				'domain'      => 'https://subdomain.example.com',
				'active'      => 1,
			),
		);
	}

	/**
	 * Build custom User-Agent string for API requests.
	 * It includes plugin version for easier tracking and debugging on the API side.
	 *
	 * @return string User agent.
	 */
	private function get_user_agent() {
		static $ua = null;
		if ( null !== $ua ) {
			return $ua;
		}

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$main_file = dirname( __DIR__ ) . '/' . basename( dirname( __DIR__ ) ) . '.php';

		$version = '1.0';
		if ( file_exists( $main_file ) ) {
			$plugin_data = get_plugin_data( $main_file, false, false );
			if ( ! empty( $plugin_data['Version'] ) ) {
				$version = $plugin_data['Version'];
			}
		}

		$ua = 'MeteoprogWPPlugin/' . $version . ' (+https://meteoprog.com)';
		return $ua;
	}

	/**
	 * Fetch informers list from Meteoprog API via HTTP request.
	 *
	 * - The API key is sent via the Authorization Bearer header.
	 * - The current site domain is passed in the X-Site-Domain header.
	 *   This is used by Meteoprog for statistics (to identify which site is requesting the widgets).
	 * - The request is required to retrieve the list of available widgets (informers)
	 *   linked to the user's account at billing.meteoprog.com.
	 *
	 * @param string $override_key Optional. If provided, this key will be used instead of the saved one.
	 * @return array List of informers, or an empty array on error or invalid key.
	 */
	private function fetch_from_api( $override_key = null ) {
		$api_key = $override_key ? $override_key : $this->get_api_key();
		if ( ! $api_key ) {
			return array();
		}

		$informer_domain = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( ! $informer_domain ) {
			$informer_domain = home_url();
		}
		$informer_domain = strtolower( $informer_domain );

		$response = wp_remote_get(
			$this->api_url,
			array(
				'timeout' => 15,
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Accept'        => 'application/json',
					'X-Site-Domain' => $informer_domain,
					'User-Agent'    => $this->get_user_agent(),
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array();
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['informers'] ) && is_array( $data['informers'] ) ) {
			return $data['informers'];
		}

		if ( isset( $data[0] ) && is_array( $data[0] ) ) {
			return $data;
		}

		return array();
	}

	/**
	 * Get saved API key from WordPress options.
	 *
	 * @return string API key or empty string.
	 */
	public function get_api_key() {
		return get_option( $this->opt_api_key, '' );
	}

	/**
	 * Validate API key by attempting to fetch informers with the provided key.
	 *
	 * @param string $key API key to validate.
	 * @return bool True if valid, false otherwise.
	 */
	public function validate_key( $key ) {
		if ( true === $this->debug ) {
			$result = $this->load_from_array();
		} else {
			$result = $this->fetch_from_api( $key );
		}

		return ! empty( $result );
	}
}
