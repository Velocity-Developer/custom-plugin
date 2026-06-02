<?php

namespace CustomPlugin\Frontend;

use CustomPlugin\Admin\Admin;
use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Shortcode
 * 
 * Best practice for Shortcode implementation.
 */
class Shortcode
{
    const ORDER_SHORTCODE = 'custom_order_form';
    const PRICE_SHORTCODE = 'custom_product_price';
    const ORDER_NONCE_ACTION = 'custom_plugin_submit_order';
    const ORDER_NONCE_NAME = 'custom_plugin_order_form_nonce';

    public function __construct()
    {
        add_shortcode(self::ORDER_SHORTCODE, array($this, 'order_form_shortcode'));
        add_shortcode(self::PRICE_SHORTCODE, array($this, 'product_price_shortcode'));
        add_action('init', array($this, 'handle_order_submission'));
    }

    /**
     * Shortcode: [custom_hello name="User"]
     * 
     * @param array $atts
     * @return string
     */
    public function hello_shortcode($atts)
    {
        // 1. Define default attributes and merge with user inputs
        $atts = shortcode_atts(
            array(
                'name' => 'User',
                'color' => 'blue'
            ),
            $atts,
            'custom_hello'
        );

        // 2. Data to pass to template (Logic)
        $data = array(
            'name'  => sanitize_text_field($atts['name']),
            'color' => sanitize_hex_color($atts['color']) ?: 'blue'
        );

        // 3. Render using Template Engine (Separation of Concerns)
        return Template::get('frontend/hello-message', $data);
    }

    public function order_form_shortcode($atts)
    {
        $products = get_posts(array(
            'post_type'      => 'produk',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ));

        $product_options = array();
        foreach ($products as $product) {
            $price = get_post_meta($product->ID, '_product_price', true);
            $product_options[] = array(
                'id'    => $product->ID,
                'title' => $product->post_title,
                'price' => $price,
            );
        }

        return Template::get('frontend/order-form', array(
            'products'      => $product_options,
            'nonce_action'  => self::ORDER_NONCE_ACTION,
            'nonce_name'    => self::ORDER_NONCE_NAME,
            'feedback'      => $this->get_feedback_message(),
            'old'           => $this->get_old_input(),
            'store_settings' => Admin::get_store_settings(),
            'payment_methods' => array(
                'bank_transfer'  => 'Transfer Bank',
                'digital_wallet' => 'QRIS / Dompet Digital',
                'cod'            => 'COD',
            ),
        ));
    }

