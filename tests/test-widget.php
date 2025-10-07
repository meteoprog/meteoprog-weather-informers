<?php
/**
 * Widget Tests for Meteoprog Weather Informers.
 *
 * Covers registration, rendering, and admin form behavior of the legacy widget.
 *
 * @package    MeteoprogWeatherInformers
 * @subpackage Tests
 * @since      1.0.0
 * @author     meteoprog
 * @license    GPL-2.0-or-later
 */

class WidgetTest extends WP_Compat_TestCase {

    /**
     * @before
     * Runs automatically before each test.
     * Initializes the widget environment and registers the widget.
     */
    public function prepare_environment() {
        // Create a mock frontend object so the widget can render predictable HTML
        $frontend = $this->getMockBuilder(stdClass::class)
                        ->setMethods(['build_html'])
                        ->getMock();

        // Always return a static HTML string from build_html()
        $frontend->method('build_html')->willReturn('<div>Informer HTML</div>');

        // Store mock frontend instance in global to mimic plugin behavior
        $GLOBALS['meteoprog_weather_informers_instance'] = $frontend;

        // Explicitly register the widget to ensure it's available for tests
        register_widget('Meteoprog_Informers_Widget');
    }

    // -------------------------------------------------------------------------
    // Class and registration tests
    // -------------------------------------------------------------------------

    /**
     * Test that the widget class exists and can be autoloaded.
     */
    public function test_widget_class_exists() {
        $this->assertTrue(class_exists('Meteoprog_Informers_Widget'));
    }

    /**
     * Test that the widget is registered in the global widget factory.
     */
    public function test_widget_registered() {
        global $wp_widget_factory;
        $this->assertArrayHasKey(
            'Meteoprog_Informers_Widget',
            $wp_widget_factory->widgets,
            'Widget not registered'
        );
    }

    // -------------------------------------------------------------------------
    // Widget rendering tests
    // -------------------------------------------------------------------------

    /**
     * Test widget output when no ID is provided in the instance settings.
     */
    public function test_widget_output_no_id() {
        $widget = new Meteoprog_Informers_Widget();
        ob_start();
        $widget->widget(
            ['before_widget' => '<section>', 'after_widget' => '</section>'],
            [] // instance without id
        );
        $output = ob_get_clean();

        $this->assertStringContainsString('<!-- Meteoprog informer: no ID set -->', $output);
    }

    /**
     * Test widget output when an ID is provided.
     */
    public function test_widget_output_with_id() {
        $widget = new Meteoprog_Informers_Widget();
        ob_start();
        $widget->widget(
            ['before_widget' => '<section>', 'after_widget' => '</section>'],
            ['id' => '123']
        );
        $output = ob_get_clean();

        $this->assertStringContainsString('<div>Informer HTML</div>', $output);
    }

    /**
     * Test that the widget output respects before_widget and after_widget wrappers.
     */
    public function test_widget_wraps_with_before_and_after() {
        $widget = new Meteoprog_Informers_Widget();
        ob_start();
        $widget->widget(
            ['before_widget' => '<div class="wrap">', 'after_widget' => '</div>'],
            ['id' => '123']
        );
        $output = ob_get_clean();

        $this->assertStringStartsWith('<div class="wrap">', $output);
        $this->assertStringEndsWith('</div>', trim($output));
    }

    /**
     * Test widget output when the frontend instance is not set globally.
     */
    public function test_widget_output_without_frontend_instance() {
        unset($GLOBALS['meteoprog_weather_informers_instance']);

        $widget = new Meteoprog_Informers_Widget();
        ob_start();
        $widget->widget(
            ['before_widget' => '<section>', 'after_widget' => '</section>'],
            ['id' => '123']
        );
        $output = ob_get_clean();

        // In this case frontend won't be set and HTML won't appear
        $this->assertStringNotContainsString('Informer HTML', $output);
    }

    // -------------------------------------------------------------------------
    // Widget admin form and update tests
    // -------------------------------------------------------------------------

    /**
     * Test that the widget admin form generates an input with the correct value.
     */
    public function test_widget_form_generates_input() {
        $widget = new Meteoprog_Informers_Widget();
        ob_start();
        $widget->form(['id' => 'abc']);
        $form_html = ob_get_clean();

        $this->assertStringContainsString('value="abc"', $form_html);
        $this->assertStringContainsString('Informer ID', $form_html);
    }

    /**
     * Test that the widget update() method sanitizes input correctly.
     */
    public function test_widget_update_sanitizes_input() {
        $widget = new Meteoprog_Informers_Widget();
        $updated = $widget->update(['id' => ' <b>123</b> '], []);
        $this->assertEquals('123', $updated['id']);
    }

    /**
     * Test widget output uses default informer ID when instance ID is empty.
     */
    public function test_widget_uses_default_id_if_no_instance_id() {
        update_option('meteoprog_default_informer_id', 'foo123');

        $widget = new Meteoprog_Informers_Widget();
        ob_start();
        $widget->widget(
            ['before_widget' => '<section>', 'after_widget' => '</section>'],
            [] // no instance ID
        );
        $output = ob_get_clean();

        $this->assertStringContainsString('<div>Informer HTML</div>', $output);

        delete_option('meteoprog_default_informer_id');
    }
}
