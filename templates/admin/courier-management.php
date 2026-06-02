<?php

/**
 * Admin Template: Courier Management
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
        <h2>Tambah Kurir</h2>
        <form method="post">
            <?php wp_nonce_field(Admin::CREATE_NONCE_ACTION, Admin::CREATE_NONCE_NAME); ?>
            <input type="hidden" name="custom_plugin_admin_action" value="create_courier">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="courier-display-name">Nama</label></th>
                        <td><input name="courier_display_name" type="text" id="courier-display-name" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="courier-username">Username</label></th>
                        <td><input name="courier_username" type="text" id="courier-username" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="courier-email">Email</label></th>
                        <td><input name="courier_email" type="email" id="courier-email" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="courier-password">Password</label></th>
                        <td><input name="courier_password" type="password" id="courier-password" class="regular-text" required></td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button('Tambah Kurir'); ?>
        </form>
    </div>

    <div style="margin-top: 24px;">
        <h2>Daftar Kurir</h2>
        <?php if (empty($couriers)) : ?>
            <p>Belum ada akun kurir.</p>
        <?php else : ?>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($couriers as $courier) : ?>
                        <tr>
                            <td><?php echo esc_html($courier->display_name); ?></td>
                            <td><?php echo esc_html($courier->user_login); ?></td>
                            <td><?php echo esc_html($courier->user_email); ?></td>
                            <td>
                                <?php
                                $delete_url = wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'page' => Admin::MENU_SLUG,
                                            'action' => 'delete_courier',
                                            'courier_id' => $courier->ID,
                                        ),
                                        admin_url('users.php')
                                    ),
                                    Admin::DELETE_NONCE_ACTION . $courier->ID
                                );
                                ?>
                                <a href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('Hapus akun kurir ini?');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>