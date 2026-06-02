<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="custom-plugin-courier-dashboard container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mb-4">
                <div>
                    <h2 class="h3 mb-2">Dashboard Kurir</h2>
                    <p class="text-body-secondary mb-0">Halo, <?php echo esc_html($current_user->display_name); ?>. Berikut daftar order yang ditugaskan ke akun Anda.</p>
                </div>
                <div class="badge text-bg-primary fs-6"><?php echo esc_html((string) count($orders)); ?> Order</div>
            </div>

            <?php if (!empty($feedback)) : ?>
                <div class="alert alert-info mb-4" role="alert">
                    <?php echo esc_html($feedback); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($orders)) : ?>
                <div class="alert alert-secondary mb-0" role="alert">
                    Belum ada order yang ditugaskan.
                </div>
            <?php else : ?>
                <div class="row g-4">
                    <?php foreach ($orders as $order) : ?>
                        <div class="col-12">
                            <section class="card shadow-sm border-0 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                                        <div>
                                            <h3 class="h5 mb-1"><?php echo esc_html($order['title'] !== '' ? $order['title'] : 'Order #' . $order['id']); ?></h3>
                                            <div class="text-body-secondary small">ID Order: #<?php echo esc_html((string) $order['id']); ?></div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge text-bg-light border"><?php echo esc_html(isset($payment_labels[$order['payment_method']]) ? $payment_labels[$order['payment_method']] : '-'); ?></span>
                                            <span class="badge text-bg-success"><?php echo esc_html(isset($status_labels[$order['order_status']]) ? $status_labels[$order['order_status']] : '-'); ?></span>
                                        </div>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-12 col-lg-6">
                                            <div class="card bg-light border-0 h-100">
                                                <div class="card-body">
                                                    <h4 class="h6 mb-3">Detail Konsumen</h4>
                                                    <dl class="row mb-0">
                                                        <dt class="col-sm-4">Nama</dt>
                                                        <dd class="col-sm-8"><?php echo esc_html($order['customer_name']); ?></dd>

                                                        <dt class="col-sm-4">Telepon</dt>
                                                        <dd class="col-sm-8"><?php echo esc_html($order['customer_phone']); ?></dd>

                                                        <dt class="col-sm-4">Alamat</dt>
                                                        <dd class="col-sm-8"><?php echo nl2br(esc_html($order['customer_address'])); ?></dd>

                                                        <dt class="col-sm-4">Titik GPS</dt>
                                                        <dd class="col-sm-8"><?php echo esc_html($order['customer_gps'] !== '' ? $order['customer_gps'] : '-'); ?></dd>

                                                        <dt class="col-sm-4">Jadwal Kirim</dt>
                                                        <dd class="col-sm-8"><?php echo esc_html(trim($order['delivery_date'] . ' ' . $order['delivery_time'])); ?></dd>
                                                    </dl>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-6">
                                            <div class="card bg-light border-0 h-100">
                                                <div class="card-body">
                                                    <h4 class="h6 mb-3">Update Pengiriman</h4>

                                                    <?php if (!empty($order['delivery_proof_url'])) : ?>
                                                        <div class="mb-2">
                                                            <a class="link-primary" href="<?php echo esc_url($order['delivery_proof_url']); ?>" target="_blank" rel="noopener noreferrer">Lihat bukti pengiriman</a>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($order['cod_payment_proof_url'])) : ?>
                                                        <div class="mb-3">
                                                            <a class="link-primary" href="<?php echo esc_url($order['cod_payment_proof_url']); ?>" target="_blank" rel="noopener noreferrer">Lihat bukti pembayaran COD</a>
                                                        </div>
                                                    <?php endif; ?>

                                                    <form method="post" enctype="multipart/form-data" class="row g-3">
                                                        <?php wp_nonce_field($nonce_action, $nonce_name); ?>
                                                        <input type="hidden" name="custom_plugin_courier_action" value="update_order">
                                                        <input type="hidden" name="order_id" value="<?php echo esc_attr((string) $order['id']); ?>">

                                                        <div class="col-12">
                                                            <label for="order-status-<?php echo esc_attr((string) $order['id']); ?>" class="form-label">Update Status</label>
                                                            <select id="order-status-<?php echo esc_attr((string) $order['id']); ?>" name="order_status" class="form-select">
                                                                <?php foreach ($courier_status_labels as $status_value => $status_label) : ?>
                                                                    <option value="<?php echo esc_attr($status_value); ?>" <?php selected($order['order_status'], $status_value); ?>><?php echo esc_html($status_label); ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="col-12">
                                                            <label for="delivery-proof-file-<?php echo esc_attr((string) $order['id']); ?>" class="form-label">Upload Bukti Pengiriman</label>
                                                            <input type="file" id="delivery-proof-file-<?php echo esc_attr((string) $order['id']); ?>" name="delivery_proof_file" class="form-control" accept="image/*,.pdf">
                                                        </div>

                                                        <div class="col-12">
                                                            <label for="delivery-proof-url-<?php echo esc_attr((string) $order['id']); ?>" class="form-label">Atau URL Bukti Pengiriman</label>
                                                            <input type="url" id="delivery-proof-url-<?php echo esc_attr((string) $order['id']); ?>" name="delivery_proof_url" class="form-control" value="<?php echo esc_attr($order['delivery_proof_url']); ?>">
                                                        </div>

                                                        <?php if ($order['payment_method'] === 'cod') : ?>
                                                            <div class="col-12">
                                                                <label for="cod-proof-file-<?php echo esc_attr((string) $order['id']); ?>" class="form-label">Upload Bukti Pembayaran COD</label>
                                                                <input type="file" id="cod-proof-file-<?php echo esc_attr((string) $order['id']); ?>" name="cod_payment_proof_file" class="form-control" accept="image/*,.pdf">
                                                            </div>

                                                            <div class="col-12">
                                                                <label for="cod-proof-url-<?php echo esc_attr((string) $order['id']); ?>" class="form-label">Atau URL Bukti Pembayaran COD</label>
                                                                <input type="url" id="cod-proof-url-<?php echo esc_attr((string) $order['id']); ?>" name="cod_payment_proof_url" class="form-control" value="<?php echo esc_attr($order['cod_payment_proof_url']); ?>">
                                                            </div>
                                                        <?php endif; ?>

                                                        <div class="col-12">
                                                            <button type="submit" class="btn btn-primary">Simpan Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>