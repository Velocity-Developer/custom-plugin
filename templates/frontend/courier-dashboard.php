<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="custom-plugin-courier-dashboard">
    <h2>Dashboard Kurir</h2>
    <p>Halo, <?php echo esc_html($current_user->display_name); ?>. Berikut daftar order yang ditugaskan ke akun Anda.</p>

    <?php if (!empty($feedback)) : ?>
        <p><strong><?php echo esc_html($feedback); ?></strong></p>
    <?php endif; ?>

    <?php if (empty($orders)) : ?>
        <p>Belum ada order yang ditugaskan.</p>
    <?php else : ?>
        <?php foreach ($orders as $order) : ?>
            <section class="custom-plugin-courier-order" style="margin-bottom:24px;padding:16px;border:1px solid #ddd;">
                <h3><?php echo esc_html($order['title'] !== '' ? $order['title'] : 'Order #' . $order['id']); ?></h3>
                <p><strong>Konsumen:</strong> <?php echo esc_html($order['customer_name']); ?></p>
                <p><strong>Telepon:</strong> <?php echo esc_html($order['customer_phone']); ?></p>
                <p><strong>Alamat:</strong> <?php echo nl2br(esc_html($order['customer_address'])); ?></p>
                <p><strong>Titik GPS:</strong> <?php echo esc_html($order['customer_gps'] !== '' ? $order['customer_gps'] : '-'); ?></p>
                <p><strong>Jadwal Kirim:</strong> <?php echo esc_html(trim($order['delivery_date'] . ' ' . $order['delivery_time'])); ?></p>
                <p><strong>Pembayaran:</strong> <?php echo esc_html(isset($payment_labels[$order['payment_method']]) ? $payment_labels[$order['payment_method']] : '-'); ?></p>
                <p><strong>Status:</strong> <?php echo esc_html(isset($status_labels[$order['order_status']]) ? $status_labels[$order['order_status']] : '-'); ?></p>

                <?php if (!empty($order['delivery_proof_url'])) : ?>
                    <p><strong>Bukti Pengiriman:</strong> <a href="<?php echo esc_url($order['delivery_proof_url']); ?>" target="_blank" rel="noopener noreferrer">Lihat bukti</a></p>
                <?php endif; ?>

                <?php if (!empty($order['cod_payment_proof_url'])) : ?>
                    <p><strong>Bukti Pembayaran COD:</strong> <a href="<?php echo esc_url($order['cod_payment_proof_url']); ?>" target="_blank" rel="noopener noreferrer">Lihat bukti</a></p>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field($nonce_action, $nonce_name); ?>
                    <input type="hidden" name="custom_plugin_courier_action" value="update_order">
                    <input type="hidden" name="order_id" value="<?php echo esc_attr((string) $order['id']); ?>">

                    <p>
                        <label for="order-status-<?php echo esc_attr((string) $order['id']); ?>"><strong>Update Status</strong></label><br>
                        <select id="order-status-<?php echo esc_attr((string) $order['id']); ?>" name="order_status">
                            <?php foreach ($status_labels as $status_value => $status_label) : ?>
                                <option value="<?php echo esc_attr($status_value); ?>" <?php selected($order['order_status'], $status_value); ?>><?php echo esc_html($status_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>

                    <p>
                        <label for="delivery-proof-file-<?php echo esc_attr((string) $order['id']); ?>"><strong>Upload Bukti Pengiriman</strong></label><br>
                        <input type="file" id="delivery-proof-file-<?php echo esc_attr((string) $order['id']); ?>" name="delivery_proof_file" accept="image/*,.pdf">
                    </p>
                    <p>
                        <label for="delivery-proof-url-<?php echo esc_attr((string) $order['id']); ?>"><strong>Atau URL Bukti Pengiriman</strong></label><br>
                        <input type="url" id="delivery-proof-url-<?php echo esc_attr((string) $order['id']); ?>" name="delivery_proof_url" value="<?php echo esc_attr($order['delivery_proof_url']); ?>" style="width:100%;max-width:480px;">
                    </p>

                    <?php if ($order['payment_method'] === 'cod') : ?>
                        <p>
                            <label for="cod-proof-file-<?php echo esc_attr((string) $order['id']); ?>"><strong>Upload Bukti Pembayaran COD</strong></label><br>
                            <input type="file" id="cod-proof-file-<?php echo esc_attr((string) $order['id']); ?>" name="cod_payment_proof_file" accept="image/*,.pdf">
                        </p>
                        <p>
                            <label for="cod-proof-url-<?php echo esc_attr((string) $order['id']); ?>"><strong>Atau URL Bukti Pembayaran COD</strong></label><br>
                            <input type="url" id="cod-proof-url-<?php echo esc_attr((string) $order['id']); ?>" name="cod_payment_proof_url" value="<?php echo esc_attr($order['cod_payment_proof_url']); ?>" style="width:100%;max-width:480px;">
                        </p>
                    <?php endif; ?>

                    <p>
                        <button type="submit">Simpan Update</button>
                    </p>
                </form>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>