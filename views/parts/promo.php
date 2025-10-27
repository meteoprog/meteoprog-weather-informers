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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="meteoprog-admin-promo">
	<hr class="meteoprog-admin-promo__divider">

	<p class="meteoprog-admin-promo__text">
		<?php esc_html_e( 'Enjoying Meteoprog Weather Widgets?', 'meteoprog-weather-informers' ); ?><br>
		<a href="https://wordpress.org/support/plugin/meteoprog-weather-informers/reviews/"
			target="_blank"
			rel="noopener noreferrer"
			class="meteoprog-admin-promo__link">
			⭐ <?php esc_html_e( 'Give us a ★★★★★ rating', 'meteoprog-weather-informers' ); ?>
		</a>
		<?php esc_html_e( 'on WordPress.org — it really helps us!', 'meteoprog-weather-informers' ); ?>
	</p>
</div>