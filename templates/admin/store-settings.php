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
                    <th scope="row">Daftar Rekening Bank</th>
                    <td>
                        <?php $banks = !empty($settings['banks']) ? $settings['banks'] : array(array('bank_name' => '', 'bank_account_name' => '', 'bank_account_number' => '')); ?>
                        <div id="custom-plugin-bank-list">
                            <?php foreach ($banks as $index => $bank) : ?>
                                <div class="custom-plugin-bank-item" style="margin-bottom:16px;padding:16px;border:1px solid #dcdcde;border-radius:6px;">
                                    <p><label>Nama Bank<br><input type="text" class="regular-text" name="banks[<?php echo esc_attr((string) $index); ?>][bank_name]" value="<?php echo esc_attr($bank['bank_name']); ?>"></label></p>
                                    <p><label>Nama Pemilik Rekening<br><input type="text" class="regular-text" name="banks[<?php echo esc_attr((string) $index); ?>][bank_account_name]" value="<?php echo esc_attr($bank['bank_account_name']); ?>"></label></p>
                                    <p><label>Nomor Rekening<br><input type="text" class="regular-text" name="banks[<?php echo esc_attr((string) $index); ?>][bank_account_number]" value="<?php echo esc_attr($bank['bank_account_number']); ?>"></label></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p><button type="button" class="button" id="custom-plugin-add-bank">Tambah Bank</button></p>
                        <p class="description">Tambahkan satu blok rekening untuk setiap bank yang ingin ditampilkan di form order.</p>
                    </td>
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