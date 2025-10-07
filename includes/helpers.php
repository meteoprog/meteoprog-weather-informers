<?php
/**
 * Helper Functions for Meteoprog Weather Informers.
 *
 * Contains small reusable utility functions used across the plugin:
 * string masking, cache clearing, editor detection, and URL parsing.
 *
 * Compatible with PHP 5.6+ and WordPress 4.9+.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Core
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'meteoprog_mask_string' ) ) {
	/**
	 * Mask part of a string with a fixed number of mask characters.
	 *
	 * Original: 550e8400-e29b-41d4-a716-446655440000
	 * Masked:   550e********400000
	 *
	 * @param string $key        Original string.
	 * @param int    $from_start Number of visible characters at the start.
	 * @param int    $before_end Number of visible characters at the end.
	 * @param int    $between    Number of mask characters to insert between.
	 * @param string $mask       Mask character.
	 *
	 * @return string Masked string.
	 */
	function meteoprog_mask_string( $key, $from_start = 4, $before_end = 6, $between = 8, $mask = '*' ) {
		$key = (string) $key;
		$len = strlen( $key );

		// If string is too short, return as is.
		if ( $len <= ( $from_start + $before_end ) ) {
			return $key;
		}

		$start  = substr( $key, 0, $from_start );
		$end    = substr( $key, -$before_end );
		$masked = str_repeat( $mask, $between );

		return $start . $masked . $end;
	}
}

if ( ! function_exists( 'meteoprog_clear_cache' ) ) {
	/**
	 * Clear informers cache globally.
	 *
	 * Wrapper around Meteoprog_Informers_API::clear_cache().
	 *
	 * @return void
	 */
	function meteoprog_clear_cache() {
		if ( class_exists( 'Meteoprog_Informers_API' ) ) {
			$api = new Meteoprog_Informers_API();
			$api->clear_cache();
		}
	}
}

if ( ! function_exists( 'meteoprog_is_elementor_editor_mode' ) ) {
	/**
	 * Detect if Elementor editor is currently active.
	 *
	 * Supports old and new Elementor versions:
	 * - Modern (3.5+): editor->is_edit_mode().
	 * - Mid (3.0+): ELEMENTOR_EDITOR constant.
	 * - Legacy (2.x): elementor-preview param.
	 * - Classic: action=elementor (post.php?post=X&action=elementor).
	 * - Fallback: preview->is_preview_mode().
	 *
	 * @return bool
	 */
	function meteoprog_is_elementor_editor_mode() {

		// Modern check (Elementor 3.5+).
		if (
			class_exists( '\Elementor\Plugin' ) &&
			\Elementor\Plugin::$instance &&
			method_exists( \Elementor\Plugin::$instance->editor, 'is_edit_mode' ) &&
			\Elementor\Plugin::$instance->editor->is_edit_mode()
		) {
			return true;
		}

		// Mid versions (Elementor 3.0+).
		if ( defined( 'ELEMENTOR_EDITOR' ) && ELEMENTOR_EDITOR ) {
			return true;
		}

		// Legacy iframe mode (?elementor-preview).
		if ( isset( $_GET['elementor-preview'] ) && is_admin() ) {
			return true;
		}

		// Classic editor URL (post.php?post=X&action=elementor)
		if ( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] && is_admin() ) {
			return true;
		}

		// Fallback preview mode.
		if (
			class_exists( '\Elementor\Plugin' ) &&
			\Elementor\Plugin::$instance &&
			method_exists( \Elementor\Plugin::$instance->preview, 'is_preview_mode' ) &&
			\Elementor\Plugin::$instance->preview->is_preview_mode()
		) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'meteoprog_host_from_url' ) ) {
	/**
	 * Extract hostname from a URL or plain domain string.
	 *
	 * Uses wp_parse_url() for safety and falls back to regex for bare domains.
	 * Compatible with PHP 5.6+.
	 *
	 * @param string $url URL or domain string.
	 * @return string Hostname in lowercase, or empty string on failure.
	 */
	function meteoprog_host_from_url( $url ) {
		if ( ! $url ) {
			return '';
		}

		// Use WordPress-safe parser.
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! empty( $host ) ) {
			return strtolower( $host );
		}

		// Fallback for plain domain without scheme.
		$url = preg_replace( '#^https?://#i', '', $url );
		$url = preg_replace( '#/.*$#', '', $url );
		return strtolower( $url );
	}
}
