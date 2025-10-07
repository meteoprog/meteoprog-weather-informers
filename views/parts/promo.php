<?php
/**
 * Promo Box — Rating and Feedback CTA.
 *
 * This file renders the promo box encouraging users to rate the plugin
 * on WordPress.org to support further development.
 *
 * @package    MeteoprogWeatherInformers
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined('ABSPATH') ) {
    exit;
}

?>
<div class="meteoprog-admin-promo">
    <hr class="meteoprog-admin-promo__divider">

    <p class="meteoprog-admin-promo__text">
        <?php _e( 'Enjoying Meteoprog Weather Widgets?', 'meteoprog-weather-informers' ); ?><br>
        <a href="https://wordpress.org/support/plugin/meteoprog-weather-informers/reviews/?filter=5"
           target="_blank"
           rel="noopener noreferrer"
           class="meteoprog-admin-promo__link">
            ⭐ <?php _e( 'Give us a ★★★★★ rating', 'meteoprog-weather-informers' ); ?>
        </a>
        <?php _e( 'on WordPress.org — it really helps us!', 'meteoprog-weather-informers' ); ?>
    </p>
</div>