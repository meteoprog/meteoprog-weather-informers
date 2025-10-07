<?php
/**
 * Weather API Box — Optional Developer Features Overview.
 *
 * This file renders the collapsible box describing optional Meteoprog Weather API
 * capabilities for developers who need more than the free widget functionality.
 *
 * @package    MeteoprogWeatherInformers
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined('ABSPATH') ) {
    exit;
}

?>
<div class="meteoprog-admin-api-box notice notice-warning">
    <button type="button"
            class="collapsible-toggle"
            aria-expanded="false"
            aria-controls="meteoprog-api-content">
        <h2 class="meteoprog-admin-api-box__title">
            ☁️ <?php _e( 'Meteoprog Weather API (optional)', 'meteoprog-weather-informers' ); ?>
        </h2>
        <span class="toggle-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
    </button>

    <div id="meteoprog-api-content" class="collapsible-content" aria-hidden="true">
        <p>
            <strong>
                <?php _e( 'For developers who need more than widgets, Meteoprog provides a powerful Weather API suite.', 'meteoprog-weather-informers' ); ?>
            </strong>
        </p>

        <ul class="meteoprog-admin-api-box__list">
            <li>🌐 <?php _e( 'Worldwide coverage — over 2,150,000 geo points in 170+ countries', 'meteoprog-weather-informers' ); ?></li>
            <li>📝 <?php _e( 'OpenAPI documentation available for easy integration', 'meteoprog-weather-informers' ); ?></li>
            <li>🌍 <?php _e( 'Multilingual API — 35+ supported languages', 'meteoprog-weather-informers' ); ?></li>
            <li>🕒 <?php _e( 'Forecasts up to 14 days — hourly & daily granularity', 'meteoprog-weather-informers' ); ?></li>
            <li>📊 <?php _e( 'Rich data: temperature, precipitation, wind, pressure, UV index, and more', 'meteoprog-weather-informers' ); ?></li>
            <li>💨 <?php _e( 'Extra modules:', 'meteoprog-weather-informers' ); ?>
                <ul class="meteoprog-admin-api-box__sublist">
                    <li><?php _e( 'Air Quality', 'meteoprog-weather-informers' ); ?></li>
                    <li><?php _e( 'Water', 'meteoprog-weather-informers' ); ?></li>
                    <li><?php _e( 'Astronomy', 'meteoprog-weather-informers' ); ?></li>
                    <li><?php _e( 'K-index (geomagnetic)', 'meteoprog-weather-informers' ); ?></li>
                    <li><?php _e( 'Weather Extremes & Historical Extremes', 'meteoprog-weather-informers' ); ?></li>
                    <li><?php _e( 'Timezone & Geocoding APIs', 'meteoprog-weather-informers' ); ?></li>
                </ul>
            </li>
            <li>🔗 <?php _e( 'Integration formats: JSON or XML over HTTPS', 'meteoprog-weather-informers' ); ?></li>
            <li>🆓 <?php _e( '14-day free trial available for testing a selection of API features', 'meteoprog-weather-informers' ); ?></li>
        </ul>

        <p class="meteoprog-admin-api-box__cta">
            <a href="https://billing.meteoprog.com/pricing/?utm_source=wp-plugin&utm_medium=admin-link&utm_campaign=meteoprog-weather-widgets"
               target="_blank"
               class="button meteoprog-admin-api-box__button">
                📖 <?php _e( 'Learn more & view API documentation', 'meteoprog-weather-informers' ); ?>
            </a>
        </p>

        <p class="meteoprog-admin-api-box__note">
            <?php _e( 'Note: The Weather API is optional and separate. Widgets use a free Informer API key and do not require the paid API.', 'meteoprog-weather-informers' ); ?>
        </p>
    </div>
</div>
