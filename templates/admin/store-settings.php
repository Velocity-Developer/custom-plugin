<?php

/**
 * Admin Template: Store Settings
 */

use CustomPlugin\Admin\Admin;

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap">
    <h1>Pengaturan Toko</h1>

    <?php if (!empty($feedback)) : ?>
        <div class="notice notice-info is-dismissible">
            <p><?php echo esc_html($feedback === 'saved' ? 'Pengaturan toko berhasil disimpan.' : $feedback); ?></p>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field(Admin::STORE_NONCE_ACTION, Admin::STORE_NONCE_NAME); ?>
        <input type="hidden" name="custom_plugin_admin_action" value="save_store_settings">

        <table class="form-table" role="presentation">
            <tbody>
                <tr>
                    <th scope="row"><label for="bank-name">Nama Bank</label></th>
                    <td><input type="text" class="regular-text" id="bank-name" name="bank_name" value="<?php echo esc_attr($settings['bank_name']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bank-account-name">Nama Pemilik Rekening</label></th>
                    <td><input type="text" class="regular-text" id="bank-account-name" name="bank_account_name" value="<?php echo esc_attr($settings['bank_account_name']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="bank-account-number">Nomor Rekening</label></th>
                    <td><input type="text" class="regular-text" id="bank-account-number" name="bank_account_number" value="<?php echo esc_attr($settings['bank_account_number']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="qris-image-id">Gambar QRIS</label></th>
                    <td>
                        <input type="hidden" id="qris-image-id" name="qris_image_id" value="<?php echo esc_attr((string) $settings['qris_image_id']); ?>">
                        <p>
                            <button type="button" class="button custom-plugin-media-button" data-target="#qris-image-id" data-preview="#qris-image-preview">Pilih Gambar QRIS</button>
                            <button type="button" class="button custom-plugin-media-clear" data-target="#qris-image-id" data-preview="#qris-image-preview">Hapus Gambar</button>
                        </p>
                        <img id="qris-image-preview" src="<?php echo esc_url($qris_image_url); ?>" alt="Preview QRIS" style="max-width:240px;height:auto;" class="<?php echo $qris_image_url ? '' : 'd-none'; ?>">
                    </td>
                </tr>
            </tbody>
        </table>

        <?php submit_button('Simpan Pengaturan'); ?>
    </form>
</div>