    /**
     * Shortcode: [custom_product_price id="123" before="" after=""]
     *
     * If `id` is not provided, it will try to use the current product post ID.
     *
     * @param array $atts
     * @return string
     */
    public function product_price_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'id'     => 0,
                'before' => '',
                'after'  => '',
            ),
            $atts,
            self::PRICE_SHORTCODE
        );

        $product_id = absint($atts['id']);
        if ($product_id < 1 && get_post_type(get_the_ID()) === 'produk') {
            $product_id = get_the_ID();
        }

        if ($product_id < 1) {
            return '';
        }

        $price = get_post_meta($product_id, '_product_price', true);
        if ($price === '') {
            return '';
        }

        return esc_html($atts['before']) . Frontend::get_formatted_price((int) $price) . esc_html($atts['after']);
    }

    public function handle_order_submission()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['custom_plugin_frontend_action']) || $_POST['custom_plugin_frontend_action'] !== 'submit_order') {
            return;
        }

        if (!isset($_POST[self::ORDER_NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::ORDER_NONCE_NAME])), self::ORDER_NONCE_ACTION)) {
            $this->redirect_with_feedback('invalid_nonce');
        }

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? max(1, absint($_POST['quantity'])) : 1;
        $customer_name = isset($_POST['customer_name']) ? sanitize_text_field(wp_unslash($_POST['customer_name'])) : '';
        $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field(wp_unslash($_POST['customer_phone'])) : '';
        $customer_address = isset($_POST['customer_address']) ? sanitize_textarea_field(wp_unslash($_POST['customer_address'])) : '';
        $customer_gps = isset($_POST['customer_gps']) ? sanitize_text_field(wp_unslash($_POST['customer_gps'])) : '';
        $delivery_date = isset($_POST['delivery_date']) ? sanitize_text_field(wp_unslash($_POST['delivery_date'])) : '';
        $delivery_time = isset($_POST['delivery_time']) ? sanitize_text_field(wp_unslash($_POST['delivery_time'])) : '';
        $payment_method = isset($_POST['payment_method']) ? sanitize_text_field(wp_unslash($_POST['payment_method'])) : '';

        if (
            $product_id < 1 ||
            $customer_name === '' ||
            $customer_phone === '' ||
            $customer_address === '' ||
            $delivery_date === '' ||
            $delivery_time === '' ||
            !in_array($payment_method, array('bank_transfer', 'digital_wallet', 'cod'), true)
        ) {
            $this->redirect_with_feedback('missing_fields');
        }

        $product = get_post($product_id);
        if (!$product || $product->post_type !== 'produk' || $product->post_status !== 'publish') {
            $this->redirect_with_feedback('invalid_product');
        }

        $price = get_post_meta($product_id, '_product_price', true);
        $unit_price = absint($price);
        $total_price = $unit_price * $quantity;

        $order_id = wp_insert_post(array(
            'post_type'   => 'order',
            'post_status' => 'publish',
            'post_title'  => sprintf('Order %s - %s', current_time('YmdHis'), $customer_name),
        ), true);

        if (is_wp_error($order_id) || !$order_id) {
            $this->redirect_with_feedback('create_failed');
        }

        update_post_meta($order_id, '_order_customer_name', $customer_name);
        update_post_meta($order_id, '_order_customer_phone', $customer_phone);
        update_post_meta($order_id, '_order_customer_address', $customer_address);
        update_post_meta($order_id, '_order_customer_gps', $customer_gps);
        update_post_meta($order_id, '_order_delivery_date', $delivery_date);
        update_post_meta($order_id, '_order_delivery_time', $delivery_time);
        update_post_meta($order_id, '_order_payment_method', $payment_method);
        update_post_meta($order_id, '_order_order_status', 'pending');
        update_post_meta($order_id, '_order_product_id', (string) $product_id);
        update_post_meta($order_id, '_order_product_name', $product->post_title);
        update_post_meta($order_id, '_order_product_quantity', (string) $quantity);
        update_post_meta($order_id, '_order_product_unit_price', (string) $unit_price);
        update_post_meta($order_id, '_order_total_price', (string) $total_price);

        $this->redirect_with_feedback('created');
    }

    private function get_feedback_message()
    {
        if (!isset($_GET['order_status'])) {
            return '';
        }

        $status = sanitize_text_field(wp_unslash($_GET['order_status']));
        $messages = array(
            'created'        => 'Order berhasil dikirim.',
            'missing_fields' => 'Mohon lengkapi semua field wajib.',
            'invalid_product' => 'Produk yang dipilih tidak valid.',
            'invalid_nonce'  => 'Sesi form tidak valid. Silakan kirim ulang.',
            'create_failed'  => 'Order gagal dibuat. Silakan coba lagi.',
        );

        return isset($messages[$status]) ? $messages[$status] : '';
    }

    private function get_old_input()
    {
        return array(
            'product_id'       => isset($_GET['product_id']) ? absint($_GET['product_id']) : 0,
            'quantity'         => isset($_GET['quantity']) ? absint($_GET['quantity']) : 1,
            'customer_name'    => isset($_GET['customer_name']) ? sanitize_text_field(wp_unslash($_GET['customer_name'])) : '',
            'customer_phone'   => isset($_GET['customer_phone']) ? sanitize_text_field(wp_unslash($_GET['customer_phone'])) : '',
            'customer_address' => isset($_GET['customer_address']) ? sanitize_textarea_field(wp_unslash($_GET['customer_address'])) : '',
            'customer_gps'     => isset($_GET['customer_gps']) ? sanitize_text_field(wp_unslash($_GET['customer_gps'])) : '',
            'delivery_date'    => isset($_GET['delivery_date']) ? sanitize_text_field(wp_unslash($_GET['delivery_date'])) : '',
            'delivery_time'    => isset($_GET['delivery_time']) ? sanitize_text_field(wp_unslash($_GET['delivery_time'])) : '',
            'payment_method'   => isset($_GET['payment_method']) ? sanitize_text_field(wp_unslash($_GET['payment_method'])) : '',
        );
    }

    private function redirect_with_feedback($status)
    {
        $redirect_url = wp_get_referer();
        if (!$redirect_url) {
            $redirect_url = home_url('/');
        }

        $args = array('order_status' => $status);
        if ($status !== 'created') {
            $fields = array(
                'product_id',
                'quantity',
                'customer_name',
                'customer_phone',
                'customer_address',
                'customer_gps',
                'delivery_date',
                'delivery_time',
                'payment_method',
            );

            foreach ($fields as $field) {
                if (isset($_POST[$field])) {
                    $args[$field] = rawurlencode(sanitize_text_field(wp_unslash($_POST[$field])));
                }
            }
        }

        wp_safe_redirect(add_query_arg($args, $redirect_url));
        exit;
    }
}
