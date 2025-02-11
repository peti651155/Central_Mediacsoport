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

// Widget fájl beillesztése
require_once plugin_dir_path(__FILE__) . 'weather-widget.php';

class Weather_Widget_Plugin {
    private $api_key = '39fcfbebbee3c502b73e6062ba8c4eb8';

    public function __construct() {
        add_action('wp_enqueue_scripts', function() {
            wp_enqueue_style('weather-widget-style', plugin_dir_url(__FILE__) . 'style.css');
        });        
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'handle_weather_request'));
        add_action('widgets_init', function() {
            register_widget('Weather_Widget');
        });
    }

    // URL szabályok beállítása (/city/{városnév})
    public function add_rewrite_rules() {
        add_rewrite_rule('^city/([^/]*)/?$', 'index.php?city_name=$matches[1]', 'top');
        add_rewrite_tag('%city_name%', '([^/]*)');
    }

    // Időjárási adatok API lekérése
    public function get_weather_data($city) {
        $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid={$this->api_key}&units=metric&lang=hu";
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return ['error' => 'Nem sikerült lekérni az adatokat'];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    // Az URL alapján történő időjárás megjelenítés
    public function handle_weather_request() {
        if (get_query_var('city_name')) {
            $city = get_query_var('city_name');
            $weather_data = $this->get_weather_data($city);
    
            if (!isset($weather_data['error'])) {
                echo "<html><head><meta charset='UTF-8'><title>Időjárás: " . esc_html($city) . "</title></head><body>";
                echo "<h1>Időjárási adatok: " . esc_html($weather_data['name']) . "</h1>";
                echo "<p><strong>Hőmérséklet:</strong> " . esc_html($weather_data['main']['temp']) . "°C</p>";
                echo "<p><strong>Érzett hőmérséklet:</strong> " . esc_html($weather_data['main']['feels_like']) . "°C</p>";
                echo "<p><strong>Leírás:</strong> " . esc_html(ucfirst($weather_data['weather'][0]['description'])) . "</p>";
                echo "<p><strong>Szélsebesség:</strong> " . esc_html($weather_data['wind']['speed']) . " m/s</p>";
                echo "<p><strong>Páratartalom:</strong> " . esc_html($weather_data['main']['humidity']) . "%</p>";
                echo "<p><strong>Légnyomás:</strong> " . esc_html($weather_data['main']['pressure']) . " hPa</p>";
                echo "</body></html>";
            } else {
                echo "<p>Nem sikerült betölteni az időjárási adatokat.</p>";
            }
            exit;
        }
    }
}

// Plugin inicializálás
new Weather_Widget_Plugin();
