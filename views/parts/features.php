<?php
/**
 * Features Box — Key Functionality Overview.
 *
 * This file renders the collapsible box with an overview of Meteoprog Weather Widget
 * features, integrations, and supported functionality within the plugin admin UI.
 *
 * @package    MeteoprogWeatherInformers
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="meteoprog-admin-features collapsible-box notice notice-success">
	<button type="button"
			class="collapsible-toggle"
			aria-expanded="false"
			aria-controls="meteoprog-features-content">
		<h3>🌤 <?php esc_html_e( 'Meteoprog Widget Features', 'meteoprog-weather-informers' ); ?></h3>
		<span class="toggle-icon dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
	</button>

	<div id="meteoprog-features-content" class="collapsible-content" aria-hidden="true">
		<div class="features-grid">

			<div class="feature-column">
				<h3>🌍 <?php esc_html_e( 'Location', 'meteoprog-weather-informers' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Auto-detect by visitor IP', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Set fixed location (any city/town)', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Fallback default location', 'meteoprog-weather-informers' ); ?></li>
				</ul>
			</div>

			<div class="feature-column">
				<h3>🗣 <?php esc_html_e( 'Language & Coverage', 'meteoprog-weather-informers' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Auto-detect visitor language', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Set default widget language', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Informer interface supports 35+ languages', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Covers over 2,150,000 locations worldwide with up-to-date forecasts', 'meteoprog-weather-informers' ); ?></li>
				</ul>
				<p class="feature-note">
					<?php esc_html_e( 'The WordPress plugin interface is currently in English. Community translations will be delivered via WordPress.org translation system.', 'meteoprog-weather-informers' ); ?>
				</p>
			</div>

			<div class="feature-column">
				<h3>📊 <?php esc_html_e( 'Information Display', 'meteoprog-weather-informers' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Current weather: temperature, description, icon', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Wind, Humidity, Precipitation, Pressure, UV index', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Hourly and multi-day forecast', 'meteoprog-weather-informers' ); ?></li>
				</ul>
			</div>

			<div class="feature-column">
				<h3>🌡 <?php esc_html_e( 'Units', 'meteoprog-weather-informers' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Degrees Celsius (°C)', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Degrees Fahrenheit (°F)', 'meteoprog-weather-informers' ); ?></li>
				</ul>
			</div>

			<div class="feature-column">
				<h3>🎨 <?php esc_html_e( 'Styling & Appearance', 'meteoprog-weather-informers' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Custom width and rounded corners', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Font and icon colors', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Light / Dark background', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Border and shadow options', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Interactive theme', 'meteoprog-weather-informers' ); ?></li>
				</ul>
			</div>

			<div class="feature-column">
				<h3>⚙️ <?php esc_html_e( 'Interactivity', 'meteoprog-weather-informers' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Automatic weather updates', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Responsive layout', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Cross-browser compatibility', 'meteoprog-weather-informers' ); ?></li>
				</ul>
			</div>

			<div class="feature-column">
				<h3>🔑 <?php esc_html_e( 'Integration', 'meteoprog-weather-informers' ); ?></h3>
				<ul>
					<li>
						<?php esc_html_e( 'Shortcodes & Placeholders:', 'meteoprog-weather-informers' ); ?>
						<em><?php esc_html_e( 'Insert directly into content or templates', 'meteoprog-weather-informers' ); ?></em>
					</li>
					<li>
						<?php esc_html_e( 'Legacy Widget (WordPress 4.9–5.7):', 'meteoprog-weather-informers' ); ?>
						<em><?php esc_html_e( 'Appearance → Widgets → Meteoprog Weather Widget', 'meteoprog-weather-informers' ); ?></em>
					</li>
					<li>
						<?php esc_html_e( 'Gutenberg block:', 'meteoprog-weather-informers' ); ?>
						<em><?php esc_html_e( 'Widgets → Meteoprog Weather Widget', 'meteoprog-weather-informers' ); ?></em>
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
						<?php esc_html_e( 'Shortcodes Ultimate:', 'meteoprog-weather-informers' ); ?>
						<em><?php esc_html_e( 'Custom shortcode [su_meteoprog_informer] with live preview in admin, plus full integration into the SU insert dialog.', 'meteoprog-weather-informers' ); ?></em>
					</li>
					<li>
						<?php esc_html_e( 'WP-CLI & REST API:', 'meteoprog-weather-informers' ); ?>
						<em><?php esc_html_e( 'Manage keys, defaults, and cache; or integrate via REST endpoint', 'meteoprog-weather-informers' ); ?></em>
					</li>
				</ul>
			</div>

			<div class="feature-column">
				<h3>🖥 WP-CLI</h3>
				<ul>
					<li><code>wp meteoprog-weather-informers set-key &lt;key&gt;</code> — <?php esc_html_e( 'set API key', 'meteoprog-weather-informers' ); ?></li>
					<li><code>wp meteoprog-weather-informers get-key</code> — <?php esc_html_e( 'show current API key (masked)', 'meteoprog-weather-informers' ); ?></li>
					<li><code>wp meteoprog-weather-informers set-default &lt;id&gt;</code> — <?php esc_html_e( 'set default informer', 'meteoprog-weather-informers' ); ?></li>
					<li><code>wp meteoprog-weather-informers get-default</code> — <?php esc_html_e( 'show default informer', 'meteoprog-weather-informers' ); ?></li>
					<li><code>wp meteoprog-weather-informers refresh</code> — <?php esc_html_e( 'clear cache and reload informers from API', 'meteoprog-weather-informers' ); ?></li>
					<li><code>wp meteoprog-weather-informers clear-cache</code> — <?php esc_html_e( 'clear cache only', 'meteoprog-weather-informers' ); ?></li>
				</ul>
			</div>

			<div class="feature-column feature-highlight">
				<h3>💸 <?php esc_html_e( 'Free to Use', 'meteoprog-weather-informers' ); ?></h3>
				<p class="highlight-subtitle">
					<?php esc_html_e( 'Meteoprog Weather Widget is completely free — no hidden fees, subscriptions, or usage limits.', 'meteoprog-weather-informers' ); ?>
				</p>
				<ul class="highlight-list">
					<li><?php esc_html_e( 'Supports WordPress 4.9 → latest and PHP 5.6 → 8.3 for maximum compatibility.', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Works with Gutenberg, Elementor, Shortcodes Ultimate, Legacy Widgets, shortcodes, placeholders, and WP-CLI.', 'meteoprog-weather-informers' ); ?></li>
					<li><?php esc_html_e( 'Actively maintained to support older WordPress versions and popular integrations.', 'meteoprog-weather-informers' ); ?></li>
				</ul>
				<p class="highlight-idea">
					💡 <?php esc_html_e( 'We welcome your ideas for future free features and potential premium extensions.', 'meteoprog-weather-informers' ); ?>
				</p>
			</div>

		</div>
	</div>
</div>
