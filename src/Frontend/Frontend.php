<?php

namespace CustomPlugin\Frontend;

use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend
{

    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_head', array($this, 'add_meta_tags'));
        add_filter('the_content', array($this, 'modify_content'));
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('custom-plugin-frontend', CUSTOM_PLUGIN_URL . 'assets/frontend/css/frontend.css', array(), CUSTOM_PLUGIN_VERSION);
        wp_enqueue_script('custom-plugin-frontend', CUSTOM_PLUGIN_URL . 'assets/frontend/js/frontend.js', array('jquery'), CUSTOM_PLUGIN_VERSION, true);

        // Enqueue Alpine.js only if not already enqueued or registered
        if (!wp_script_is('alpinejs', 'enqueued') && !wp_script_is('alpine', 'enqueued') && !wp_script_is('custom-plugin-alpine', 'enqueued')) {
            wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js', array(), '3.13.3', true);
        }

        // Localize script for API
        wp_localize_script('custom-plugin-frontend', 'customPlugin', array(
            'apiUrl' => rest_url('custom-plugin/v1/contact'),
            'nonce' => wp_create_nonce('wp_rest')
        ));
    }

    public function add_meta_tags()
    {
        $settings = get_option('custom_plugin_settings', array());
        if (isset($settings['enable_feature_1']) && $settings['enable_feature_1']) {
            echo '<meta name="custom-plugin" content="enabled" />' . "\n";
        }
    }

    public function modify_content($content)
    {
        $settings = get_option('custom_plugin_settings', array());
        if (isset($settings['enable_feature_2']) && $settings['enable_feature_2']) {
            $custom_message = isset($settings['custom_message']) ? $settings['custom_message'] : '';
            if (!empty($custom_message) && is_single()) {
                $content .= '<div class="custom-plugin-message">' . esc_html($custom_message) . '</div>';
            }
        }
        return $content;
    }

    public static function display_form()
    {
        return Template::get('frontend/contact-form');
    }
}
