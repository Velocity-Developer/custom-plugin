<?php

namespace CustomPlugin\Api;

if (!defined('ABSPATH')) {
    exit;
}

class SettingsController {

    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes() {
        register_rest_route('custom-plugin/v1', '/settings', array(
            array(
                'methods' => 'GET',
                'callback' => array($this, 'get_settings'),
                'permission_callback' => array($this, 'check_permission')
            ),
            array(
                'methods' => 'POST',
                'callback' => array($this, 'update_settings'),
                'permission_callback' => array($this, 'check_permission')
            )
        ));
    }

    public function check_permission() {
        return current_user_can('manage_options');
    }

    public function get_settings() {
        $settings = get_option('custom_plugin_settings', array(
            'enable_feature_1' => true,
            'enable_feature_2' => false,
            'custom_message' => 'Selamat datang di Custom Plugin!'
        ));

        // Ensure boolean types for checkboxes
        $settings['enable_feature_1'] = filter_var($settings['enable_feature_1'], FILTER_VALIDATE_BOOLEAN);
        $settings['enable_feature_2'] = filter_var($settings['enable_feature_2'], FILTER_VALIDATE_BOOLEAN);

        return rest_ensure_response($settings);
    }

    public function update_settings($request) {
        $params = $request->get_json_params();

        $settings = array(
            'enable_feature_1' => isset($params['enable_feature_1']) ? (bool) $params['enable_feature_1'] : false,
            'enable_feature_2' => isset($params['enable_feature_2']) ? (bool) $params['enable_feature_2'] : false,
            'custom_message' => isset($params['custom_message']) ? sanitize_textarea_field($params['custom_message']) : ''
        );

        update_option('custom_plugin_settings', $settings);

        return rest_ensure_response(array(
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan!',
            'settings' => $settings
        ));
    }
}
