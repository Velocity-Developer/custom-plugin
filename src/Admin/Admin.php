<?php

namespace CustomPlugin\Admin;

use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

class Admin
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_init', array($this, 'init'));
    }

    public function init()
    {
        register_setting('custom_plugin_settings', 'custom_plugin_settings');
    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Plugin Kustom',
            'Plugin Kustom',
            'manage_options',
            'custom-plugin',
            array($this, 'admin_page'),
            'dashicons-admin-plugins',
            30
        );

        /*
        add_submenu_page(
            'custom-plugin',
            'Kiriman Formulir',
            'Kiriman',
            'manage_options',
            'custom-plugin-submissions',
            array($this, 'submissions_page')
        );
        */

        add_submenu_page(
            'custom-plugin',
            'Pengaturan',
            'Pengaturan',
            'manage_options',
            'custom-plugin-settings',
            array($this, 'settings_page')
        );
    }

    public function admin_page()
    {
        Template::render('admin/admin-page');
    }

    public function submissions_page()
    {
        Template::render('admin/submissions-page');
    }

    public function settings_page()
    {
        Template::render('admin/settings-page');
    }

    public function enqueue_scripts($hook)
    {
        if (strpos($hook, 'custom-plugin') !== false) {
            // Enqueue Alpine.js only if not already enqueued or registered
            if (!wp_script_is('alpinejs', 'enqueued') && !wp_script_is('alpine', 'enqueued') && !wp_script_is('custom-plugin-alpine', 'enqueued')) {
                wp_enqueue_script('alpinejs', 'https://unpkg.com/alpinejs@3.13.3/dist/cdn.min.js', array(), '3.13.3', true);
            }

            wp_enqueue_style('custom-plugin-admin', CUSTOM_PLUGIN_URL . 'assets/admin/css/admin.css', array(), time()); // Use time() for cache busting
        }
    }
}
