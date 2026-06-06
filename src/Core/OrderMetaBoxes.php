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
    'customer_email'      => 'text',
    'customer_username'   => 'text',
    'customer_user_id'    => 'text',
    'customer_phone'      => 'text',
    'customer_address'    => 'textarea',
    'customer_city'       => 'text',
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
    $this->render_text_row('Email Customer', 'customer_email', $values['customer_email']);
    $this->render_text_row('Username Customer', 'customer_username', $values['customer_username']);
    $this->render_text_row('ID Customer Login', 'customer_user_id', $values['customer_user_id']);
    $this->render_text_row('No. Telepon', 'customer_phone', $values['customer_phone']);
    $this->render_textarea_row('Alamat Lengkap', 'customer_address', $values['customer_address']);
    $this->render_text_row('Kota', 'customer_city', $values['customer_city']);
    $this->render_text_row('Titik GPS', 'customer_gps', $values['customer_gps'], 'Contoh: -6.200000, 106.816666');
    $this->render_display_row('ID Produk', (string) get_post_meta($post->ID, '_order_product_id', true));
    $this->render_display_row('Nama Produk', (string) get_post_meta($post->ID, '_order_product_name', true));
    $this->render_display_row('Jumlah Produk', (string) get_post_meta($post->ID, '_order_product_quantity', true));
    $this->render_display_row('Harga Satuan', $this->format_price((int) get_post_meta($post->ID, '_order_product_unit_price', true)));
    $this->render_display_row('Total Harga', $this->format_price((int) get_post_meta($post->ID, '_order_total_price', true)));
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
        $new_columns['customer_summary'] = 'Customer';
        $new_columns['customer_email'] = 'Email Customer';
        $new_columns['customer_address'] = 'Alamat Lengkap';
        $new_columns['customer_gps'] = 'Titik GPS';
        $new_columns['product_summary'] = 'Produk';
        $new_columns['price_summary'] = 'Harga';
        $new_columns['order_status'] = 'Status';
        $new_columns['delivery_schedule'] = 'Jadwal Kirim';
        $new_columns['assigned_courier'] = 'Kurir';
        $new_columns['payment_method'] = 'Pembayaran';
        $new_columns['customer_account'] = 'Akun Login';
      }
    }

    return $new_columns;
  }

  public function render_order_column($column, $post_id)
  {
    if ($column === 'customer_summary') {
      $customer_name = (string) get_post_meta($post_id, '_order_customer_name', true);
      $customer_phone = (string) get_post_meta($post_id, '_order_customer_phone', true);
      $customer_city = (string) get_post_meta($post_id, '_order_customer_city', true);
      $customer_user_id = (string) get_post_meta($post_id, '_order_customer_user_id', true);
      $customer_status = ($customer_user_id !== '' && $customer_user_id !== '0') ? 'Customer Login' : 'Guest / Belum Login';

      echo '<strong>' . esc_html($customer_name !== '' ? $customer_name : '-') . '</strong><br>';
      echo '<span>' . esc_html($customer_phone !== '' ? $customer_phone : '-') . '</span><br>';
      echo '<span style="color:#646970;">' . esc_html($customer_city !== '' ? $customer_city : '-') . '</span><br>';
      echo '<span style="color:#2271b1;">' . esc_html($customer_status) . '</span>';
      return;
    }

    if ($column === 'product_summary') {
      $product_name = (string) get_post_meta($post_id, '_order_product_name', true);
      $quantity = (string) get_post_meta($post_id, '_order_product_quantity', true);

      echo '<strong>' . esc_html($product_name !== '' ? $product_name : '-') . '</strong><br>';
      echo '<span>Qty: ' . esc_html($quantity !== '' ? $quantity : '0') . '</span>';
      return;
    }

    if ($column === 'price_summary') {
      $unit_price = (int) get_post_meta($post_id, '_order_product_unit_price', true);
      $total_price = (int) get_post_meta($post_id, '_order_total_price', true);

      echo '<strong>Total: ' . esc_html($this->format_price($total_price)) . '</strong><br>';
      echo '<span>Satuan: ' . esc_html($this->format_price($unit_price)) . '</span>';
      return;
    }

    if ($column === 'customer_email') {
      $customer_email = (string) get_post_meta($post_id, '_order_customer_email', true);
      echo esc_html($customer_email !== '' ? $customer_email : '-');
      return;
    }

    if ($column === 'customer_address') {
      $customer_address = (string) get_post_meta($post_id, '_order_customer_address', true);
      echo $customer_address !== '' ? nl2br(esc_html($customer_address)) : '-';
      return;
    }

    if ($column === 'customer_gps') {
      $customer_gps = (string) get_post_meta($post_id, '_order_customer_gps', true);
      echo esc_html($customer_gps !== '' ? $customer_gps : '-');
      return;
    }

    if ($column === 'order_status') {
      $status = get_post_meta($post_id, '_order_order_status', true);
      echo '<div class="custom-plugin-order-list-field" data-order-id="' . esc_attr((string) $post_id) . '">';
      echo '<select name="order_status" class="custom-plugin-order-status-select">';
      foreach ($this->get_order_status_options() as $status_value => $status_label) {
        echo '<option value="' . esc_attr($status_value) . '" ' . selected($status, $status_value, false) . '>' . esc_html($status_label) . '</option>';
      }
      echo '</select> ';
      echo '<button type="button" class="button button-small custom-plugin-order-list-save" data-update-field="order_status">Simpan</button>';
      echo '</div>';
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

      echo '<div class="custom-plugin-order-list-field" data-order-id="' . esc_attr((string) $post_id) . '">';
      echo '<select name="assigned_courier_id" class="custom-plugin-courier-select">';
      echo '<option value="">Pilih kurir</option>';
      foreach ($couriers as $user) {
        echo '<option value="' . esc_attr((string) $user->ID) . '" ' . selected((string) $courier_id, (string) $user->ID, false) . '>' . esc_html($user->display_name) . '</option>';
      }
      echo '</select> ';
      echo '<button type="button" class="button button-small custom-plugin-order-list-save" data-update-field="assigned_courier_id">Simpan</button>';
      echo '</div>';
      return;
    }

    if ($column === 'payment_method') {
      $payment_method = get_post_meta($post_id, '_order_payment_method', true);
      echo esc_html($this->get_payment_method_label($payment_method));
      return;
    }

    if ($column === 'customer_account') {
      $customer_email = (string) get_post_meta($post_id, '_order_customer_email', true);
      $customer_username = (string) get_post_meta($post_id, '_order_customer_username', true);
      $customer_user_id = (string) get_post_meta($post_id, '_order_customer_user_id', true);

      echo '<strong>' . esc_html($customer_email !== '' ? $customer_email : '-') . '</strong><br>';
      echo '<span>' . esc_html($customer_username !== '' ? $customer_username : '-') . '</span><br>';
      echo '<span style="color:#646970;">User ID: ' . esc_html($customer_user_id !== '' ? $customer_user_id : '-') . '</span>';
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

    if (!isset($_POST['custom_plugin_order_action']) || $_POST['custom_plugin_order_action'] !== 'update_list_field') {
      return;
    }

    if (!isset($_POST[self::LIST_NONCE_NAME]) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::LIST_NONCE_NAME])), self::LIST_NONCE_ACTION)) {
      return;
    }

    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    if ($order_id < 1 || !current_user_can('edit_post', $order_id)) {
      return;
    }

    $field_key = isset($_POST['field_key']) ? sanitize_key(wp_unslash($_POST['field_key'])) : '';
    if (!in_array($field_key, array('assigned_courier_id', 'order_status'), true)) {
      return;
    }

    $field_type = isset($this->fields[$field_key]) ? $this->fields[$field_key] : '';
    $raw_value = isset($_POST[$field_key]) ? wp_unslash($_POST[$field_key]) : '';
    $value = $this->sanitize_field_value($field_key, $field_type, $raw_value);
    $meta_key = '_order_' . $field_key;

    if ($value === '') {
      delete_post_meta($order_id, $meta_key);
    } else {
      update_post_meta($order_id, $meta_key, $value);
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
      'document.addEventListener("DOMContentLoaded",function(){var items=document.querySelectorAll(".custom-plugin-order-list-field");items.forEach(function(item){var button=item.querySelector(".custom-plugin-order-list-save");if(!button){return;}button.addEventListener("click",function(){var fieldKey=button.getAttribute("data-update-field");var field=item.querySelector("[name=\'"+fieldKey+"\']");if(!field){return;}var form=document.createElement("form");form.method="post";form.action="";var fields={custom_plugin_order_action:"update_list_field",order_id:item.getAttribute("data-order-id"),field_key:fieldKey,' . json_encode(self::LIST_NONCE_NAME) . ':' . json_encode(wp_create_nonce(self::LIST_NONCE_ACTION)) . '};fields[fieldKey]=field.value;Object.keys(fields).forEach(function(key){var input=document.createElement("input");input.type="hidden";input.name=key;input.value=fields[key];form.appendChild(input);});document.body.appendChild(form);form.submit();});});});',
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
      $allowed = array_keys($this->get_order_status_options());
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
    $labels = $this->get_order_status_options();

    return isset($labels[$status]) ? $labels[$status] : '-';
  }

  private function get_order_status_options()
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

  private function format_price($price)
  {
    return 'Rp ' . number_format((int) $price, 0, ',', '.');
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

  private function render_display_row($label, $value)
  {
    echo '<tr>';
    echo '<th scope="row">' . esc_html($label) . '</th>';
    echo '<td><strong>' . esc_html($value !== '' ? $value : '-') . '</strong></td>';
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
