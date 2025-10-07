<?php
/**
 * Admin Form Template — API Key Settings Page.
 *
 * This file renders the admin settings form for entering and saving the
 * Meteoprog Widget API key, as well as a button to refresh the informer list.
 *
 * @package    MeteoprogWeatherInformers
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined('ABSPATH') ) {
    exit;
}

?>
<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="meteoprog-api-form">
    <input type="hidden" name="action" value="meteoprog_save_api_key" />
    <?php wp_nonce_field( 'meteoprog_informers_options-options' ); ?>

    <div class="api-key-row">
        <label for="<?php echo esc_attr( $this->opt_api_key ); ?>">
            <?php _e( 'API Key', 'meteoprog-weather-informers' ); ?>
            <span class="dashicons dashicons-editor-help"
                title="<?php echo esc_attr( __( 'Important: The widget API key is different from the Meteoprog Weather API key. Use the key from billing.meteoprog.com.', 'meteoprog-weather-informers' ) ); ?>"
                aria-label="<?php esc_attr_e( 'Help', 'meteoprog-weather-informers' ); ?>">
            </span>
        </label>

        <input type="text"
               id="<?php echo esc_attr( $this->opt_api_key ); ?>"
               name="<?php echo esc_attr( $this->opt_api_key ); ?>"
               value="<?php echo esc_attr( $masked_key ); ?>"
               size="50"
               autocomplete="off"
               autocapitalize="none"
               spellcheck="false"
               aria-describedby="meteoprog-api-key-description" />

        <?php submit_button( __( 'Save API Key', 'meteoprog-weather-informers' ), 'primary', 'submit', false ); ?>

        <a href="<?php echo esc_url(
            wp_nonce_url(
                admin_url( 'admin-post.php?action=meteoprog_refresh' ),
                'meteoprog_refresh_nonce'
            )
        ); ?>" class="button button-secondary" rel="noopener noreferrer">
            <?php _e( 'Refresh list', 'meteoprog-weather-informers' ); ?>
        </a>
    </div>

    <p id="meteoprog-api-key-description" class="description">
        <?php _e( 'Enter your Meteoprog Widget API key. You can find it in your account on billing.meteoprog.com. This key is required to load and manage your widgets.', 'meteoprog-weather-informers' ); ?>
    </p>
</form>
<hr>
