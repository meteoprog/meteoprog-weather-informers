<?php
/**
 * Uninstall & Data Removal Logic for Meteoprog Weather Informers.
 *
 * Handles plugin uninstallation and full data deletion:
 * - Registers hidden Tools submenu for data removal confirmation
 * - Provides admin page for manual data deletion
 * - Cleans up transients during plugin uninstall
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

/**
 * Hides the "Remove Meteoprog Plugin Data" submenu item from the Tools menu.
 *
 * The page remains accessible via direct URL, but it won't appear in the Tools submenu.
 * This avoids cluttering the admin menu while still providing a secure way
 * to access the removal page when needed.
 */
function meteoprog_hide_remove_data_page() {
    remove_submenu_page( 'tools.php', 'meteoprog-remove-data' );
}

/**
 * Render the data removal confirmation page.
 * This page can only be accessed by administrators via a direct URL.
 */
function meteoprog_render_remove_data_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Insufficient permissions.', 'meteoprog-weather-informers' ), '', array( 'response' => 403 ) );
    }

    if (
        isset( $_POST['meteoprog_confirm_remove'] ) &&
        isset( $_POST['meteoprog_remove_data_nonce'] ) &&
        wp_verify_nonce( $_POST['meteoprog_remove_data_nonce'], 'meteoprog_remove_data_action' )
    ) {
        meteoprog_delete_all_plugin_data();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Meteoprog Plugin Data Removed', 'meteoprog-weather-informers' ); ?></h1>
            <p><?php esc_html_e( 'All plugin settings and cached data have been successfully deleted.', 'meteoprog-weather-informers' ); ?></p>
            <p>
                <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button">
                    <?php esc_html_e( 'Back to Plugins', 'meteoprog-weather-informers' ); ?>
                </a>
            </p>
        </div>
        <?php
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Remove Meteoprog Plugin Data', 'meteoprog-weather-informers' ); ?></h1>
        <p><?php esc_html_e( 'Do you really want to delete all plugin settings and cached data? This action cannot be undone.', 'meteoprog-weather-informers' ); ?></p>
        <form method="post">
            <?php wp_nonce_field( 'meteoprog_remove_data_action', 'meteoprog_remove_data_nonce' ); ?>
            <p>
                <input type="submit" name="meteoprog_confirm_remove" class="button button-primary" value="<?php echo esc_attr__( 'Yes, delete everything', 'meteoprog-weather-informers' ); ?>">
                <a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button">
                    <?php esc_html_e( 'Cancel', 'meteoprog-weather-informers' ); ?>
                </a>
            </p>
        </form>
    </div>
    <?php
}

/**
 * Run when the plugin is uninstalled via WordPress.
 * Deletes only the cached transients, preserving API key and settings.
 */
function meteoprog_informers_on_uninstall() {
    global $wpdb;
    // Delete only transients related to informers cache
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_meteoprog_informers_cache_%'" );
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_meteoprog_informers_cache_%'" );
}


function meteoprog_delete_all_plugin_data() {
    global $wpdb;

    // Delete plugin options
    delete_option( 'meteoprog_api_key' );
    delete_option( 'meteoprog_default_informer_id' );

    // Delete plugin transients
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_meteoprog_informers_cache_%'" );
    $wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_meteoprog_informers_cache_%'" );
}