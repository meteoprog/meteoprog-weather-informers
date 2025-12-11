<?php
/**
 * Informers Table — List of User Widgets.
 *
 * Renders the admin table listing all user informers (widgets),
 * including shortcode/placeholder copy buttons and preview options.
 *
 * @package    MeteoprogWeatherInformers
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<hr>
<h2>Create widget (Informer)</h2>
<div class="meteoprog-admin-table-actions">
	<a href="https://billing.meteoprog.com/informer/?utm_source=wp-plugin&utm_medium=admin-link&utm_campaign=meteoprog-weather-widgets"
		target="_blank" rel="noopener noreferrer"
		class="button meteoprog-create-new-btn"
		aria-label="<?php esc_attr_e( 'Create new widget (Informer) — opens the Meteoprog website', 'meteoprog-weather-informers' ); ?>">
		<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span>
		<?php esc_html_e( 'Create widget (Informer)', 'meteoprog-weather-informers' ); ?>
	</a>
	<span class="meteoprog-create-new-note">
		<?php esc_html_e( 'Opens the Meteoprog.com informer generator in a new tab', 'meteoprog-weather-informers' ); ?>
	</span>
</div>

<hr>

<h2><?php esc_html_e( 'Your Widgets (Informers)', 'meteoprog-weather-informers' ); ?></h2>

<p>
	<?php esc_html_e( 'You can create new free and fully customizable weather widgets (informers) at', 'meteoprog-weather-informers' ); ?>
	<a href="https://billing.meteoprog.com/informer/?utm_source=wp-plugin&utm_medium=admin-link&utm_campaign=meteoprog-weather-widgets"
		target="_blank" rel="noopener noreferrer">https://billing.meteoprog.com/informer/</a>.
</p>

<table class="widefat striped">
	<thead>
		<tr>
			<th style="width:60px; text-align:center;"><?php esc_html_e( 'Status', 'meteoprog-weather-informers' ); ?></th>
			<th style="width:620px;"><?php esc_html_e( 'Informer', 'meteoprog-weather-informers' ); ?></th>
			<th style="width:160px;"><?php esc_html_e( 'Copy', 'meteoprog-weather-informers' ); ?></th>
			<th><?php esc_html_e( 'Preview', 'meteoprog-weather-informers' ); ?></th>
		</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $informers as $meteoprog_inf ) :

		$meteoprog_iid             = isset( $meteoprog_inf['informer_id'] ) ? $meteoprog_inf['informer_id'] : '';
		$meteoprog_informer_domain = ! empty( $meteoprog_inf['domain'] ) ? $meteoprog_inf['domain'] : '';
		$meteoprog_active          = isset( $meteoprog_inf['active'] ) ? (int) $meteoprog_inf['active'] : 0;

		$meteoprog_short_code  = '[meteoprog_informer id="' . $meteoprog_iid . '"]';
		$meteoprog_placeholder = '{meteoprog_informer_' . $meteoprog_iid . '}';

		$meteoprog_icon = $meteoprog_active ? '🟢' : '🔴';

		$meteoprog_domain_host = $meteoprog_informer_domain ? wp_parse_url( $meteoprog_informer_domain, PHP_URL_HOST ) : '';
		$meteoprog_match       = $meteoprog_domain_host && $current_host && ( $meteoprog_domain_host === $current_host );

		$meteoprog_domain_label = $meteoprog_match
			? '<span style="color:green;font-weight:bold;">' . esc_html__( '✔ Domain OK', 'meteoprog-weather-informers' ) . '</span>'
			: '<span style="color:red;font-weight:bold;">' . esc_html__( '✖ Domain mismatch', 'meteoprog-weather-informers' ) . '</span>';

		?>
		<tr>
			<!-- Status -->
			<td data-label="<?php esc_attr_e( 'Status', 'meteoprog-weather-informers' ); ?>" style="text-align:center; vertical-align:top;">
				<?php echo esc_html( $meteoprog_icon ); ?>
			</td>

			<!-- Informer details -->
			<td data-label="<?php esc_attr_e( 'Informer', 'meteoprog-weather-informers' ); ?>">
				<div class="meteoprog-id-line">
					<strong><?php esc_html_e( 'ID:', 'meteoprog-weather-informers' ); ?></strong>
					<code><?php echo esc_html( $meteoprog_iid ); ?></code>
				</div>
				<div class="meteoprog-code-line">
					<strong><?php esc_html_e( 'Shortcode:', 'meteoprog-weather-informers' ); ?></strong>
					<code><?php echo esc_html( $meteoprog_short_code ); ?></code>
				</div>
				<div class="meteoprog-code-line">
					<strong><?php esc_html_e( 'Placeholder:', 'meteoprog-weather-informers' ); ?></strong>
					<code><?php echo esc_html( $meteoprog_placeholder ); ?></code>
				</div>
				<div class="meteoprog-domain">
					<strong><?php esc_html_e( 'Domain:', 'meteoprog-weather-informers' ); ?></strong>
					<?php echo esc_html( $meteoprog_informer_domain ); ?> — <?php echo wp_kses_post( $meteoprog_domain_label ); ?>
				</div>
			</td>

			<!-- Copy buttons -->
			<td data-label="<?php esc_attr_e( 'Copy', 'meteoprog-weather-informers' ); ?>" class="meteoprog-copy-cell">
				<div class="meteoprog-copy-line">
					<a href="#" class="meteoprog-copy button button-secondary"
						data-copy="<?php echo esc_attr( $meteoprog_short_code ); ?>">
						<?php esc_html_e( 'Shortcode', 'meteoprog-weather-informers' ); ?>
					</a>
				</div>
				<div class="meteoprog-copy-line">
					<a href="#" class="meteoprog-copy button button-secondary"
						data-copy="<?php echo esc_attr( $meteoprog_placeholder ); ?>">
						<?php esc_html_e( 'Placeholder', 'meteoprog-weather-informers' ); ?>
					</a>
				</div>
			</td>

			<!-- Preview -->
			<td data-label="<?php esc_attr_e( 'Preview', 'meteoprog-weather-informers' ); ?>" class="meteoprog-preview-cell">
				<?php if ( $meteoprog_match ) : ?>
					<a href="#" class="meteoprog-preview button button-secondary"
						data-id="<?php echo esc_attr( $meteoprog_iid ); ?>">
						👁 <?php esc_html_e( 'Preview', 'meteoprog-weather-informers' ); ?>
					</a>
					<div id="meteoprog-preview-<?php echo esc_attr( $meteoprog_iid ); ?>"
						class="meteoprog-preview-box"
						style="display:none;margin-top:10px;"></div>
				<?php else : ?>
					<span style="color:red;font-weight:bold;">
						<?php esc_html_e( '✖ Domain mismatch', 'meteoprog-weather-informers' ); ?>
					</span>
					<span class="dashicons dashicons-editor-help"
						title="<?php esc_attr_e( 'This informer was created for a different domain. Please check the domain specified when generating the informer on https://billing.meteoprog.com/informer/.', 'meteoprog-weather-informers' ); ?>"
						aria-label="<?php esc_attr_e( 'This informer was created for a different domain. Please check the domain specified when generating the informer on https://billing.meteoprog.com/informer/.', 'meteoprog-weather-informers' ); ?>">
					</span>
				<?php endif; ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
