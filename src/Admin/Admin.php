<?php

namespace CustomPlugin\Admin;

use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Admin
 * 
 * Best practice for WordPress Admin Menu and Hooks.
 */
class Admin
{

    public function __construct()
    {
        // Example: Uncomment to activate admin dashboard menu.
        // add_action('admin_menu', array($this, 'add_admin_menu'));
        // add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Proper way to add an Admin Menu Page.
     */
    public function add_admin_menu()
    {
        add_menu_page(
            __('Custom Plugin', 'custom-plugin'), // Page Title
            __('Plugin Kustom', 'custom-plugin'), // Menu Title
            'manage_options',                    // Capability
            'custom-plugin',                     // Menu Slug
            array($this, 'admin_page'),          // Callback
            'dashicons-admin-plugins',           // Icon
            30                                   // Position
        );
    }

    /**
     * Proper way to enqueue scripts for Admin Dashboard only.
     * 
     * @param string $hook
     */
    public function enqueue_scripts($hook)
    {
        // Only load assets on our plugin's pages.
        if (strpos($hook, 'custom-plugin') !== false) {
            // wp_enqueue_style('custom-plugin-admin', CUSTOM_PLUGIN_URL . 'assets/admin/css/admin.css', array(), time()); // Use time() for cache busting during development
        }
    }

    /**
     * Admin Dashboard Callback.
     */
    public function admin_page()
    {
        Template::render('admin/dashboard');
    }
}
