<?php

namespace CustomPlugin\Frontend;

use CustomPlugin\Core\Roles;
use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

class CourierDashboard
{
    const SHORTCODE = 'custom_courier_dashboard';
    const NONCE_ACTION = 'custom_plugin_courier_dashboard';
    const NONCE_NAME = 'custom_plugin_courier_nonce';

    public function __construct()
    {
        add_shortcode(self::SHORTCODE, array($this, 'render_shortcode'));
        add_action('init', array($this, 'handle_form_submission'));
        add_filter('login_redirect', array($this, 'redirect_courier_after_login'), 10, 3);
        add_action('admin_init', array($this, 'block_courier_admin_access'));
    }

    public function render_shortcode()
    {
        if (!is_user_logged_in()) {
            return '<p>Silakan login sebagai kurir untuk melihat dashboard pengiriman.</p>';
        }

        $current_user = wp_get_current_user();
        if (!in_array(Roles::COURIER_ROLE, (array) $current_user->roles, true)) {
            return '<p>Halaman ini hanya tersedia untuk akun kurir.</p>';
        }

        $orders = $this->get_assigned_orders($current_user->ID);

        $items = array();
        foreach ($orders as $order) {
            $items[] = array(
                'id'                    => $order->ID,
                'title'                 => $order->post_title,
                'customer_name'         => get_post_meta($order->ID, '_order_customer_name', true),
                'customer_phone'        => get_post_meta($order->ID, '_order_customer_phone', true),
                'customer_address'      => get_post_meta($order->ID, '_order_customer_address', true),
                'customer_gps'          => get_post_meta($order->ID, '_order_customer_gps', true),
                'delivery_date'         => get_post_meta($order->ID, '_order_delivery_date', true),
                'delivery_time'         => get_post_meta($order->ID, '_order_delivery_time', true),
                'payment_method'        => get_post_meta($order->ID, '_order_payment_method', true),
                'order_status'          => get_post_meta($order->ID, '_order_order_status', true),
                'delivery_proof_url'    => get_post_meta($order->ID, '_order_delivery_proof_url', true),
                'cod_payment_proof_url' => get_post_meta($order->ID, '_order_cod_payment_proof_url', true),
            );
        }

        return Template::get('frontend/courier-dashboard', array(
            'orders'          => $items,
            'current_user'    => $current_user,
            'nonce_action'    => self::NONCE_ACTION,
            'nonce_name'      => self::NONCE_NAME,
            'payment_labels'  => $this->get_payment_labels(),
            'status_labels'   => $this->get_status_labels(),
            'courier_status_labels' => $this->get_courier_status_labels(),
            'feedback'        => $this->get_feedback_message(),
        ));
    }

