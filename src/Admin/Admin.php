<?php

namespace CustomPlugin\Admin;

use CustomPlugin\Core\Template;
use CustomPlugin\Core\Roles;

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
    const MENU_SLUG = 'custom-plugin-couriers';
    const CREATE_NONCE_ACTION = 'custom_plugin_create_courier';
    const CREATE_NONCE_NAME = 'custom_plugin_create_courier_nonce';
    const DELETE_NONCE_ACTION = 'custom_plugin_delete_courier_';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_actions'));
    }

    /**
     * Proper way to add an Admin Menu Page.
     */
    public function add_admin_menu()
    {
        add_users_page(
            __('Kelola Kurir', 'custom-plugin'),
            __('Kelola Kurir', 'custom-plugin'),
            'manage_options',
            self::MENU_SLUG,
            array($this, 'admin_page')
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
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Anda tidak memiliki akses ke halaman ini.', 'custom-plugin'));
        }

        $couriers = get_users(array(
            'role'    => Roles::COURIER_ROLE,
            'orderby' => 'display_name',
            'order'   => 'ASC',
        ));

        Template::render('admin/courier-management', array(
            'couriers' => $couriers,
            'feedback' => $this->get_feedback_message(),
        ));
    }

    public function handle_actions()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!isset($_GET['page']) || $_GET['page'] !== self::MENU_SLUG) {
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom_plugin_admin_action']) && $_POST['custom_plugin_admin_action'] === 'create_courier') {
            $this->handle_create_courier();
            return;
        }

        if (isset($_GET['action'], $_GET['courier_id']) && $_GET['action'] === 'delete_courier') {
            $this->handle_delete_courier();
        }
    }

    private function handle_create_courier()
    {
        if (!isset($_POST[self::CREATE_NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::CREATE_NONCE_NAME])), self::CREATE_NONCE_ACTION)) {
            $this->redirect_with_feedback('nonce_error');
        }

        $username = isset($_POST['courier_username']) ? sanitize_user(wp_unslash($_POST['courier_username'])) : '';
        $email = isset($_POST['courier_email']) ? sanitize_email(wp_unslash($_POST['courier_email'])) : '';
        $display_name = isset($_POST['courier_display_name']) ? sanitize_text_field(wp_unslash($_POST['courier_display_name'])) : '';
        $password = isset($_POST['courier_password']) ? wp_unslash($_POST['courier_password']) : '';

        if ($username === '' || $email === '' || $password === '') {
            $this->redirect_with_feedback('missing_fields');
        }

        if (username_exists($username) || email_exists($email)) {
            $this->redirect_with_feedback('exists');
        }

        $user_id = wp_insert_user(array(
            'user_login'   => $username,
            'user_email'   => $email,
            'display_name' => $display_name !== '' ? $display_name : $username,
            'user_pass'    => $password,
            'role'         => Roles::COURIER_ROLE,
        ));

        if (is_wp_error($user_id)) {
            $this->redirect_with_feedback('create_failed');
        }

        $this->redirect_with_feedback('created');
    }

    private function handle_delete_courier()
    {
        $courier_id = absint($_GET['courier_id']);
        if ($courier_id < 1) {
            $this->redirect_with_feedback('invalid');
        }

        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, self::DELETE_NONCE_ACTION . $courier_id)) {
            $this->redirect_with_feedback('nonce_error');
        }

        $courier = get_user_by('id', $courier_id);
        if (!$courier || !in_array(Roles::COURIER_ROLE, (array) $courier->roles, true)) {
            $this->redirect_with_feedback('invalid');
        }

        $orders = get_posts(array(
            'post_type'      => 'order',
            'post_status'    => array('publish', 'draft', 'pending', 'private'),
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_key'       => '_order_assigned_courier_id',
            'meta_value'     => (string) $courier_id,
        ));

        foreach ($orders as $order_id) {
            delete_post_meta($order_id, '_order_assigned_courier_id');
        }

        require_once ABSPATH . 'wp-admin/includes/user.php';
        $deleted = wp_delete_user($courier_id);

        if (!$deleted) {
            $this->redirect_with_feedback('delete_failed');
        }

        $this->redirect_with_feedback('deleted');
    }

    private function redirect_with_feedback($status)
    {
        $url = add_query_arg(
            array(
                'page'     => self::MENU_SLUG,
                'feedback' => $status,
            ),
            admin_url('users.php')
        );

        wp_safe_redirect($url);
        exit;
    }

    private function get_feedback_message()
    {
        if (!isset($_GET['feedback'])) {
            return '';
        }

        $status = sanitize_text_field(wp_unslash($_GET['feedback']));
        $messages = array(
            'created'       => 'Kurir berhasil ditambahkan.',
            'deleted'       => 'Kurir berhasil dihapus.',
            'missing_fields' => 'Username, email, dan password wajib diisi.',
            'exists'        => 'Username atau email sudah digunakan.',
            'invalid'       => 'Data kurir tidak valid.',
            'nonce_error'   => 'Permintaan tidak valid. Silakan coba lagi.',
            'create_failed' => 'Kurir gagal dibuat.',
            'delete_failed' => 'Kurir gagal dihapus.',
        );

        return isset($messages[$status]) ? $messages[$status] : '';
    }
}
