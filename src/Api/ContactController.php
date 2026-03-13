<?php

namespace CustomPlugin\Api;

use CustomPlugin\Core\SubmissionModel;

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

    // Verify Nonce
    $nonce = $request->get_header('X-WP-Nonce');
    if (!wp_verify_nonce($nonce, 'wp_rest')) {
      return new \WP_Error('rest_forbidden', 'Keamanan (nonce) tidak valid.', array('status' => 403));
    }

    $result = SubmissionModel::create($params);

    if ($result === false) {
      return new \WP_Error('db_error', 'Gagal menyimpan data.', array('status' => 500));
    }

    return rest_ensure_response(array(
      'success' => true,
      'message' => 'Terima kasih! Pesan Anda telah dikirim.'
    ));
  }
}
