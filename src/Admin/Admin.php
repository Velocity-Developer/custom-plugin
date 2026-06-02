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
    const STORE_MENU_SLUG = 'custom-plugin-store-settings';
    const CREATE_NONCE_ACTION = 'custom_plugin_create_courier';
    const CREATE_NONCE_NAME = 'custom_plugin_create_courier_nonce';
    const DELETE_NONCE_ACTION = 'custom_plugin_delete_courier_';
    const STORE_NONCE_ACTION = 'custom_plugin_save_store_settings';
    const STORE_NONCE_NAME = 'custom_plugin_store_settings_nonce';
    const STORE_OPTION_KEY = 'custom_plugin_store_settings';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'handle_actions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Proper way to add an Admin Menu Page.
     */
    public function add_admin_menu()
    {
        add_submenu_page(
            'edit.php?post_type=produk',
            __('Pengaturan Toko', 'custom-plugin'),
            __('Toko', 'custom-plugin'),
            'manage_options',
            self::STORE_MENU_SLUG,
            array($this, 'store_settings_page')
        );

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
        if ($hook === 'produk_page_' . self::STORE_MENU_SLUG) {
            wp_enqueue_media();
            $script = <<<'JS'
(function($){
    $(document).on('click','.custom-plugin-media-button',function(e){
        e.preventDefault();
        var target = $($(this).data('target'));
        var preview = $($(this).data('preview'));
        var frame = wp.media({
            title: 'Pilih Gambar QRIS',
            button: { text: 'Gunakan gambar ini' },
            multiple: false
        });

        frame.on('select',function(){
            var attachment = frame.state().get('selection').first().toJSON();
            target.val(attachment.id);
            if (preview.length) {
                preview.attr('src', attachment.url).removeClass('d-none');
            }
        });

        frame.open();
    });

    $(document).on('click','.custom-plugin-media-clear',function(e){
        e.preventDefault();
        var target = $($(this).data('target'));
        var preview = $($(this).data('preview'));
        target.val('');
        if (preview.length) {
            preview.attr('src', '').addClass('d-none');
        }
    });
})(jQuery);
JS;
            wp_add_inline_script(
                'jquery-core',
                $script,
                'after'
            );
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

    public function store_settings_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Anda tidak memiliki akses ke halaman ini.', 'custom-plugin'));
        }

        $settings = $this->get_store_settings();
        $qris_image_url = $settings['qris_image_id'] ? wp_get_attachment_image_url((int) $settings['qris_image_id'], 'medium') : '';

        Template::render('admin/store-settings', array(
            'settings' => $settings,
            'qris_image_url' => $qris_image_url,
            'feedback' => $this->get_feedback_message(),
        ));
    }

    public function handle_actions()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (!isset($_GET['page'])) {
            return;
        }

        $page = sanitize_text_field(wp_unslash($_GET['page']));

        if ($page === self::STORE_MENU_SLUG) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom_plugin_admin_action']) && $_POST['custom_plugin_admin_action'] === 'save_store_settings') {
                $this->handle_save_store_settings();
            }
            return;
        }

        if ($page !== self::MENU_SLUG) {
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

    private function handle_save_store_settings()
    {
        if (!isset($_POST[self::STORE_NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::STORE_NONCE_NAME])), self::STORE_NONCE_ACTION)) {
            $this->redirect_with_feedback('nonce_error', self::STORE_MENU_SLUG, admin_url('edit.php?post_type=produk'));
        }

        $settings = array(
            'bank_name' => isset($_POST['bank_name']) ? sanitize_text_field(wp_unslash($_POST['bank_name'])) : '',
            'bank_account_name' => isset($_POST['bank_account_name']) ? sanitize_text_field(wp_unslash($_POST['bank_account_name'])) : '',
            'bank_account_number' => isset($_POST['bank_account_number']) ? sanitize_text_field(wp_unslash($_POST['bank_account_number'])) : '',
            'qris_image_id' => isset($_POST['qris_image_id']) ? absint($_POST['qris_image_id']) : 0,
        );

        update_option(self::STORE_OPTION_KEY, $settings);
        $this->redirect_with_feedback('saved', self::STORE_MENU_SLUG, admin_url('edit.php?post_type=produk'));
    }

    private function handle_create_courier()
    {
        if (!isset($_POST[self::CREATE_NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::CREATE_NONCE_NAME])), self::CREATE_NONCE_ACTION)) {
            $this->redirect_with_feedback('nonce_error', self::MENU_SLUG, admin_url('users.php'));
        }

        $username = isset($_POST['courier_username']) ? sanitize_user(wp_unslash($_POST['courier_username'])) : '';
        $email = isset($_POST['courier_email']) ? sanitize_email(wp_unslash($_POST['courier_email'])) : '';
        $display_name = isset($_POST['courier_display_name']) ? sanitize_text_field(wp_unslash($_POST['courier_display_name'])) : '';
        $password = isset($_POST['courier_password']) ? wp_unslash($_POST['courier_password']) : '';

        if ($username === '' || $email === '' || $password === '') {
            $this->redirect_with_feedback('missing_fields', self::MENU_SLUG, admin_url('users.php'));
        }

        if (username_exists($username) || email_exists($email)) {
            $this->redirect_with_feedback('exists', self::MENU_SLUG, admin_url('users.php'));
        }

        $user_id = wp_insert_user(array(
            'user_login'   => $username,
            'user_email'   => $email,
            'display_name' => $display_name !== '' ? $display_name : $username,
            'user_pass'    => $password,
            'role'         => Roles::COURIER_ROLE,
        ));

        if (is_wp_error($user_id)) {
            $this->redirect_with_feedback('create_failed', self::MENU_SLUG, admin_url('users.php'));
        }

        $this->redirect_with_feedback('created', self::MENU_SLUG, admin_url('users.php'));
    }

    private function handle_delete_courier()
    {
        $courier_id = absint($_GET['courier_id']);
        if ($courier_id < 1) {
            $this->redirect_with_feedback('invalid', self::MENU_SLUG, admin_url('users.php'));
        }

        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!wp_verify_nonce($nonce, self::DELETE_NONCE_ACTION . $courier_id)) {
            $this->redirect_with_feedback('nonce_error', self::MENU_SLUG, admin_url('users.php'));
        }

        $courier = get_user_by('id', $courier_id);
        if (!$courier || !in_array(Roles::COURIER_ROLE, (array) $courier->roles, true)) {
            $this->redirect_with_feedback('invalid', self::MENU_SLUG, admin_url('users.php'));
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
            $this->redirect_with_feedback('delete_failed', self::MENU_SLUG, admin_url('users.php'));
        }

        $this->redirect_with_feedback('deleted', self::MENU_SLUG, admin_url('users.php'));
    }

    private function redirect_with_feedback($status, $page, $base_url)
    {
        $url = add_query_arg(
            array(
                'page'     => $page,
                'feedback' => $status,
            ),
            $base_url
        );

        wp_safe_redirect($url);
        exit;
    }

    public static function get_store_settings()
    {
        $defaults = array(
            'bank_name' => '',
            'bank_account_name' => '',
            'bank_account_number' => '',
            'qris_image_id' => 0,
        );

        $settings = get_option(self::STORE_OPTION_KEY, array());
        return wp_parse_args($settings, $defaults);
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
