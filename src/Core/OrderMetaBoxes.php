<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
  exit;
}

class OrderMetaBoxes
{
  const NONCE_ACTION = 'custom_plugin_save_order_meta';
  const NONCE_NAME = 'custom_plugin_order_meta_nonce';
  const LIST_NONCE_ACTION = 'custom_plugin_assign_courier_from_list';
  const LIST_NONCE_NAME = 'custom_plugin_assign_courier_list_nonce';
  const LIST_SCRIPT_HANDLE = 'custom-plugin-order-list';

  private $fields = array(
    'customer_name'       => 'text',
    'customer_phone'      => 'text',
    'customer_address'    => 'textarea',
    'customer_gps'        => 'text',
    'delivery_date'       => 'date',
    'delivery_time'       => 'time',
    'payment_method'      => 'select',
    'order_status'        => 'select',
    'assigned_courier_id' => 'user',
    'delivery_proof_url'  => 'url',
    'cod_payment_proof_url' => 'url',
  );

  public function __construct()
  {
    add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
    add_action('save_post_order', array($this, 'save_order_meta'));
    add_filter('manage_order_posts_columns', array($this, 'filter_order_columns'));
    add_action('manage_order_posts_custom_column', array($this, 'render_order_column'), 10, 2);
    add_action('admin_init', array($this, 'handle_list_assignment'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_list_scripts'));
  }

  public function register_meta_boxes()
  {
    add_meta_box(
      'custom-plugin-order-details',
      'Detail Order',
      array($this, 'render_order_details_meta_box'),
      'order',
      'normal',
      'high'
    );
  }

  public function render_order_details_meta_box($post)
  {
    wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);

    $values = array();
    foreach (array_keys($this->fields) as $field_key) {
      $values[$field_key] = get_post_meta($post->ID, '_order_' . $field_key, true);
    }

    $couriers = get_users(array(
      'role'    => Roles::COURIER_ROLE,
      'orderby' => 'display_name',
      'order'   => 'ASC',
    ));

    echo '<table class="form-table" role="presentation"><tbody>';
    $this->render_text_row('Nama Konsumen', 'customer_name', $values['customer_name']);
    $this->render_text_row('No. Telepon', 'customer_phone', $values['customer_phone']);
    $this->render_textarea_row('Alamat Lengkap', 'customer_address', $values['customer_address']);
    $this->render_text_row('Titik GPS', 'customer_gps', $values['customer_gps'], 'Contoh: -6.200000, 106.816666');
    $this->render_text_row('Tanggal Kirim', 'delivery_date', $values['delivery_date'], '', 'date');
    $this->render_text_row('Jam Kirim', 'delivery_time', $values['delivery_time'], '', 'time');
    $this->render_select_row(
      'Metode Pembayaran',
      'payment_method',
      $values['payment_method'],
      array(
        ''               => 'Pilih metode pembayaran',
        'bank_transfer'  => 'Transfer Bank',
        'digital_wallet' => 'Transfer Dompet Digital',
        'cod'            => 'COD',
      )
    );
    $this->render_select_row(
      'Status Order',
      'order_status',
      $values['order_status'],
      array(
        'pending'    => 'Pending',
        'scheduled'  => 'Terjadwal',
        'processing' => 'Diproses',
        'delivering' => 'Dalam Pengiriman',
        'delivered'  => 'Terkirim',
        'completed'  => 'Selesai',
        'cancelled'  => 'Dibatalkan',
      )
    );
    $this->render_user_select_row('Kurir', 'assigned_courier_id', $values['assigned_courier_id'], $couriers);
    $this->render_text_row('Bukti Pengiriman', 'delivery_proof_url', $values['delivery_proof_url'], 'Masukkan URL file atau gambar bukti kirim.', 'url');
    $this->render_text_row('Bukti Pembayaran COD', 'cod_payment_proof_url', $values['cod_payment_proof_url'], 'Isi jika metode pembayaran COD.', 'url');
    echo '</tbody></table>';
  }

  public function filter_order_columns($columns)
  {
    $new_columns = array();

    foreach ($columns as $key => $label) {
      $new_columns[$key] = $label;

      if ($key === 'title') {
        $new_columns['order_status'] = 'Status';
        $new_columns['delivery_schedule'] = 'Jadwal Kirim';
        $new_columns['assigned_courier'] = 'Kurir';
        $new_columns['payment_method'] = 'Pembayaran';
      }
    }

    return $new_columns;
  }

  public function render_order_column($column, $post_id)
  {
    if ($column === 'order_status') {
      $status = get_post_meta($post_id, '_order_order_status', true);
      echo esc_html($this->get_order_status_label($status));
      return;
    }

    if ($column === 'delivery_schedule') {
      $date = get_post_meta($post_id, '_order_delivery_date', true);
      $time = get_post_meta($post_id, '_order_delivery_time', true);
      $schedule = trim($date . ' ' . $time);
      echo $schedule !== '' ? esc_html($schedule) : '-';
      return;
    }

    if ($column === 'assigned_courier') {
      $courier_id = absint(get_post_meta($post_id, '_order_assigned_courier_id', true));
      $couriers = get_users(array(
        'role'    => Roles::COURIER_ROLE,
        'orderby' => 'display_name',
        'order'   => 'ASC',
      ));

      echo '<div class="custom-plugin-order-courier" data-order-id="' . esc_attr((string) $post_id) . '">';
      echo '<select name="assigned_courier_id" class="custom-plugin-courier-select">';
      echo '<option value="">Pilih kurir</option>';
      foreach ($couriers as $user) {
        echo '<option value="' . esc_attr((string) $user->ID) . '" ' . selected((string) $courier_id, (string) $user->ID, false) . '>' . esc_html($user->display_name) . '</option>';
      }
      echo '</select> ';
      echo '<button type="button" class="button button-small custom-plugin-courier-save">Simpan</button>';
      echo '</div>';
      return;
    }

    if ($column === 'payment_method') {
      $payment_method = get_post_meta($post_id, '_order_payment_method', true);
      echo esc_html($this->get_payment_method_label($payment_method));
    }
  }

  public function save_order_meta($post_id)
  {
    if (!$this->can_save($post_id)) {
      return;
    }

    foreach ($this->fields as $field_key => $field_type) {
      $input_name = 'custom_plugin_order_' . $field_key;
      $meta_key = '_order_' . $field_key;

      if (!isset($_POST[$input_name])) {
        if ($field_type === 'select' || $field_type === 'user') {
          delete_post_meta($post_id, $meta_key);
        }
        continue;
      }

      $raw_value = wp_unslash($_POST[$input_name]);
      $value = $this->sanitize_field_value($field_key, $field_type, $raw_value);

      if ($value === '') {
        delete_post_meta($post_id, $meta_key);
        continue;
      }

      update_post_meta($post_id, $meta_key, $value);
    }
  }

  public function handle_list_assignment()
  {
    if (!is_admin() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
      return;
    }

    if (!isset($_POST['custom_plugin_order_action']) || $_POST['custom_plugin_order_action'] !== 'assign_courier') {
      return;
    }

    if (!isset($_POST[self::LIST_NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::LIST_NONCE_NAME])), self::LIST_NONCE_ACTION)) {
      return;
    }

    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    if ($order_id < 1 || !current_user_can('edit_post', $order_id)) {
      return;
    }