    private function get_assigned_orders($courier_id)
    {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT posts.ID
                FROM {$wpdb->posts} AS posts
                INNER JOIN {$wpdb->postmeta} AS postmeta
                    ON posts.ID = postmeta.post_id
                WHERE posts.post_type = %s
                    AND posts.post_status IN ('publish', 'draft', 'pending', 'private')
                    AND postmeta.meta_key = %s
                    AND postmeta.meta_value = %s
                ORDER BY posts.post_date DESC",
                'order',
                '_order_assigned_courier_id',
                (string) $courier_id
            )
        );

        if (empty($results)) {
            return array();
        }

        $orders = array();
        foreach ($results as $result) {
            $order = get_post((int) $result->ID);
            if ($order instanceof \WP_Post) {
                $orders[] = $order;
            }
        }

        return $orders;
    }

    public function handle_form_submission()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['custom_plugin_courier_action']) || $_POST['custom_plugin_courier_action'] !== 'update_order') {
            return;
        }

        if (!is_user_logged_in()) {
            return;
        }

        $current_user = wp_get_current_user();
        if (!in_array(Roles::COURIER_ROLE, (array) $current_user->roles, true)) {
            return;
        }

        if (!isset($_POST[self::NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
            return;
        }

        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        if ($order_id < 1 || !$this->is_order_assigned_to_courier($order_id, $current_user->ID)) {
            $this->redirect_with_feedback('invalid');
        }

        $status = isset($_POST['order_status']) ? sanitize_text_field(wp_unslash($_POST['order_status'])) : '';
        if (!isset($this->get_courier_status_labels()[$status])) {
            $status = 'delivering';
        }

        update_post_meta($order_id, '_order_order_status', $status);

        $delivery_proof_url = $this->handle_upload('delivery_proof_file', '_order_delivery_proof_url', $order_id);
        if ($delivery_proof_url === false && isset($_POST['delivery_proof_url'])) {
            update_post_meta($order_id, '_order_delivery_proof_url', esc_url_raw(wp_unslash($_POST['delivery_proof_url'])));
        }

        $payment_method = get_post_meta($order_id, '_order_payment_method', true);
        $cod_payment_proof_url = $this->handle_upload('cod_payment_proof_file', '_order_cod_payment_proof_url', $order_id);
        if ($cod_payment_proof_url === false && $payment_method === 'cod' && isset($_POST['cod_payment_proof_url'])) {
            update_post_meta($order_id, '_order_cod_payment_proof_url', esc_url_raw(wp_unslash($_POST['cod_payment_proof_url'])));
        }

        $this->redirect_with_feedback('updated');
    }

    public function redirect_courier_after_login($redirect_to, $requested_redirect_to, $user)
    {
        if (!($user instanceof \WP_User)) {
            return $redirect_to;
        }

        if (!in_array(Roles::COURIER_ROLE, (array) $user->roles, true)) {
            return $redirect_to;
        }

        $dashboard_url = $this->get_dashboard_url();
        return $dashboard_url ? $dashboard_url : home_url('/');
    }

    public function block_courier_admin_access()
    {
        if (!is_user_logged_in() || wp_doing_ajax()) {
            return;
        }

        $current_user = wp_get_current_user();
        if (!in_array(Roles::COURIER_ROLE, (array) $current_user->roles, true)) {
            return;
        }

        $dashboard_url = $this->get_dashboard_url();
        wp_safe_redirect($dashboard_url ? $dashboard_url : home_url('/'));
        exit;
    }

    private function handle_upload($file_key, $meta_key, $order_id)
    {
        if (empty($_FILES[$file_key]['name'])) {
            return false;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attachment_id = media_handle_upload($file_key, $order_id);
        if (is_wp_error($attachment_id)) {
            return false;
        }

        $url = wp_get_attachment_url($attachment_id);
        if ($url) {
            update_post_meta($order_id, $meta_key, esc_url_raw($url));
        }

        return $url;
    }

    private function is_order_assigned_to_courier($order_id, $courier_id)
    {
        return (string) $courier_id === (string) get_post_meta($order_id, '_order_assigned_courier_id', true);
    }

    private function get_dashboard_url()
    {
        $pages = get_posts(array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            's'              => '[' . self::SHORTCODE . ']',
        ));

        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }

        return '';
    }

    private function redirect_with_feedback($status)
    {
        $redirect_url = wp_get_referer();
        if (!$redirect_url) {
            $redirect_url = home_url('/');
        }

        wp_safe_redirect(add_query_arg('courier_update', $status, $redirect_url));
        exit;
    }

    private function get_feedback_message()
    {
        if (!isset($_GET['courier_update'])) {
            return '';
        }

        $status = sanitize_text_field(wp_unslash($_GET['courier_update']));
        if ($status === 'updated') {
            return 'Order berhasil diperbarui.';
        }

        if ($status === 'invalid') {
            return 'Order tidak valid atau bukan milik kurir ini.';
        }

        return '';
    }

    private function get_payment_labels()
    {
        return array(
            'bank_transfer'  => 'Transfer Bank',
            'digital_wallet' => 'Transfer Dompet Digital',
            'cod'            => 'COD',
        );
    }

    private function get_status_labels()
    {
        return array(
            'pending'    => 'Pending',
            'scheduled'  => 'Terjadwal',
            'processing' => 'Diproses',
            'delivering' => 'Dalam Pengiriman',
            'delivered'  => 'Terkirim',
            'completed'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
        );
    }

    private function get_courier_status_labels()
    {
        return array(
            'delivering' => 'Dalam Pengiriman',
            'delivered'  => 'Terkirim',
        );
    }
}
