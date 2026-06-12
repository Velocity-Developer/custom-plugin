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
    const BUY_BUTTON_SHORTCODE = 'custom_buy_button';
    const CUSTOMER_REGISTRATION_SHORTCODE = 'custom_customer_registration';
    const CUSTOMER_PROFILE_SHORTCODE = 'custom_customer_profile';
    const ORDER_NONCE_ACTION = 'custom_plugin_submit_order';
    const ORDER_NONCE_NAME = 'custom_plugin_order_form_nonce';
    const CUSTOMER_NONCE_ACTION = 'custom_plugin_submit_customer_profile';
    const CUSTOMER_NONCE_NAME = 'custom_plugin_customer_form_nonce';
    const CUSTOMER_AUTH_NONCE_ACTION = 'custom_plugin_customer_auth';
    const CUSTOMER_AUTH_NONCE_NAME = 'custom_plugin_customer_auth_nonce';

    public function __construct()
    {
        add_shortcode(self::ORDER_SHORTCODE, array($this, 'order_form_shortcode'));
        add_shortcode(self::PRICE_SHORTCODE, array($this, 'product_price_shortcode'));
        add_shortcode(self::BUY_BUTTON_SHORTCODE, array($this, 'buy_button_shortcode'));
        add_shortcode(self::CUSTOMER_REGISTRATION_SHORTCODE, array($this, 'customer_registration_shortcode'));
        add_shortcode(self::CUSTOMER_PROFILE_SHORTCODE, array($this, 'customer_profile_shortcode'));
        add_action('init', array($this, 'handle_order_submission'));
        add_action('init', array($this, 'handle_customer_submission'));
        add_action('init', array($this, 'handle_customer_registration_account'));
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
        $city_options = $this->get_customer_city_options();
        foreach ($products as $product) {
            $base_price = get_post_meta($product->ID, '_product_price', true);
            $city_prices = array();

            foreach ($city_options as $city_option) {
                $meta_key = $this->get_product_city_price_meta_key($city_option['value']);
                $city_prices[$city_option['value']] = $meta_key !== '' ? get_post_meta($product->ID, $meta_key, true) : '';
            }

            $product_options[] = array(
                'id'          => $product->ID,
                'title'       => $product->post_title,
                'price'       => $base_price,
                'city_prices' => $city_prices,
            );
        }

        $old = $this->get_old_input();
        $current_user_id = get_current_user_id();
        $is_logged_in = $current_user_id > 0;
        $logged_in_city = '';
        $logged_in_email = '';
        $lock_fields = array(
            'customer_name'    => false,
            'customer_address' => false,
            'customer_email'   => false,
            'customer_phone'   => false,
        );

        if ($is_logged_in) {
            $current_user = wp_get_current_user();
            $logged_in_email = (string) $current_user->user_email;
            $logged_in_city = (string) get_user_meta($current_user_id, '_customer_city', true);
            $profile_name = (string) get_user_meta($current_user_id, '_customer_full_name', true);
            $profile_address = (string) get_user_meta($current_user_id, '_customer_address', true);
            $profile_whatsapp = (string) get_user_meta($current_user_id, '_customer_whatsapp', true);
            $profile_phone = (string) get_user_meta($current_user_id, '_customer_phone', true);

            if ($old['customer_name'] === '') {
                $old['customer_name'] = $profile_name !== '' ? $profile_name : (string) $current_user->display_name;
            }

            if ($old['customer_address'] === '' && $profile_address !== '') {
                $old['customer_address'] = $profile_address;
            }

            if ($old['customer_phone'] === '') {
                if ($profile_whatsapp !== '') {
                    $old['customer_phone'] = $profile_whatsapp;
                } elseif ($profile_phone !== '') {
                    $old['customer_phone'] = $profile_phone;
                }
            }

            if ($old['customer_city'] === '' && $logged_in_city !== '') {
                $old['customer_city'] = $logged_in_city;
            }

            $lock_fields['customer_name'] = $profile_name !== '';
            $lock_fields['customer_address'] = $profile_address !== '';
            $lock_fields['customer_email'] = $logged_in_email !== '';
            $lock_fields['customer_phone'] = $profile_whatsapp !== '';
        }

        return Template::get('frontend/order-form', array(
            'products'          => $product_options,
            'nonce_action'      => self::ORDER_NONCE_ACTION,
            'nonce_name'        => self::ORDER_NONCE_NAME,
            'feedback'          => $this->get_feedback_message(),
            'old'               => $old,
            'store_settings'    => Admin::get_store_settings(),
            'delivery_time_options' => $this->get_delivery_time_options(),
            'payment_methods' => array(
                'bank_transfer'  => 'Transfer Bank',
                'digital_wallet' => 'QRIS / Dompet Digital',
                'cod'            => 'COD',
            ),
            'city_options'      => $city_options,
            'logged_in_city'    => $logged_in_city,
            'logged_in_email'   => $logged_in_email,
            'lock_fields'       => $lock_fields,
            'is_logged_in'      => $is_logged_in,
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
        if ($product_id < 1) {
            $product_id = $this->resolve_current_product_id();
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

    /**
     * Shortcode: [custom_buy_button id="123" text="Beli Sekarang"]
     *
     * If `id` is not provided, it will try to use the current product post ID.
     *
     * @param array $atts
     * @return string
     */
    public function buy_button_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(
                'id'    => 0,
                'text'  => 'Beli Sekarang',
                'url'   => 'https://tinelogas.com/order/',
                'class' => '',
            ),
            $atts,
            self::BUY_BUTTON_SHORTCODE
        );

        $product_id = absint($atts['id']);
        if ($product_id < 1) {
            $product_id = $this->resolve_current_product_id();
        }

        if ($product_id < 1) {
            return '';
        }

        $order_url = add_query_arg(
            array('product_id' => $product_id),
            esc_url_raw($atts['url'])
        );

        $button_classes = trim('btn btn-lg custom-plugin-buy-button ' . $atts['class']);

        return sprintf(
            '<a class="%1$s" href="%2$s">%3$s</a>',
            esc_attr($button_classes),
            esc_url($order_url),
            esc_html($atts['text'])
        ) . $this->get_buy_button_styles();
    }

    public function customer_registration_shortcode($atts)
    {
        if (!is_user_logged_in()) {
            return $this->get_customer_auth_panel('Silakan login atau buat akun customer terlebih dulu.');
        }

        $current_user = wp_get_current_user();

        return Template::get('frontend/customer-registration', array(
            'profile'       => $this->get_customer_profile_data($current_user->ID),
            'city_options'  => $this->get_customer_city_options(),
            'nonce_action'  => self::CUSTOMER_NONCE_ACTION,
            'nonce_name'    => self::CUSTOMER_NONCE_NAME,
            'feedback'      => $this->get_customer_feedback_message(),
            'form_title'    => 'Pendaftaran Customer',
            'form_subtitle' => 'Lengkapi data customer Anda untuk kebutuhan pengiriman dan verifikasi.',
            'submit_label'  => 'Simpan Pendaftaran',
        ));
    }

    public function customer_profile_shortcode($atts)
    {
        if (!is_user_logged_in()) {
            return $this->get_customer_auth_panel('Silakan login atau daftar akun customer untuk melihat profile.');
        }

        $current_user = wp_get_current_user();

        return Template::get('frontend/customer-profile', array(
            'profile'       => $this->get_customer_profile_data($current_user->ID),
            'orders'        => $this->get_customer_orders($current_user->ID),
            'city_options'  => $this->get_customer_city_options(),
            'nonce_action'  => self::CUSTOMER_NONCE_ACTION,
            'nonce_name'    => self::CUSTOMER_NONCE_NAME,
            'feedback'      => $this->get_customer_feedback_message(),
            'form_title'    => 'Profile Customer',
            'form_subtitle' => 'Perbarui data customer Anda kapan saja dari halaman ini.',
            'submit_label'  => 'Update Profile',
            'payment_labels' => $this->get_payment_method_labels(),
            'status_labels'  => $this->get_order_status_labels(),
        ));
    }

    private function resolve_current_product_id()
    {
        $queried_object_id = get_queried_object_id();
        if ($queried_object_id && get_post_type($queried_object_id) === 'produk') {
            return (int) $queried_object_id;
        }

        $current_post_id = get_the_ID();
        if ($current_post_id && get_post_type($current_post_id) === 'produk') {
            return (int) $current_post_id;
        }

        global $post;
        if ($post instanceof \WP_Post && $post->post_type === 'produk') {
            return (int) $post->ID;
        }

        return 0;
    }

    private function get_buy_button_styles()
    {
        static $styles_rendered = false;

        if ($styles_rendered) {
            return '';
        }

        $styles_rendered = true;

        return '<style>
            .custom-plugin-buy-button {
                --bs-btn-color: #fff;
                --bs-btn-bg: #F83C89;
                --bs-btn-border-color: #F83C89;
                --bs-btn-hover-color: #fff;
                --bs-btn-hover-bg: #e2357c;
                --bs-btn-hover-border-color: #e2357c;
                --bs-btn-focus-shadow-rgb: 248, 60, 137;
                --bs-btn-active-color: #fff;
                --bs-btn-active-bg: #d82f73;
                --bs-btn-active-border-color: #d82f73;
                --bs-btn-disabled-color: #fff;
                --bs-btn-disabled-bg: #F83C89;
                --bs-btn-disabled-border-color: #F83C89;
            }
        </style>';
    }

    private function get_delivery_time_options()
    {
        return array(
            '08.00 - 09:00',
            '09.01 - 10.00',
            '10.01 - 11.00',
            '11.01 - 12.00',
            '12.01 - 13.00',
            '13.01 - 14.00',
            '14.01 - 15.00',
            '15.01 - 16.00',
            '16.01 - 17.00',
        );
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
        $customer_email_input = isset($_POST['customer_email']) ? sanitize_email(wp_unslash($_POST['customer_email'])) : '';
        $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field(wp_unslash($_POST['customer_phone'])) : '';
        $customer_address = isset($_POST['customer_address']) ? sanitize_textarea_field(wp_unslash($_POST['customer_address'])) : '';
        $customer_gps = isset($_POST['customer_gps']) ? sanitize_text_field(wp_unslash($_POST['customer_gps'])) : '';
        $delivery_date = isset($_POST['delivery_date']) ? sanitize_text_field(wp_unslash($_POST['delivery_date'])) : '';
        $delivery_time = isset($_POST['delivery_time']) ? sanitize_text_field(wp_unslash($_POST['delivery_time'])) : '';
        $payment_method = isset($_POST['payment_method']) ? sanitize_text_field(wp_unslash($_POST['payment_method'])) : '';
        $customer_city = isset($_POST['customer_city']) ? sanitize_text_field(wp_unslash($_POST['customer_city'])) : '';
        $customer_user_id = get_current_user_id();
        $is_logged_in = $customer_user_id > 0;
        $customer_email = '';
        $customer_username = '';
        $city_values = wp_list_pluck($this->get_customer_city_options(), 'value');
        $delivery_time_options = $this->get_delivery_time_options();

        if ($is_logged_in) {
            $current_user = wp_get_current_user();
            $customer_email = (string) $current_user->user_email;
            $customer_username = (string) $current_user->user_login;

            $profile_name = (string) get_user_meta($customer_user_id, '_customer_full_name', true);
            $profile_address = (string) get_user_meta($customer_user_id, '_customer_address', true);
            $profile_city = (string) get_user_meta($customer_user_id, '_customer_city', true);

            if ($profile_name !== '') {
                $customer_name = $profile_name;
            }

            if ($profile_address !== '') {
                $customer_address = $profile_address;
            }

            if ($profile_city !== '') {
                $customer_city = $profile_city;
            }
        } else {
            $customer_email = $customer_email_input;
        }

        if (!$is_logged_in && $customer_email !== '' && !is_email($customer_email)) {
            $this->redirect_with_feedback('missing_fields');
        }

        if (
            $product_id < 1 ||
            $customer_name === '' ||
            (!$is_logged_in && $customer_email === '') ||
            $customer_phone === '' ||
            $customer_address === '' ||
            $customer_city === '' ||
            $delivery_date === '' ||
            $delivery_time === '' ||
            !in_array($delivery_time, $delivery_time_options, true) ||
            !in_array($payment_method, array('bank_transfer', 'digital_wallet', 'cod'), true) ||
            !in_array($customer_city, $city_values, true)
        ) {
            $this->redirect_with_feedback('missing_fields');
        }

        if ($is_logged_in && $customer_phone !== '') {
            update_user_meta($customer_user_id, '_customer_whatsapp', $customer_phone);
            update_user_meta($customer_user_id, '_customer_phone', $customer_phone);
        }

        $product = get_post($product_id);
        if (!$product || $product->post_type !== 'produk' || $product->post_status !== 'publish') {
            $this->redirect_with_feedback('invalid_product');
        }

        $unit_price = $this->get_product_price_for_city($product_id, $customer_city);
        $total_price = $unit_price * $quantity;

        $order_id = wp_insert_post(array(
            'post_type'   => 'order',
            'post_status' => 'publish',
            'post_title'  => sprintf('Order %s - %s', current_time('YmdHis'), $customer_name),
            'post_author' => $customer_user_id,
        ), true);

        if (is_wp_error($order_id) || !$order_id) {
            $this->redirect_with_feedback('create_failed');
        }

        update_post_meta($order_id, '_order_customer_name', $customer_name);
        update_post_meta($order_id, '_order_customer_phone', $customer_phone);
        update_post_meta($order_id, '_order_customer_address', $customer_address);
        update_post_meta($order_id, '_order_customer_city', $customer_city);
        update_post_meta($order_id, '_order_customer_gps', $customer_gps);
        update_post_meta($order_id, '_order_customer_email', $customer_email);
        update_post_meta($order_id, '_order_customer_username', $customer_username);
        update_post_meta($order_id, '_order_delivery_date', $delivery_date);
        update_post_meta($order_id, '_order_delivery_time', $delivery_time);
        update_post_meta($order_id, '_order_payment_method', $payment_method);
        update_post_meta($order_id, '_order_order_status', 'pending');
        update_post_meta($order_id, '_order_product_id', (string) $product_id);
        update_post_meta($order_id, '_order_product_name', $product->post_title);
        update_post_meta($order_id, '_order_product_quantity', (string) $quantity);
        update_post_meta($order_id, '_order_product_unit_price', (string) $unit_price);
        update_post_meta($order_id, '_order_total_price', (string) $total_price);
        if ($is_logged_in) {
            update_post_meta($order_id, '_order_customer_user_id', (string) $customer_user_id);
        }

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
        $defaults = array(
            'product_id'       => isset($_GET['product_id']) ? absint($_GET['product_id']) : 0,
            'quantity'         => isset($_GET['quantity']) ? absint($_GET['quantity']) : 1,
            'customer_name'    => isset($_GET['customer_name']) ? sanitize_text_field(wp_unslash($_GET['customer_name'])) : '',
            'customer_email'   => isset($_GET['customer_email']) ? sanitize_email(wp_unslash($_GET['customer_email'])) : '',
            'customer_phone'   => isset($_GET['customer_phone']) ? sanitize_text_field(wp_unslash($_GET['customer_phone'])) : '',
            'customer_address' => isset($_GET['customer_address']) ? sanitize_textarea_field(wp_unslash($_GET['customer_address'])) : '',
            'customer_city'    => isset($_GET['customer_city']) ? sanitize_text_field(wp_unslash($_GET['customer_city'])) : '',
            'customer_gps'     => isset($_GET['customer_gps']) ? sanitize_text_field(wp_unslash($_GET['customer_gps'])) : '',
            'delivery_date'    => isset($_GET['delivery_date']) ? sanitize_text_field(wp_unslash($_GET['delivery_date'])) : '',
            'delivery_time'    => isset($_GET['delivery_time']) ? sanitize_text_field(wp_unslash($_GET['delivery_time'])) : '',
            'payment_method'   => isset($_GET['payment_method']) ? sanitize_text_field(wp_unslash($_GET['payment_method'])) : '',
        );

        if ($defaults['delivery_date'] === '') {
            $defaults['delivery_date'] = current_time('Y-m-d');
        }

        if ($defaults['delivery_time'] === '') {
            $delivery_time_options = $this->get_delivery_time_options();
            $defaults['delivery_time'] = $delivery_time_options[0];
        }

        return $defaults;
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
                'customer_email',
                'customer_phone',
                'customer_address',
                'customer_city',
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

    public function handle_customer_submission()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['custom_plugin_frontend_action']) || $_POST['custom_plugin_frontend_action'] !== 'submit_customer_profile') {
            return;
        }

        if (!is_user_logged_in()) {
            $this->redirect_customer_with_feedback('login_required');
        }

        if (!isset($_POST[self::CUSTOMER_NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::CUSTOMER_NONCE_NAME])), self::CUSTOMER_NONCE_ACTION)) {
            $this->redirect_customer_with_feedback('invalid_nonce');
        }

        $current_user = wp_get_current_user();
        $full_name = isset($_POST['customer_full_name']) ? sanitize_text_field(wp_unslash($_POST['customer_full_name'])) : '';
        $whatsapp = isset($_POST['customer_whatsapp']) ? sanitize_text_field(wp_unslash($_POST['customer_whatsapp'])) : '';
        $address = isset($_POST['customer_address']) ? sanitize_textarea_field(wp_unslash($_POST['customer_address'])) : '';
        $city = isset($_POST['customer_city']) ? sanitize_text_field(wp_unslash($_POST['customer_city'])) : '';
        $city_options = $this->get_customer_city_options();
        $city_values = wp_list_pluck($city_options, 'value');
        $current_ktp_id = (int) get_user_meta($current_user->ID, '_customer_ktp_attachment_id', true);

        if ($full_name === '' || $whatsapp === '' || $address === '' || $city === '' || !in_array($city, $city_values, true)) {
            $this->redirect_customer_with_feedback('missing_fields');
        }

        $ktp_attachment_id = $current_ktp_id;
        if (!empty($_FILES['customer_ktp_photo']['name'])) {
            $ktp_attachment_id = $this->handle_customer_ktp_upload('customer_ktp_photo');
            if ($ktp_attachment_id < 1) {
                $this->redirect_customer_with_feedback('upload_failed');
            }
        } elseif ($current_ktp_id < 1) {
            $this->redirect_customer_with_feedback('missing_ktp');
        }

        update_user_meta($current_user->ID, '_customer_full_name', $full_name);
        update_user_meta($current_user->ID, '_customer_whatsapp', $whatsapp);
        update_user_meta($current_user->ID, '_customer_phone', $whatsapp);
        update_user_meta($current_user->ID, '_customer_address', $address);
        update_user_meta($current_user->ID, '_customer_city', $city);
        update_user_meta($current_user->ID, '_customer_ktp_attachment_id', $ktp_attachment_id);

        wp_update_user(array(
            'ID'           => $current_user->ID,
            'display_name' => $full_name,
        ));

        if (!in_array('customer', (array) $current_user->roles, true)) {
            $current_user->add_role('customer');
        }

        $this->redirect_customer_with_feedback('saved');
    }

    public function handle_customer_registration_account()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['custom_plugin_frontend_action']) || $_POST['custom_plugin_frontend_action'] !== 'register_customer_account') {
            return;
        }

        if (!isset($_POST[self::CUSTOMER_AUTH_NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::CUSTOMER_AUTH_NONCE_NAME])), self::CUSTOMER_AUTH_NONCE_ACTION)) {
            $this->redirect_customer_auth('invalid_nonce');
        }

        $full_name = isset($_POST['register_full_name']) ? sanitize_text_field(wp_unslash($_POST['register_full_name'])) : '';
        $email = isset($_POST['register_email']) ? sanitize_email(wp_unslash($_POST['register_email'])) : '';
        $whatsapp = isset($_POST['register_whatsapp']) ? sanitize_text_field(wp_unslash($_POST['register_whatsapp'])) : '';
        $address = isset($_POST['register_address']) ? sanitize_textarea_field(wp_unslash($_POST['register_address'])) : '';
        $password = isset($_POST['register_password']) ? (string) wp_unslash($_POST['register_password']) : '';
        $city = isset($_POST['register_city']) ? sanitize_text_field(wp_unslash($_POST['register_city'])) : '';
        $city_values = wp_list_pluck($this->get_customer_city_options(), 'value');

        if ($full_name === '' || $email === '' || $whatsapp === '' || $address === '' || $password === '' || $city === '' || !in_array($city, $city_values, true)) {
            $this->redirect_customer_auth('missing_fields');
        }

        if (!is_email($email)) {
            $this->redirect_customer_auth('invalid_email');
        }

        if (email_exists($email)) {
            $this->redirect_customer_auth('email_exists');
        }

        if (strlen($password) < 6) {
            $this->redirect_customer_auth('weak_password');
        }

        $username = $this->generate_customer_username($email);
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id) || !$user_id) {
            $this->redirect_customer_auth('register_failed');
        }

        wp_update_user(array(
            'ID'           => $user_id,
            'display_name' => $full_name,
        ));

        update_user_meta($user_id, '_customer_full_name', $full_name);
        update_user_meta($user_id, '_customer_whatsapp', $whatsapp);
        update_user_meta($user_id, '_customer_phone', $whatsapp);
        update_user_meta($user_id, '_customer_address', $address);
        update_user_meta($user_id, '_customer_city', $city);

        $user = get_user_by('id', $user_id);
        if ($user instanceof \WP_User) {
            $user->add_role('customer');
        }

        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);

        $this->redirect_customer_auth('registered');
    }

    private function handle_customer_ktp_upload($field_name)
    {
        if (!function_exists('media_handle_upload')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        $attachment_id = media_handle_upload($field_name, 0);
        if (is_wp_error($attachment_id)) {
            return 0;
        }

        return (int) $attachment_id;
    }

    private function get_customer_profile_data($user_id)
    {
        $city = (string) get_user_meta($user_id, '_customer_city', true);
        $ktp_attachment_id = (int) get_user_meta($user_id, '_customer_ktp_attachment_id', true);

        return array(
            'full_name'         => (string) get_user_meta($user_id, '_customer_full_name', true),
            'whatsapp'          => (string) get_user_meta($user_id, '_customer_whatsapp', true),
            'address'           => (string) get_user_meta($user_id, '_customer_address', true),
            'city'              => $city,
            'city_label'        => $this->get_customer_city_label($city),
            'ktp_attachment_id' => $ktp_attachment_id,
            'ktp_image_html'    => $ktp_attachment_id > 0 ? wp_get_attachment_image($ktp_attachment_id, 'medium', false, array('class' => 'img-fluid rounded border', 'alt' => 'Foto KTP')) : '',
            'has_customer_role' => in_array('customer', (array) get_userdata($user_id)->roles, true),
        );
    }

    private function get_customer_city_options()
    {
        return array(
            array(
                'value' => 'kota-gorontalo',
                'label' => 'Kota Gorontalo',
            ),
            array(
                'value' => 'kabupaten-gorontalo',
                'label' => 'Kabupaten Gorontalo',
            ),
            array(
                'value' => 'kabupaten-gorontalo-utara',
                'label' => 'Kabupaten Gorontalo Utara',
            ),
            array(
                'value' => 'kabupaten-boalemo',
                'label' => 'Kabupaten Boalemo',
            ),
            array(
                'value' => 'kabupaten-pohuwato',
                'label' => 'Kabupaten Pohuwato',
            ),
            array(
                'value' => 'kabupaten-bone-bolango',
                'label' => 'Kabupaten Bone Bolango',
            ),
        );
    }

    private function get_customer_city_label($value)
    {
        foreach ($this->get_customer_city_options() as $city) {
            if ($city['value'] === $value) {
                return $city['label'];
            }
        }

        return '';
    }

    private function get_product_price_for_city($product_id, $city)
    {
        $base_price = absint(get_post_meta($product_id, '_product_price', true));
        $meta_key = $this->get_product_city_price_meta_key($city);
        if ($meta_key === '') {
            return $base_price;
        }

        $city_price = get_post_meta($product_id, $meta_key, true);
        if ($city_price === '') {
            return $base_price;
        }

        return absint($city_price);
    }

    private function get_product_city_price_meta_key($city)
    {
        $map = array(
            'kota-gorontalo' => '_product_price_kota_gorontalo',
            'kabupaten-gorontalo' => '_product_price_kabupaten_gorontalo',
            'kabupaten-gorontalo-utara' => '_product_price_kabupaten_gorontalo_utara',
            'kabupaten-boalemo' => '_product_price_kabupaten_boalemo',
            'kabupaten-pohuwato' => '_product_price_kabupaten_pohuwato',
            'kabupaten-bone-bolango' => '_product_price_kabupaten_bone_olango',
        );

        return isset($map[$city]) ? $map[$city] : '';
    }

    private function get_customer_feedback_message()
    {
        if (!isset($_GET['customer_status'])) {
            return '';
        }

        $status = sanitize_text_field(wp_unslash($_GET['customer_status']));
        $messages = array(
            'saved'          => 'Data customer berhasil disimpan.',
            'missing_fields' => 'Mohon lengkapi semua field wajib.',
            'missing_ktp'    => 'Foto KTP wajib diunggah.',
            'upload_failed'  => 'Upload foto KTP gagal. Silakan coba lagi.',
            'invalid_nonce'  => 'Sesi form tidak valid. Silakan kirim ulang.',
            'login_required' => 'Anda harus login untuk mengakses form customer.',
        );

        return isset($messages[$status]) ? $messages[$status] : '';
    }

    private function get_customer_auth_feedback_message()
    {
        if (!isset($_GET['customer_auth_status'])) {
            return '';
        }

        $status = sanitize_text_field(wp_unslash($_GET['customer_auth_status']));
        $messages = array(
            'registered'     => 'Akun customer berhasil dibuat dan Anda sudah login.',
            'missing_fields' => 'Mohon lengkapi semua field register.',
            'invalid_email'  => 'Email yang dimasukkan tidak valid.',
            'email_exists'   => 'Email sudah terdaftar. Silakan login.',
            'weak_password'  => 'Password minimal 6 karakter.',
            'invalid_nonce'  => 'Sesi form tidak valid. Silakan coba lagi.',
            'register_failed' => 'Pendaftaran akun gagal. Silakan coba lagi.',
        );

        return isset($messages[$status]) ? $messages[$status] : '';
    }

    private function get_customer_orders($user_id)
    {
        $user = get_user_by('id', $user_id);
        if (!$user instanceof \WP_User) {
            return array();
        }

        global $wpdb;

        $order_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT p.ID
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
                WHERE p.post_type = %s
                AND p.post_status NOT IN ('trash', 'auto-draft', 'inherit')
                AND pm.meta_key = %s
                AND pm.meta_value = %s
                ORDER BY p.post_date DESC
                LIMIT 200",
                'order',
                '_order_customer_user_id',
                (string) $user_id
            )
        );

        if (empty($order_ids)) {
            return array();
        }

        $items = array();
        foreach ($order_ids as $order_id) {
            $order = get_post((int) $order_id);
            if (!$order instanceof \WP_Post || $order->post_type !== 'order') {
                continue;
            }
            $items[] = $this->build_customer_order_item($order);
        }

        return $items;
    }

    private function get_matching_customer_order_ids($user_id, \WP_User $user, $customer_full_name, $customer_display_name)
    {
        global $wpdb;

        $order_ids = array();

        $ids_by_meta_user = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT post_id
                FROM {$wpdb->postmeta}
                WHERE meta_key = %s
                AND meta_value = %s",
                '_order_customer_user_id',
                (string) $user_id
            )
        );

        if (!empty($ids_by_meta_user)) {
            $order_ids = array_merge($order_ids, $ids_by_meta_user);
        }

        $ids_by_author = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID
                FROM {$wpdb->posts}
                WHERE post_type = %s
                AND post_author = %d
                AND post_status NOT IN ('trash', 'auto-draft', 'inherit')
                ORDER BY post_date DESC",
                'order',
                $user_id
            )
        );

        if (!empty($ids_by_author)) {
            $order_ids = array_merge($order_ids, $ids_by_author);
        }

        if ($user->user_email !== '') {
            $ids_by_email = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT post_id
                    FROM {$wpdb->postmeta}
                    WHERE meta_key = %s
                    AND meta_value = %s",
                    '_order_customer_email',
                    (string) $user->user_email
                )
            );

            if (!empty($ids_by_email)) {
                $order_ids = array_merge($order_ids, $ids_by_email);
            }
        }

        if ($user->user_login !== '') {
            $ids_by_username = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT post_id
                    FROM {$wpdb->postmeta}
                    WHERE meta_key = %s
                    AND meta_value = %s",
                    '_order_customer_username',
                    (string) $user->user_login
                )
            );

            if (!empty($ids_by_username)) {
                $order_ids = array_merge($order_ids, $ids_by_username);
            }
        }

        if ($customer_full_name !== '') {
            $ids_by_full_name = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT post_id
                    FROM {$wpdb->postmeta}
                    WHERE meta_key = %s
                    AND meta_value = %s",
                    '_order_customer_name',
                    $customer_full_name
                )
            );

            if (!empty($ids_by_full_name)) {
                $order_ids = array_merge($order_ids, $ids_by_full_name);
            }
        }

        if ($customer_display_name !== '' && $customer_display_name !== $customer_full_name) {
            $ids_by_display_name = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT DISTINCT post_id
                    FROM {$wpdb->postmeta}
                    WHERE meta_key = %s
                    AND meta_value = %s",
                    '_order_customer_name',
                    $customer_display_name
                )
            );

            if (!empty($ids_by_display_name)) {
                $order_ids = array_merge($order_ids, $ids_by_display_name);
            }
        }

        $order_ids = array_map('absint', $order_ids);
        $order_ids = array_filter($order_ids);
        $order_ids = array_values(array_unique($order_ids));

        if (empty($order_ids)) {
            return array();
        }

        usort($order_ids, function ($left, $right) {
            $left_date = get_post_field('post_date', $left);
            $right_date = get_post_field('post_date', $right);

            return strcmp((string) $right_date, (string) $left_date);
        });

        return $order_ids;
    }

    private function build_customer_order_item($order)
    {
        $status = (string) get_post_meta($order->ID, '_order_order_status', true);
        $quantity = (int) get_post_meta($order->ID, '_order_product_quantity', true);
        $unit_price = (int) get_post_meta($order->ID, '_order_product_unit_price', true);
        $total_price = (int) get_post_meta($order->ID, '_order_total_price', true);

        return array(
            'id'             => $order->ID,
            'title'          => $order->post_title,
            'date'           => get_the_date('d M Y H:i', $order),
            'product_name'   => (string) get_post_meta($order->ID, '_order_product_name', true),
            'quantity'       => $quantity,
            'unit_price'     => $unit_price,
            'total_price'    => $total_price,
            'delivery_date'  => (string) get_post_meta($order->ID, '_order_delivery_date', true),
            'delivery_time'  => (string) get_post_meta($order->ID, '_order_delivery_time', true),
            'payment_method' => (string) get_post_meta($order->ID, '_order_payment_method', true),
            'order_status'   => $status,
        );
    }

    private function sync_order_customer_identity($order_id, $user_id, \WP_User $user, $customer_full_name)
    {
        $stored_user_id = (string) get_post_meta($order_id, '_order_customer_user_id', true);
        $stored_email = (string) get_post_meta($order_id, '_order_customer_email', true);
        $stored_username = (string) get_post_meta($order_id, '_order_customer_username', true);

        if ($stored_user_id === '' || $stored_user_id === '0') {
            update_post_meta($order_id, '_order_customer_user_id', (string) $user_id);
        }

        if ($stored_email === '' && $user->user_email !== '') {
            update_post_meta($order_id, '_order_customer_email', (string) $user->user_email);
        }

        if ($stored_username === '' && $user->user_login !== '') {
            update_post_meta($order_id, '_order_customer_username', (string) $user->user_login);
        }

        if ($customer_full_name !== '' && (string) get_post_meta($order_id, '_order_customer_name', true) === '') {
            update_post_meta($order_id, '_order_customer_name', $customer_full_name);
        }
    }

    private function get_payment_method_labels()
    {
        return array(
            'bank_transfer'  => 'Transfer Bank',
            'digital_wallet' => 'QRIS / Dompet Digital',
            'cod'            => 'COD',
        );
    }

    private function get_order_status_labels()
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

    private function redirect_customer_with_feedback($status)
    {
        $redirect_url = wp_get_referer();
        if (!$redirect_url) {
            $redirect_url = home_url('/');
        }

        wp_safe_redirect(add_query_arg(array('customer_status' => $status), $redirect_url));
        exit;
    }

    private function redirect_customer_auth($status)
    {
        $redirect_url = wp_get_referer();
        if (!$redirect_url) {
            $redirect_url = home_url('/');
        }

        wp_safe_redirect(add_query_arg(array('customer_auth_status' => $status), $redirect_url));
        exit;
    }

    private function get_customer_auth_panel($message)
    {
        return Template::get('frontend/customer-auth', array(
            'message'      => $message,
            'feedback'     => $this->get_customer_auth_feedback_message(),
            'nonce_action' => self::CUSTOMER_AUTH_NONCE_ACTION,
            'nonce_name'   => self::CUSTOMER_AUTH_NONCE_NAME,
            'login_url'    => wp_login_url(get_permalink()),
            'city_options' => $this->get_customer_city_options(),
        ));
    }

    private function generate_customer_username($email)
    {
        $base = sanitize_user(current(explode('@', $email)), true);
        if ($base === '') {
            $base = 'customer';
        }

        $username = $base;
        $suffix = 1;
        while (username_exists($username)) {
            $username = $base . $suffix;
            $suffix++;
        }

        return $username;
    }

    private function get_customer_auth_notice($message)
    {
        return sprintf(
            '<div class="alert alert-warning" role="alert">%1$s <a href="%2$s">%3$s</a></div>',
            esc_html($message),
            esc_url(wp_login_url(get_permalink())),
            esc_html__('Login di sini', 'custom-plugin')
        );
    }
}
