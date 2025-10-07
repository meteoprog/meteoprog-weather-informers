<?php
/**
 * Classic Widget for Meteoprog Weather Informers (WP 4.9+).
 *
 * Provides a legacy WordPress widget for sites not using Gutenberg or builders.
 * Allows selecting a specific informer ID or falling back to the default one.
 *
 * Compatible with PHP 5.6+ and WordPress 4.9+.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Widget
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Meteoprog_Informers_Widget extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'meteoprog_informer_widget',
			__( 'Meteoprog Weather Widget', 'meteoprog-weather-informers' ),
			array(
				'description' => __( 'Display a Meteoprog weather informer (supports default or specific ID).', 'meteoprog-weather-informers' ),
			)
		);
	}

	/**
	 * Frontend widget display.
	 *
	 * Renders the selected informer by ID, or falls back to the default informer ID
	 * stored in the options if none is set in the widget instance.
	 *
	 * @param array $args     Widget arguments (before/after wrappers).
	 * @param array $instance Widget instance settings (may contain 'id').
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];

		// Sanitize informer ID (whether from instance or default option).
		$id = ! empty( $instance['id'] )
			? sanitize_text_field( $instance['id'] )
			: get_option( 'meteoprog_default_informer_id', '' );

		if ( empty( $id ) ) {
			echo '<!-- Meteoprog informer: no ID set -->';
		} else {
			$frontend = isset( $GLOBALS['meteoprog_weather_informers_instance'] )
				? $GLOBALS['meteoprog_weather_informers_instance']
				: null;

			if ( $frontend ) {
				// Build and output informer HTML.
				echo $frontend->build_html( $id );
			} else {
				echo '<!-- Meteoprog informer: frontend instance not available -->';
			}
		}

		echo $args['after_widget'];
	}

	/**
	 * Backend form in WP Admin.
	 */
	public function form( $instance ) {
		$id = ! empty( $instance['id'] ) ? $instance['id'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>">
				<?php esc_html_e( 'Informer ID (leave empty to use default)', 'meteoprog-weather-informers' ); ?>
			</label>
			<input
				class="widefat"
				id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"
				name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>"
				type="text"
				value="<?php echo esc_attr( $id ); ?>"
			/>
		</p>
		<?php
	}

	/**
	 * Sanitize widget settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance       = array();
		$instance['id'] = ! empty( $new_instance['id'] ) ? sanitize_text_field( $new_instance['id'] ) : '';
		return $instance;
	}
}

/**
 * Register Meteoprog Widget.
 */
add_action(
	'widgets_init',
	function () {

		// Do not enqueue inside Elementor editor.
		if ( function_exists( 'meteoprog_is_elementor_editor_mode' ) && meteoprog_is_elementor_editor_mode() ) {
			return;
		}

		register_widget( 'Meteoprog_Informers_Widget' );
	}
);
