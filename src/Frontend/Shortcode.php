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

    public function __construct()
    {
        add_shortcode(self::ORDER_SHORTCODE, array($this, 'order_form_shortcode'));
        add_shortcode(self::PRICE_SHORTCODE, array($this, 'product_price_shortcode'));
        add_shortcode(self::BUY_BUTTON_SHORTCODE, array($this, 'buy_button_shortcode'));
        add_shortcode(self::CUSTOMER_REGISTRATION_SHORTCODE, array($this, 'customer_registration_shortcode'));
        add_shortcode(self::CUSTOMER_PROFILE_SHORTCODE, array($this, 'customer_profile_shortcode'));
        add_action('init', array($this, 'handle_order_submission'));
        add_action('init', array($this, 'handle_customer_submission'));
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
            return $this->get_customer_auth_notice('Silakan login terlebih dulu untuk mengisi pendaftaran customer.');
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
            return $this->get_customer_auth_notice('Silakan login terlebih dulu untuk melihat profile customer.');
        }

        $current_user = wp_get_current_user();

        return Template::get('frontend/customer-profile', array(
            'profile'       => $this->get_customer_profile_data($current_user->ID),
            'city_options'  => $this->get_customer_city_options(),
            'nonce_action'  => self::CUSTOMER_NONCE_ACTION,
            'nonce_name'    => self::CUSTOMER_NONCE_NAME,
            'feedback'      => $this->get_customer_feedback_message(),
            'form_title'    => 'Profile Customer',
            'form_subtitle' => 'Perbarui data customer Anda kapan saja dari halaman ini.',
            'submit_label'  => 'Update Profile',
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
        $address = isset($_POST['customer_address']) ? sanitize_textarea_field(wp_unslash($_POST['customer_address'])) : '';
        $city = isset($_POST['customer_city']) ? sanitize_text_field(wp_unslash($_POST['customer_city'])) : '';
        $city_options = $this->get_customer_city_options();
        $city_values = wp_list_pluck($city_options, 'value');
        $current_ktp_id = (int) get_user_meta($current_user->ID, '_customer_ktp_attachment_id', true);

        if ($full_name === '' || $address === '' || $city === '' || !in_array($city, $city_values, true)) {
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
                'price' => 'Rp.x',
            ),
            array(
                'value' => 'kabupaten-gorontalo',
                'label' => 'Kabupaten Gorontalo',
                'price' => 'Rp.Y',
            ),
            array(
                'value' => 'kabupaten-gorontalo-utara',
                'label' => 'Kabupaten Gorontalo Utara',
                'price' => 'Rp.z',
            ),
            array(
                'value' => 'kabupaten-boalemo',
                'label' => 'Kabupaten Boalemo',
                'price' => 'Rp.A',
            ),
            array(
                'value' => 'kabupaten-pohuwato',
                'label' => 'Kabupaten Pohuwato',
                'price' => 'Rp.B',
            ),
            array(
                'value' => 'kabupaten-bone-bolango',
                'label' => 'Kabupaten Bone Bolango',
                'price' => 'Rp.C',
            ),
        );
    }

    private function get_customer_city_label($value)
    {
        foreach ($this->get_customer_city_options() as $city) {
            if ($city['value'] === $value) {
                return $city['label'] . ' (' . $city['price'] . ')';
            }
        }

        return '';
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

    private function redirect_customer_with_feedback($status)
    {
        $redirect_url = wp_get_referer();
        if (!$redirect_url) {
            $redirect_url = home_url('/');
        }

        wp_safe_redirect(add_query_arg(array('customer_status' => $status), $redirect_url));
        exit;
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