    $courier_id = isset($_POST['assigned_courier_id']) ? absint($_POST['assigned_courier_id']) : 0;
    $value = $this->sanitize_field_value('assigned_courier_id', 'user', (string) $courier_id);

    if ($value === '') {
      delete_post_meta($order_id, '_order_assigned_courier_id');
    } else {
      update_post_meta($order_id, '_order_assigned_courier_id', $value);
    }

    $redirect_url = add_query_arg(
      array(
        'post_type' => 'order',
      ),
      admin_url('edit.php')
    );

    wp_safe_redirect($redirect_url);
    exit;
  }

  public function enqueue_list_scripts($hook)
  {
    if ($hook !== 'edit.php') {
      return;
    }

    $post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';
    if ($post_type !== 'order') {
      return;
    }

    wp_register_script(self::LIST_SCRIPT_HANDLE, '', array(), CUSTOM_PLUGIN_VERSION, true);
    wp_enqueue_script(self::LIST_SCRIPT_HANDLE);
    wp_add_inline_script(
      self::LIST_SCRIPT_HANDLE,
      'document.addEventListener("DOMContentLoaded",function(){var items=document.querySelectorAll(".custom-plugin-order-courier");items.forEach(function(item){var button=item.querySelector(".custom-plugin-courier-save");var select=item.querySelector(".custom-plugin-courier-select");if(!button||!select){return;}button.addEventListener("click",function(){var form=document.createElement("form");form.method="post";form.action="";var fields={custom_plugin_order_action:"assign_courier",order_id:item.getAttribute("data-order-id"),assigned_courier_id:select.value,' . json_encode(self::LIST_NONCE_NAME) . ':' . json_encode(wp_create_nonce(self::LIST_NONCE_ACTION)) . '};Object.keys(fields).forEach(function(key){var input=document.createElement("input");input.type="hidden";input.name=key;input.value=fields[key];form.appendChild(input);});document.body.appendChild(form);form.submit();});});});',
      'after'
    );
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

  private function sanitize_field_value($field_key, $field_type, $raw_value)
  {
    if ($field_type === 'textarea') {
      return sanitize_textarea_field($raw_value);
    }

    if ($field_type === 'user') {
      $user_id = absint($raw_value);
      if ($user_id < 1) {
        return '';
      }

      $user = get_user_by('id', $user_id);
      if (!$user || !in_array(Roles::COURIER_ROLE, (array) $user->roles, true)) {
        return '';
      }

      return (string) $user_id;
    }

    if ($field_key === 'payment_method') {
      $allowed = array('bank_transfer', 'digital_wallet', 'cod');
      return in_array($raw_value, $allowed, true) ? $raw_value : '';
    }

    if ($field_key === 'order_status') {
      $allowed = array('pending', 'scheduled', 'processing', 'delivering', 'delivered', 'completed', 'cancelled');
      return in_array($raw_value, $allowed, true) ? $raw_value : '';
    }

    if ($field_type === 'url') {
      return esc_url_raw($raw_value);
    }

    return sanitize_text_field($raw_value);
  }

  private function get_payment_method_label($payment_method)
  {
    $labels = array(
      'bank_transfer'  => 'Transfer Bank',
      'digital_wallet' => 'Transfer Dompet Digital',
      'cod'            => 'COD',
    );

    return isset($labels[$payment_method]) ? $labels[$payment_method] : '-';
  }

  private function get_order_status_label($status)
  {
    $labels = array(
      'pending'    => 'Pending',
      'scheduled'  => 'Terjadwal',
      'processing' => 'Diproses',
      'delivering' => 'Dalam Pengiriman',
      'delivered'  => 'Terkirim',
      'completed'  => 'Selesai',
      'cancelled'  => 'Dibatalkan',
    );

    return isset($labels[$status]) ? $labels[$status] : '-';
  }

  private function render_text_row($label, $field_key, $value, $description = '', $type = 'text')
  {
    $input_name = 'custom_plugin_order_' . $field_key;

    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($input_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td>';
    echo '<input class="regular-text" type="' . esc_attr($type) . '" id="' . esc_attr($input_name) . '" name="' . esc_attr($input_name) . '" value="' . esc_attr($value) . '">';
    if ($description !== '') {
      echo '<p class="description">' . esc_html($description) . '</p>';
    }
    echo '</td>';
    echo '</tr>';
  }

  private function render_textarea_row($label, $field_key, $value)
  {
    $input_name = 'custom_plugin_order_' . $field_key;

    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($input_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td><textarea class="large-text" rows="4" id="' . esc_attr($input_name) . '" name="' . esc_attr($input_name) . '">' . esc_textarea($value) . '</textarea></td>';
    echo '</tr>';
  }

  private function render_select_row($label, $field_key, $value, $options)
  {
    $input_name = 'custom_plugin_order_' . $field_key;

    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($input_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td><select id="' . esc_attr($input_name) . '" name="' . esc_attr($input_name) . '">';
    foreach ($options as $option_value => $option_label) {
      echo '<option value="' . esc_attr($option_value) . '" ' . selected($value, $option_value, false) . '>' . esc_html($option_label) . '</option>';
    }
    echo '</select></td>';
    echo '</tr>';
  }

  private function render_user_select_row($label, $field_key, $value, $users)
  {
    $input_name = 'custom_plugin_order_' . $field_key;

    echo '<tr>';
    echo '<th scope="row"><label for="' . esc_attr($input_name) . '">' . esc_html($label) . '</label></th>';
    echo '<td><select id="' . esc_attr($input_name) . '" name="' . esc_attr($input_name) . '">';
    echo '<option value="">Pilih kurir</option>';
    foreach ($users as $user) {
      echo '<option value="' . esc_attr((string) $user->ID) . '" ' . selected((string) $value, (string) $user->ID, false) . '>' . esc_html($user->display_name) . '</option>';
    }
    echo '</select></td>';
    echo '</tr>';
  }
}
