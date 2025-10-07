<?php
/**
 * Admin Settings Controller for Meteoprog Weather Widgets.
 *
 * Renders and handles the main plugin admin interface: API key management,
 * informer list refresh, default informer selection, and asset loading.
 *
 * Compatible with PHP 5.6+ and WordPress 4.9+.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Admin
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Meteoprog_Informers_Admin {
	private $api;
	private $frontend;
	private $opt_api_key    = 'meteoprog_api_key';
	private $opt_default_id = 'meteoprog_default_informer_id';

	/**
	 * @param $api
	 * @param $frontend
	 */
	public function __construct( $api, $frontend ) {
		$this->api      = $api;
		$this->frontend = $frontend;

		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'admin_post_meteoprog_save_api_key', array( $this, 'save_api_key' ) );
		add_action( 'admin_post_meteoprog_refresh', array( $this, 'refresh' ) );
		add_action( 'admin_post_meteoprog_save_default', array( $this, 'save_default' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue admin CSS and JS for the Meteoprog settings page.
	 *
	 * @param $hook
	 */
	public function enqueue_assets( $hook ) {
		// Only load on our settings page (matches both "settings_page" and "options-general_page" patterns).

		if ( false === strpos( $hook, 'settings_page_meteoprog-informers' )
		&& false === strpos( $hook, 'options-general_page_meteoprog-informers' ) ) {
			return;
		}

		$base_path = plugin_dir_path( __DIR__ );
		$base_url  = plugin_dir_url( __DIR__ );

		// CSS.
		$css_file = 'assets/admin/css/admin.css';
		$css_path = $base_path . $css_file;
		wp_enqueue_style(
			'meteoprog-admin',
			$base_url . $css_file,
			array(),
			file_exists( $css_path ) ? filemtime( $css_path ) : METEOPROG_PLUGIN_VERSION
		);

		// JS.
		$js_file = 'assets/admin/js/admin.js';
		$js_path = $base_path . $js_file;

		$deps = array();
		// Only add wp-i18n if it exists (WP >= 5.0).
		if ( wp_script_is( 'wp-i18n', 'registered' ) ) {
			$deps[] = 'wp-i18n';
		}

		wp_enqueue_script(
			'meteoprog-admin',
			$base_url . $js_file,
			$deps,
			file_exists( $js_path ) ? filemtime( $js_path ) : METEOPROG_PLUGIN_VERSION,
			true
		);

		// Translations (WP >= 5.0).
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				'meteoprog-admin',
				'meteoprog-weather-informers',
				$base_path . 'languages'
			);
		}
	}

	/**
	 * Add options page to the admin menu.
	 */
	public function menu() {
		add_options_page(
			__( 'Meteoprog Widgets', 'meteoprog-weather-informers' ),
			__( 'Meteoprog Widgets', 'meteoprog-weather-informers' ),
			'manage_options',
			'meteoprog-informers',
			array( $this, 'page' )
		);
	}

	/**
	 * Register plugin settings.
	 */
	public function register_settings() {
		register_setting(
			'meteoprog_informers_options',
			$this->opt_api_key,
			array(
				'sanitize_callback' => array( $this, 'sanitize_api_key' ),
			)
		);

		register_setting( 'meteoprog_informers_options', $this->opt_default_id );
	}

	/**
	 * Save API key with validation.
	 * Does not overwrite the old key if the new one is invalid.
	 */
	public function save_api_key() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'meteoprog-weather-informers' ), 403 );
		}

		check_admin_referer( 'meteoprog_informers_options-options' );

		$old_key = get_option( $this->opt_api_key, '' );

		$new_key = isset( $_POST[ $this->opt_api_key ] )
			? sanitize_text_field( wp_unslash( $_POST[ $this->opt_api_key ] ) )
			: '';

		// Do nothing if the user submitted the masked value.
		if ( strpos( $new_key, '****' ) !== false ) {
			wp_safe_redirect( admin_url( 'options-general.php?page=meteoprog-informers&saved=1' ) );
			exit;
		}

		// Validate the new key using the API.
		if ( ! $this->api->validate_key( $new_key ) ) {
			// Keep old key, show error.
			wp_safe_redirect( admin_url( 'options-general.php?page=meteoprog-informers&error=invalid_key' ) );
			exit;
		}

		// Valid key, save it.
		update_option( $this->opt_api_key, $new_key );
		wp_safe_redirect( admin_url( 'options-general.php?page=meteoprog-informers&saved=1' ) );
		exit;
	}

	/**
	 * Sanitize API key input before saving.
	 */
	public function sanitize_api_key( $val ) {
		$old = get_option( $this->opt_api_key, '' );
		if ( strpos( $val, '****' ) !== false ) {
			return $old;
		}
		return sanitize_text_field( $val );
	}

	/**
	 * Clear cache and refresh informer list from API.
	 */
	public function refresh() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'meteoprog-weather-informers' ), 403 );
		}

		check_admin_referer( 'meteoprog_refresh_nonce' );

		$this->api->clear_cache();
		$list = $this->api->get_informers();
		if ( empty( $list ) ) {
			wp_safe_redirect( admin_url( 'options-general.php?page=meteoprog-informers&error=refresh_failed' ) );
			exit;
		}
		wp_safe_redirect( admin_url( 'options-general.php?page=meteoprog-informers&refreshed=1' ) );
		exit;
	}

	/**
	 * Save the default informer ID.
	 */
	public function save_default() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'meteoprog-weather-informers' ), 403 );
		}

		check_admin_referer( 'meteoprog_save_default_nonce' );

		$id = isset( $_POST['default_informer_id'] )
			? sanitize_text_field( wp_unslash( $_POST['default_informer_id'] ) )
			: '';

		update_option( $this->opt_default_id, $id );
		wp_safe_redirect( admin_url( 'options-general.php?page=meteoprog-informers&saved=1' ) );
		exit;
	}

	/**
	 * Render the admin settings page.
	 */
	public function page() {
		$api_key      = get_option( $this->opt_api_key, '' );
		$informers    = $this->api->get_informers();
		$default_id   = get_option( $this->opt_default_id, '' );
		$masked_key   = function_exists( 'meteoprog_mask_string' ) ? meteoprog_mask_string( $api_key ) : $api_key;
		$current_host = wp_parse_url( home_url(), PHP_URL_HOST );

		$refreshed = ! empty( $_GET['refreshed'] );
		$saved     = ! empty( $_GET['saved'] );
		$error     = ! empty( $_GET['error'] ) ? $_GET['error'] : '';

		include plugin_dir_path( __DIR__ ) . 'views/admin-page.php';
	}
}
