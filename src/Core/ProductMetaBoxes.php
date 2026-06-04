<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
  exit;
}

class ProductMetaBoxes
{
  const NONCE_ACTION = 'custom_plugin_save_product_meta';
  const NONCE_NAME = 'custom_plugin_product_meta_nonce';
  const CITY_PRICE_FIELDS = array(
    'kota_gorontalo' => 'Kota Gorontalo',
    'kabupaten_gorontalo' => 'Kabupaten Gorontalo',
    'kabupaten_gorontalo_utara' => 'Kabupaten Gorontalo Utara',
    'kabupaten_boalemo' => 'Kabupaten Boalemo',
    'kabupaten_pohuwato' => 'Kabupaten Pohuwato',
    'kabupaten_bone_olango' => 'Kabupaten Bone Olango',
  );

  public function __construct()
  {
    add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
    add_action('save_post_produk', array($this, 'save_product_meta'));
  }

  public function register_meta_boxes()
  {
    add_meta_box(
      'custom-plugin-product-price',
      'Harga Produk',
      array($this, 'render_price_meta_box'),
      'produk',
      'side',
      'high'
    );
  }

  public function render_price_meta_box($post)
  {
    wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

    $price = get_post_meta($post->ID, '_product_price', true);

    echo '<p>';
    echo '<label for="custom-plugin-product-price-field"><strong>Harga</strong></label>';
    echo '</p>';
    echo '<input type="number" min="0" step="1" class="widefat" id="custom-plugin-product-price-field" name="custom_plugin_product_price" value="' . esc_attr($price) . '">';
    echo '<p class="description">Masukkan harga produk dalam Rupiah tanpa titik atau koma.</p>';

    echo '<hr>';
    echo '<p><strong>Harga Per Kota</strong></p>';
    echo '<p class="description">Isi harga khusus per kota. Kosongkan jika ingin memakai harga utama.</p>';

    foreach (self::CITY_PRICE_FIELDS as $field_key => $label) {
      $meta_key = '_product_price_' . $field_key;
      $city_price = get_post_meta($post->ID, $meta_key, true);

      echo '<p style="margin-bottom:8px;">';
      echo '<label for="custom-plugin-product-price-' . esc_attr($field_key) . '"><strong>' . esc_html($label) . '</strong></label>';
      echo '</p>';
      echo '<input type="number" min="0" step="1" class="widefat" id="custom-plugin-product-price-' . esc_attr($field_key) . '" name="custom_plugin_product_price_' . esc_attr($field_key) . '" value="' . esc_attr($city_price) . '">';
    }
  }

  public function save_product_meta($post_id)
  {
    if (!$this->can_save($post_id)) {
      return;
    }

    if (!isset($_POST['custom_plugin_product_price'])) {
      delete_post_meta($post_id, '_product_price');
      return;
    }

    $raw_price = sanitize_text_field(wp_unslash($_POST['custom_plugin_product_price']));
    $price = preg_replace('/[^0-9]/', '', $raw_price);

    if ($price === '') {
      delete_post_meta($post_id, '_product_price');
      return;
    }

    update_post_meta($post_id, '_product_price', $price);

    foreach (self::CITY_PRICE_FIELDS as $field_key => $label) {
      $input_name = 'custom_plugin_product_price_' . $field_key;
      $meta_key = '_product_price_' . $field_key;

      if (!isset($_POST[$input_name])) {
        delete_post_meta($post_id, $meta_key);
        continue;
      }

      $raw_city_price = sanitize_text_field(wp_unslash($_POST[$input_name]));
      $city_price = preg_replace('/[^0-9]/', '', $raw_city_price);

      if ($city_price === '') {
        delete_post_meta($post_id, $meta_key);
        continue;
      }

      update_post_meta($post_id, $meta_key, $city_price);
    }
  }

  private function can_save($post_id)
  {
    if (!isset($_POST[self::NONCE_NAME])) {
      return false;
    }

    if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_NAME])), self::NONCE_ACTION)) {
      return false;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
      return false;
    }

    if (!current_user_can('edit_post', $post_id)) {
      return false;
    }

    return true;
  }
}
