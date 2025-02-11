<?php
class Weather_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'weather_widget',
            __('Weather Widget', 'weather-widget-plugin'),
            array('description' => __('Megjeleníti az aktuális időjárást a kiválasztott városra.', 'weather-widget-plugin'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo '<h3>' . __('Aktuális időjárás', 'weather-widget-plugin') . '</h3>';

        $city = !empty($instance['city']) ? $instance['city'] : 'Budapest';
        $weather_plugin = new Weather_Widget_Plugin();
        $weather_data = $weather_plugin->get_weather_data($city);

        if (!isset($weather_data['error'])) {
            echo '<p><strong>Város:</strong> ' . esc_html($weather_data['name']) . '</p>';
            echo '<p><strong>Hőmérséklet:</strong> ' . esc_html($weather_data['main']['temp']) . '°C</p>';
            echo '<p><strong>Érzett hőmérséklet:</strong> ' . esc_html($weather_data['main']['feels_like']) . '°C</p>';
            echo '<p><strong>Leírás:</strong> ' . esc_html(ucfirst($weather_data['weather'][0]['description'])) . '</p>';
            echo '<p><strong>Szélsebesség:</strong> ' . esc_html($weather_data['wind']['speed']) . ' m/s</p>';
            echo '<p><strong>Páratartalom:</strong> ' . esc_html($weather_data['main']['humidity']) . '%</p>';
        } else {
            echo '<p>' . __('Nem sikerült betölteni az időjárási adatokat.', 'weather-widget-plugin') . '</p>';
        }

        echo $args['after_widget'];
    }

    public function form($instance) {
        $city = !empty($instance['city']) ? esc_attr($instance['city']) : '';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('city')); ?>">
                <?php _e('Város neve:', 'weather-widget-plugin'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('city')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('city')); ?>" type="text"
                   value="<?php echo esc_attr($city); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['city'] = (!empty($new_instance['city'])) ? strip_tags($new_instance['city']) : '';
        return $instance;
    }
}
