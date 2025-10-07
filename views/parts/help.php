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
		<?php _e( 'How to use Meteoprog Widgets', 'meteoprog-weather-informers' ); ?>
	</h2>

	<p>
		<strong><?php _e( '1. Create new widget:', 'meteoprog-weather-informers' ); ?></strong><br>
		<?php _e( 'You can create new free and fully customizable weather widgets (informers) at', 'meteoprog-weather-informers' ); ?>
		<a href="https://billing.meteoprog.com/informer/?utm_source=wp-plugin&utm_medium=admin-link&utm_campaign=meteoprog-weather-widgets"
			target="_blank" rel="noopener noreferrer">https://billing.meteoprog.com/informer/</a>.
	</p>

	<p>
		<strong><?php _e( '2. API Key:', 'meteoprog-weather-informers' ); ?></strong><br>
		⚠️ <?php _e( 'Important: The API key for widgets (informers) is', 'meteoprog-weather-informers' ); ?>
		<u><?php _e( 'NOT the same as the Meteoprog Weather API key.', 'meteoprog-weather-informers' ); ?></u>
		<?php _e( 'Use only the widget (informer) API key from', 'meteoprog-weather-informers' ); ?>
		<a href="https://billing.meteoprog.com/?utm_source=wp-plugin&utm_medium=admin-link&utm_campaign=meteoprog-weather-widgets"
			target="_blank" rel="noopener noreferrer">https://billing.meteoprog.com</a>.
		<?php _e( 'This key is', 'meteoprog-weather-informers' ); ?>
		<span class="green-text"><?php _e( 'free', 'meteoprog-weather-informers' ); ?></span>
		<?php _e( 'and without limits.', 'meteoprog-weather-informers' ); ?>
	</p>

	<p>
		<strong><?php _e( '3. Domain requirement:', 'meteoprog-weather-informers' ); ?></strong><br>
		<?php _e( 'Each widget (informer) is bound to a specific domain. The domain must exactly match your WordPress site domain, otherwise it will not be displayed.', 'meteoprog-weather-informers' ); ?>
	</p>

	<p>
		<strong><?php _e( '4. Usage options:', 'meteoprog-weather-informers' ); ?></strong>
	</p>

	<ul>
		<li>
			<?php _e( 'Use the', 'meteoprog-weather-informers' ); ?>
			<em><?php _e( 'Meteoprog Weather Widget', 'meteoprog-weather-informers' ); ?></em>
			<?php _e( 'block in the', 'meteoprog-weather-informers' ); ?>
			<strong><?php _e( 'Gutenberg editor', 'meteoprog-weather-informers' ); ?></strong>
			(<?php _e( 'category "Widgets"', 'meteoprog-weather-informers' ); ?>).
		</li>
		<li>
			<?php _e( 'Insert via shortcode:', 'meteoprog-weather-informers' ); ?>
			<code>[meteoprog_informer id="YOUR_INFORMER_ID"]</code>
		</li>
		<li>
			<?php _e( 'Insert via placeholder in content:', 'meteoprog-weather-informers' ); ?>
			<code>{meteoprog_informer_YOUR_INFORMER_ID}</code>
		</li>
		<li>
			<?php _e( 'Elementor integration:', 'meteoprog-weather-informers' ); ?>
			<em>
				<?php _e( 'Add the “Meteoprog Weather Widget” from the', 'meteoprog-weather-informers' ); ?>
				<strong><?php _e( 'Meteoprog Widgets', 'meteoprog-weather-informers' ); ?></strong>
				<?php _e( 'group in the Elementor panel.', 'meteoprog-weather-informers' ); ?>
			</em>
		</li>
		<li>
			<?php _e( 'Shortcodes Ultimate integration:', 'meteoprog-weather-informers' ); ?>
			<em>
				<?php _e( 'Use the custom shortcode', 'meteoprog-weather-informers' ); ?>
				<code>[su_meteoprog_informer id="YOUR_INFORMER_ID"]</code>
				<?php _e( 'to embed a specific informer with live preview in the admin editor, or insert it via the SU insert dialog (Meteoprog Weather).', 'meteoprog-weather-informers' ); ?>
				<br>
				<?php _e( 'You can also use', 'meteoprog-weather-informers' ); ?>
				<code>[su_meteoprog_informer]</code>
				<?php _e( 'without the "id" attribute to display the default informer set in plugin settings.', 'meteoprog-weather-informers' ); ?>
			</em>
		</li>
	</ul>

	<p>
		<strong><?php _e( '5. Default widget:', 'meteoprog-weather-informers' ); ?></strong><br>
		<?php _e( 'If you insert the shortcode', 'meteoprog-weather-informers' ); ?>
		<code>[meteoprog_informer]</code>
		<?php _e( 'without ID, the widget selected in the settings as "Default Widget" will be displayed automatically.', 'meteoprog-weather-informers' ); ?>
		<?php _e( 'You can also use the placeholder', 'meteoprog-weather-informers' ); ?>
		<code>{meteoprog_informer}</code>
		<?php _e( 'to display the default widget inside content.', 'meteoprog-weather-informers' ); ?>
	</p>

	<hr>

	<p>
		📚 <strong><?php _e( 'Need more help?', 'meteoprog-weather-informers' ); ?></strong><br>
		<a href="https://github.com/meteoprog/meteoprog-weather-informers" target="_blank" rel="noopener noreferrer">
			<?php _e( 'View source code on GitHub', 'meteoprog-weather-informers' ); ?>
		</a> |
		<a href="https://wordpress.org/plugins/meteoprog-weather-informers/#faq" target="_blank" rel="noopener noreferrer">
			<?php _e( 'Read FAQ on WordPress.org', 'meteoprog-weather-informers' ); ?>
		</a>
	</p>
</div>
