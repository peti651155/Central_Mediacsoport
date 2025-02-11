<?php
/**
 * Plugin Name: Weather Widget Plugin
 * Description: Megjeleníti az aktuális időjárási adatokat az OpenWeather API segítségével.
 * Version: 1.0
 * Author: Peti
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Weather_Widget_Plugin {
    private $api_key = '39fcfbebbee3c502b73e6062ba8c4eb8';

    public function __construct() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_weather_request'));
        add_action('widgets_init', function() {
            register_widget('Weather_Widget');
        });
    }

    public function add_rewrite_rules() {
        add_rewrite_rule('^city/([^/]*)/?', 'index.php?city_name=$matches[1]', 'top');
        add_rewrite_tag('%city_name%', '([^/]*)');
    }

    public function handle_weather_request() {
        if (get_query_var('city_name')) {
            $city = get_query_var('city_name');
            $weather_data = $this->get_weather_data($city);
    
            if (!isset($weather_data['error'])) {
                echo "<html><head><meta charset='UTF-8'><title>Időjárás: {$city}</title></head><body>";
                echo "<h1>Időjárási adatok: {$weather_data['name']}</h1>";
                echo "<p><strong>Hőmérséklet:</strong> {$weather_data['main']['temp']}°C</p>";
                echo "<p><strong>Érzett hőmérséklet:</strong> {$weather_data['main']['feels_like']}°C</p>";
                echo "<p><strong>Leírás:</strong> " . ucfirst($weather_data['weather'][0]['description']) . "</p>";
                echo "<p><strong>Szélsebesség:</strong> {$weather_data['wind']['speed']} m/s</p>";
                echo "<p><strong>Páratartalom:</strong> {$weather_data['main']['humidity']}%</p>";
                echo "<p><strong>Légnyomás:</strong> {$weather_data['main']['pressure']} hPa</p>";
                echo "</body></html>";
            } else {
                echo "<p>Nem sikerült betölteni az időjárási adatokat.</p>";
            }
            exit;
        }
    }
    

    private function get_weather_data($city) {
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$this->api_key}&units=metric&lang=hu";
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return ['error' => 'Nem sikerült lekérni az adatokat'];
        }
        return json_decode(wp_remote_retrieve_body($response), true);
    }
}

new Weather_Widget_Plugin();

class Weather_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct('weather_widget', 'Weather Widget', array('description' => 'Megjeleníti az aktuális időjárást.'));
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo '<h3>Aktuális időjárás</h3>';
        $city = !empty($instance['city']) ? $instance['city'] : 'Budapest';
        $weather_data = (new Weather_Widget_Plugin())->get_weather_data($city);
        if (!isset($weather_data['error'])) {
            echo '<p><strong>Város:</strong> ' . $weather_data['name'] . '</p>';
            echo '<p><strong>Hőmérséklet:</strong> ' . $weather_data['main']['temp'] . '°C</p>';
            echo '<p><strong>Érzett hőmérséklet:</strong> ' . $weather_data['main']['feels_like'] . '°C</p>';
            echo '<p><strong>Leírás:</strong> ' . ucfirst($weather_data['weather'][0]['description']) . '</p>';
            echo '<p><strong>Szélsebesség:</strong> ' . $weather_data['wind']['speed'] . ' m/s</p>';
            echo '<p><strong>Páratartalom:</strong> ' . $weather_data['main']['humidity'] . '%</p>';
        } else {
            echo '<p>Nem sikerült betölteni az időjárási adatokat.</p>';
        }
        echo $args['after_widget'];
    }

    public function form($instance) {
        $city = !empty($instance['city']) ? esc_attr($instance['city']) : '';
        echo '<p><label>Város neve: <input type="text" name="' . $this->get_field_name('city') . '" value="' . $city . '" /></label></p>';
    }

    public function update($new_instance, $old_instance) {
        $instance = [];
        $instance['city'] = (!empty($new_instance['city'])) ? strip_tags($new_instance['city']) : '';
        return $instance;
    }
}
?>
