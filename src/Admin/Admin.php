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
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Custom Login Logo
        add_action('login_enqueue_scripts', array($this, 'custom_login_logo'));
        add_filter('login_headerurl', array($this, 'custom_login_logo_url'));
        add_filter('login_headertext', array($this, 'custom_login_logo_title'));
    }

    /**
     * Custom Login Logo based on Favicon
     */
    public function custom_login_logo()
    {
        $site_icon = get_site_icon_url(84); // 84px is standard for login logo

        if ($site_icon) :
?>
            <style type="text/css">
                #login h1 a,
                .login h1 a {
                    background-image: url(<?php echo esc_url($site_icon); ?>);
                    height: 84px;
                    width: 84px;
                    background-size: contain;
                    background-repeat: no-repeat;
                    padding-bottom: 20px;
                }
            </style>
<?php
        endif;
    }

    /**
     * Change Login Logo URL to site home
     */
    public function custom_login_logo_url()
    {
        return home_url();
    }

    /**
     * Change Login Logo Title to site name
     */
    public function custom_login_logo_title()
    {
        return get_bloginfo('name');
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
