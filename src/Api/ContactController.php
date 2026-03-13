<?php

namespace CustomPlugin\Api;

if (!defined('ABSPATH')) {
  exit;
}

class ContactController
{

  public function __construct()
  {
    add_action('rest_api_init', array($this, 'register_routes'));
  }

  public function register_routes()
  {
    register_rest_route('custom-plugin/v1', '/contact', array(
      'methods' => 'POST',
      'callback' => array($this, 'submit_contact_form'),
      'permission_callback' => '__return_true', // Public endpoint
    ));
  }

  public function submit_contact_form($request)
  {
    $params = $request->get_json_params();

    // Validation
    if (empty($params['name']) || empty($params['email'])) {
      return new \WP_Error('missing_params', 'Nama dan Email wajib diisi.', array('status' => 400));
    }

    if (!is_email($params['email'])) {
      return new \WP_Error('invalid_email', 'Alamat email tidak valid.', array('status' => 400));
    }

    // Verify Nonce (Optional but recommended for frontend forms if user is logged in, but for public forms, maybe captcha? For now, we'll skip complex nonce checks or just rely on a generic nonce passed from frontend)
    // Ideally, we should check a nonce.
    $nonce = $request->get_header('X-WP-Nonce');
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
      // For public REST API, wp_rest nonce is only valid for logged-in users.
      // If we want this form to be public for guests, we shouldn't strictly enforce wp_rest nonce for authentication,
      // but we might want a custom nonce. However, standard WP REST API for public endpoints doesn't require auth.
      // We'll proceed without strict nonce check for guest submission, or use a custom one.
      // Let's keep it simple for now: Public access.
    }

    $name = sanitize_text_field($params['name']);
    $email = sanitize_email($params['email']);
    $message = isset($params['message']) ? sanitize_textarea_field($params['message']) : '';

    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_plugin_data';

    $result = $wpdb->insert(
      $table_name,
      array(
        'name' => $name,
        'email' => $email,
        'message' => $message
      ),
      array('%s', '%s', '%s')
    );

    if ($result === false) {
      return new \WP_Error('db_error', 'Gagal menyimpan data.', array('status' => 500));
    }

    return rest_ensure_response(array(
      'success' => true,
      'message' => 'Terima kasih! Pesan Anda telah dikirim.'
    ));
  }
}
