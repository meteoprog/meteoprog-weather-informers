<?php
/**
 * Default Widget Form — Select and Save Default Informer.
 *
 * This file renders the admin form for selecting a default Meteoprog informer.
 * The selected informer will be used when the shortcode [meteoprog_informer]
 * is inserted without specifying an ID.
 *
 * @package    MeteoprogWeatherInformers
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2><?php _e( 'Default Widget', 'meteoprog-weather-informers' ); ?></h2>

<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="meteoprog-default-form">
	<?php wp_nonce_field( 'meteoprog_save_default_nonce' ); ?>
	<input type="hidden" name="action" value="meteoprog_save_default" />

	<label for="default_informer_id">
		<?php _e( 'Select default widget:', 'meteoprog-weather-informers' ); ?>
	</label>

	<select name="default_informer_id"
			id="default_informer_id"
			class="meteoprog-default-informer"
			aria-describedby="meteoprog-default-informer-description"
			<?php disabled( empty( $informers ) ); ?>>
		<option value=""><?php _e( '— None —', 'meteoprog-weather-informers' ); ?></option>
		<?php foreach ( $informers as $inf ) : ?>
			<?php
				$iid          = meteoprog_mask_string( $inf['informer_id'] );
				$domain       = $inf['domain'];
				$active       = isset( $inf['active'] ) ? (int) $inf['active'] : 0;
				$icon         = $active ? '🟢' : '🔴';
				$domain_host  = parse_url( $domain, PHP_URL_HOST );
				$match        = $domain_host && $current_host && $domain_host === $current_host;
				$domain_label = $match
					? '✔ ' . __( 'Domain OK', 'meteoprog-weather-informers' )
					: '✖ ' . __( 'Domain mismatch', 'meteoprog-weather-informers' );

				$title_text = sprintf(
					'%s%s (%s)',
					$domain ? $domain . ' — ' : '',
					$iid,
					$domain_label
				);
			?>
			<option value="<?php echo esc_attr( $iid ); ?>"
				<?php selected( $default_id, $iid ); ?>
				title="<?php echo esc_attr( $title_text ); ?>">
				<?php echo $icon . ' ' . esc_html( $domain ?: __( 'No domain', 'meteoprog-weather-informers' ) ) . ' — ' . esc_html( $iid ) . ' (' . esc_html( $domain_label ) . ')'; ?>
			</option>
		<?php endforeach; ?>
	</select>

	<?php submit_button( __( 'Save', 'meteoprog-weather-informers' ), 'primary', '', false ); ?>
</form>

<p id="meteoprog-default-informer-description" class="description">
	<?php _e( 'If you insert the shortcode [meteoprog_informer] without ID, this widget will be used automatically.', 'meteoprog-weather-informers' ); ?>
</p>
