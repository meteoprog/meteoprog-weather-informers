<?php
/**
 * Help Box — Usage Instructions and Integrations.
 *
 * This file renders the help box on the plugin admin page, providing
 * step-by-step usage instructions, shortcode examples, and integration options.
 *
 * @package    MeteoprogWeatherInformers
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-info meteoprog-help-box">
	<h2 class="title">
		<span aria-hidden="true">ℹ️</span>
		<?php esc_html_e( 'How to use Meteoprog Widgets', 'meteoprog-weather-informers' ); ?>
	</h2>

	<p>
		<strong><?php esc_html_e( '1. Create new widget:', 'meteoprog-weather-informers' ); ?></strong><br>
		<?php esc_html_e( 'You can create new free and fully customizable weather widgets (informers) at', 'meteoprog-weather-informers' ); ?>
		<a href="https://billing.meteoprog.com/informer/?utm_source=wp-plugin&utm_medium=admin-link&utm_campaign=meteoprog-weather-widgets"
			target="_blank" rel="noopener noreferrer">https://billing.meteoprog.com/informer/</a>.
	</p>

	<p>
		<strong><?php esc_html_e( '2. API Key:', 'meteoprog-weather-informers' ); ?></strong><br>
		⚠️ <?php esc_html_e( 'Important: The API key for widgets (informers) is', 'meteoprog-weather-informers' ); ?>
		<u><?php esc_html_e( 'NOT the same as the Meteoprog Weather API key.', 'meteoprog-weather-informers' ); ?></u>
		<?php esc_html_e( 'Use only the widget (informer) API key from', 'meteoprog-weather-informers' ); ?>
		<a href="https://billing.meteoprog.com/?utm_source=wp-plugin&utm_medium=admin-link&utm_campaign=meteoprog-weather-widgets"
			target="_blank" rel="noopener noreferrer">https://billing.meteoprog.com</a>.
		<?php esc_html_e( 'This key is', 'meteoprog-weather-informers' ); ?>
		<span class="green-text"><?php esc_html_e( 'free', 'meteoprog-weather-informers' ); ?></span>
		<?php esc_html_e( 'and without limits.', 'meteoprog-weather-informers' ); ?>
	</p>

	<p>
		<strong><?php esc_html_e( '3. Domain requirement:', 'meteoprog-weather-informers' ); ?></strong><br>
		<?php esc_html_e( 'Each widget (informer) is bound to a specific domain. The domain must exactly match your WordPress site domain, otherwise it will not be displayed.', 'meteoprog-weather-informers' ); ?>
	</p>

	<p>
		<strong><?php esc_html_e( '4. Usage options:', 'meteoprog-weather-informers' ); ?></strong>
	</p>

	<ul>
		<li>
			<?php esc_html_e( 'Use the', 'meteoprog-weather-informers' ); ?>
			<em><?php esc_html_e( 'Meteoprog Weather Widget', 'meteoprog-weather-informers' ); ?></em>
			<?php esc_html_e( 'block in the', 'meteoprog-weather-informers' ); ?>
			<strong><?php esc_html_e( 'Gutenberg editor', 'meteoprog-weather-informers' ); ?></strong>
			(<?php esc_html_e( 'category "Widgets"', 'meteoprog-weather-informers' ); ?>).
		</li>
		<li>
			<?php esc_html_e( 'Insert via shortcode:', 'meteoprog-weather-informers' ); ?>
			<code>[meteoprog_informer id="YOUR_INFORMER_ID"]</code>
		</li>
		<li>
			<?php esc_html_e( 'Insert via placeholder in content:', 'meteoprog-weather-informers' ); ?>
			<code>{meteoprog_informer_YOUR_INFORMER_ID}</code>
		</li>
		<li>
			<?php esc_html_e( 'Elementor integration:', 'meteoprog-weather-informers' ); ?>
			<em>
				<?php esc_html_e( 'Add the “Meteoprog Weather Widget” from the', 'meteoprog-weather-informers' ); ?>
				<strong><?php esc_html_e( 'Meteoprog Widgets', 'meteoprog-weather-informers' ); ?></strong>
				<?php esc_html_e( 'group in the Elementor panel.', 'meteoprog-weather-informers' ); ?>
			</em>
		</li>
		<li>
			<?php esc_html_e( 'Shortcodes Ultimate integration:', 'meteoprog-weather-informers' ); ?>
			<em>
				<?php esc_html_e( 'Use the custom shortcode', 'meteoprog-weather-informers' ); ?>
				<code>[su_meteoprog_informer id="YOUR_INFORMER_ID"]</code>
				<?php esc_html_e( 'to embed a specific informer with live preview in the admin editor, or insert it via the SU insert dialog (Meteoprog Weather).', 'meteoprog-weather-informers' ); ?>
				<br>
				<?php esc_html_e( 'You can also use', 'meteoprog-weather-informers' ); ?>
				<code>[su_meteoprog_informer]</code>
				<?php esc_html_e( 'without the "id" attribute to display the default informer set in plugin settings.', 'meteoprog-weather-informers' ); ?>
			</em>
		</li>
	</ul>

	<p>
		<strong><?php esc_html_e( '5. Default widget:', 'meteoprog-weather-informers' ); ?></strong><br>
		<?php esc_html_e( 'If you insert the shortcode', 'meteoprog-weather-informers' ); ?>
		<code>[meteoprog_informer]</code>
		<?php esc_html_e( 'without ID, the widget selected in the settings as "Default Widget" will be displayed automatically.', 'meteoprog-weather-informers' ); ?>
		<?php esc_html_e( 'You can also use the placeholder', 'meteoprog-weather-informers' ); ?>
		<code>{meteoprog_informer}</code>
		<?php esc_html_e( 'to display the default widget inside content.', 'meteoprog-weather-informers' ); ?>
	</p>

	<hr>

	<p>
		📚 <strong><?php esc_html_e( 'Need more help?', 'meteoprog-weather-informers' ); ?></strong><br>
		<a href="https://github.com/meteoprog/meteoprog-weather-informers" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'View source code on GitHub', 'meteoprog-weather-informers' ); ?>
		</a> |
		<a href="https://wordpress.org/plugins/meteoprog-weather-informers/#faq" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Read FAQ on WordPress.org', 'meteoprog-weather-informers' ); ?>
		</a>
	</p>
</div>
