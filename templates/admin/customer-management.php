<?php

/**
 * Admin Template: Customer Management
 */

use CustomPlugin\Admin\Admin;

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if (!empty($feedback)) : ?>
        <div class="notice notice-info is-dismissible">
            <p><?php echo esc_html($feedback); ?></p>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 720px; margin-top: 20px; padding: 16px;">
        <h2>Tambah Customer</h2>
        <form method="post">
            <?php wp_nonce_field(Admin::CREATE_CUSTOMER_NONCE_ACTION, Admin::CREATE_CUSTOMER_NONCE_NAME); ?>
            <input type="hidden" name="custom_plugin_admin_action" value="create_customer">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="customer-full-name">Nama Lengkap</label></th>
                        <td><input name="customer_full_name" type="text" id="customer-full-name" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="customer-email">Email</label></th>
                        <td><input name="customer_email" type="email" id="customer-email" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="customer-city">Kota</label></th>
                        <td>
                            <select name="customer_city" id="customer-city" required>
                                <option value="">Pilih kota</option>
                                <?php foreach ($city_options as $city) : ?>
                                    <option value="<?php echo esc_attr($city['value']); ?>"><?php echo esc_html($city['label']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="customer-password">Password</label></th>
                        <td><input name="customer_password" type="password" id="customer-password" class="regular-text" required></td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button('Tambah Customer'); ?>
        </form>
    </div>

    <div style="margin-top: 24px;">
        <h2>Daftar Customer</h2>
        <?php if (empty($customers)) : ?>
            <p>Belum ada akun customer.</p>
        <?php else : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Kota</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer) : ?>
                        <tr>
                            <td><?php echo esc_html($customer['display_name']); ?></td>
                            <td><?php echo esc_html($customer['username']); ?></td>
                            <td><?php echo esc_html($customer['email']); ?></td>
                            <td><?php echo esc_html($customer['city'] !== '' ? $customer['city'] : '-'); ?></td>
                            <td>
                                <?php
                                $delete_url = wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'page' => Admin::CUSTOMER_MENU_SLUG,
                                            'action' => 'delete_customer',
                                            'customer_id' => $customer['id'],
                                        ),
                                        admin_url('users.php')
                                    ),
                                    Admin::DELETE_CUSTOMER_NONCE_ACTION . $customer['id']
                                );
                                ?>
                                <a href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('Hapus akun customer ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>