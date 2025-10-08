<?php
/**
 * Admin Settings Page — Main Plugin UI.
 *
 * This file renders the main admin settings page for the Meteoprog Weather Widgets plugin,
 * including API key form, features section, help blocks, informer table and promo content.
 *
 * @package    MeteoprogWeatherInformers
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap meteoprog-admin">
	<h1><?php esc_html_e( 'Meteoprog Weather Widget', 'meteoprog-weather-informers' ); ?></h1>

	<?php if ( 'invalid_key' === $error ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Invalid API key. Please check and try again.', 'meteoprog-weather-informers' ); ?></p>
		</div>
	<?php elseif ( 'refresh_failed' === $error ) : ?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Failed to refresh informer list. Check API key or network.', 'meteoprog-weather-informers' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $refreshed ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Informer list has been refreshed successfully.', 'meteoprog-weather-informers' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $saved ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings have been saved successfully.', 'meteoprog-weather-informers' ); ?></p>
		</div>
	<?php endif; ?>

	<?php require __DIR__ . '/parts/features.php'; ?>
	<?php require __DIR__ . '/parts/help.php'; ?>
	<?php require __DIR__ . '/parts/weather-api.php'; ?>

	<?php require __DIR__ . '/parts/apikey-form.php'; ?>

	<?php if ( ! $api_key ) : ?>
		<p><?php esc_html_e( 'Please enter your API key to proceed.', 'meteoprog-weather-informers' ); ?></p>

	<?php else : ?>

		<?php if ( ! $informers ) : ?>
			<p>
				<?php esc_html_e( 'No widgets found. Create free and customizable weather widgets at', 'meteoprog-weather-informers' ); ?>
				<a href="https://billing.meteoprog.com/?utm_source=wp-plugin&utm_medium=admin-link&utm_campaign=meteoprog-weather-widgets"
				target="_blank" rel="noopener noreferrer">https://billing.meteoprog.com</a>.
			</p>
		<?php else : ?>
			<?php include __DIR__ . '/parts/default-select.php'; ?>
			<?php include __DIR__ . '/parts/informers-table.php'; ?>
		<?php endif; ?>

		<?php include __DIR__ . '/parts/promo.php'; ?>

	<?php endif; ?>
</div>