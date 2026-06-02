<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('custom_plugin_courier_dashboard_status_badge_class')) {
    function custom_plugin_courier_dashboard_status_badge_class($status)
    {
        $map = array(
            'delivering' => 'text-bg-warning',
            'delivered'  => 'text-bg-success',
            'completed'  => 'text-bg-success',
            'cancelled'  => 'text-bg-danger',
            'pending'    => 'text-bg-secondary',
            'scheduled'  => 'text-bg-info',
            'processing' => 'text-bg-primary',
        );

        return isset($map[$status]) ? $map[$status] : 'text-bg-light border';
    }
}
?>
<div class="custom-plugin-courier-dashboard container py-4">
    <style>
        .custom-plugin-courier-dashboard .courier-mobile-order-card {
            border-radius: 0.5rem;
        }

        .custom-plugin-courier-dashboard .courier-proof-preview img {
            max-height: 360px;
            object-fit: contain;
        }

        @media (max-width: 575.98px) {
            .custom-plugin-courier-dashboard {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .custom-plugin-courier-dashboard .courier-proof-preview img {
                max-height: 260px;
            }
        }
    </style>

    <div class="row justify-content-center">
        <div class="col-12">
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
                <div class="card shadow-sm border-0 d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Order</th>
                                    <th scope="col">Customer</th>
                                    <th scope="col">Kontak</th>
                                    <th scope="col">Jadwal</th>
                                    <th scope="col">Pembayaran</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order) : ?>
                                    <?php
                                    $modal_id = 'courier-order-modal-' . $order['id'];
                                    $status_badge_class = custom_plugin_courier_dashboard_status_badge_class($order['order_status']);
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo esc_html($order['title'] !== '' ? $order['title'] : 'Order #' . $order['id']); ?></div>
                                            <div class="text-body-secondary small">ID #<?php echo esc_html((string) $order['id']); ?></div>
                                        </td>
                                        <td><?php echo esc_html($order['customer_name'] !== '' ? $order['customer_name'] : '-'); ?></td>
                                        <td>
                                            <div><?php echo esc_html($order['customer_phone'] !== '' ? $order['customer_phone'] : '-'); ?></div>
                                            <div class="small text-body-secondary"><?php echo esc_html($order['customer_gps'] !== '' ? $order['customer_gps'] : '-'); ?></div>
                                        </td>
                                        <td><?php echo esc_html(trim($order['delivery_date'] . ' ' . $order['delivery_time']) !== '' ? trim($order['delivery_date'] . ' ' . $order['delivery_time']) : '-'); ?></td>
                                        <td><span class="badge text-bg-light border"><?php echo esc_html(isset($payment_labels[$order['payment_method']]) ? $payment_labels[$order['payment_method']] : '-'); ?></span></td>
                                        <td><span class="badge <?php echo esc_attr($status_badge_class); ?>"><?php echo esc_html(isset($status_labels[$order['order_status']]) ? $status_labels[$order['order_status']] : '-'); ?></span></td>
                                        <td class="text-end">
                                            <?php if (!empty($order['can_edit'])) : ?>
                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#<?php echo esc_attr($modal_id); ?>">
                                                    Edit
                                                </button>
                                            <?php else : ?>
                                                <span class="text-body-secondary small">Terkunci</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-md-none">
                    <div class="vstack gap-3">
                        <?php foreach ($orders as $order) : ?>
                            <?php
                            $modal_id = 'courier-order-modal-' . $order['id'];
                            $status_badge_class = custom_plugin_courier_dashboard_status_badge_class($order['order_status']);
                            $delivery_schedule = trim($order['delivery_date'] . ' ' . $order['delivery_time']);
                            ?>
                            <section class="card courier-mobile-order-card shadow-sm border-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                        <div class="min-w-0">
                                            <h3 class="h6 mb-1"><?php echo esc_html($order['title'] !== '' ? $order['title'] : 'Order #' . $order['id']); ?></h3>
                                            <div class="text-body-secondary small">ID #<?php echo esc_html((string) $order['id']); ?></div>
                                        </div>
                                        <span class="badge <?php echo esc_attr($status_badge_class); ?> flex-shrink-0"><?php echo esc_html(isset($status_labels[$order['order_status']]) ? $status_labels[$order['order_status']] : '-'); ?></span>
                                    </div>

                                    <dl class="row small mb-3 gy-2">
                                        <dt class="col-4 text-body-secondary fw-normal">Customer</dt>
                                        <dd class="col-8 mb-0 text-end"><?php echo esc_html($order['customer_name'] !== '' ? $order['customer_name'] : '-'); ?></dd>

                                        <dt class="col-4 text-body-secondary fw-normal">Telepon</dt>
                                        <dd class="col-8 mb-0 text-end"><?php echo esc_html($order['customer_phone'] !== '' ? $order['customer_phone'] : '-'); ?></dd>

                                        <dt class="col-4 text-body-secondary fw-normal">Jadwal</dt>
                                        <dd class="col-8 mb-0 text-end"><?php echo esc_html($delivery_schedule !== '' ? $delivery_schedule : '-'); ?></dd>

                                        <dt class="col-4 text-body-secondary fw-normal">Bayar</dt>
                                        <dd class="col-8 mb-0 text-end"><?php echo esc_html(isset($payment_labels[$order['payment_method']]) ? $payment_labels[$order['payment_method']] : '-'); ?></dd>
                                    </dl>

                                    <?php if (!empty($order['can_edit'])) : ?>
                                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#<?php echo esc_attr($modal_id); ?>">
                                            Edit Order
                                        </button>
                                    <?php else : ?>
                                        <button type="button" class="btn btn-outline-secondary w-100" disabled>
                                            Tidak Bisa Diedit
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </section>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php foreach ($orders as $order) : ?>
                    <?php $modal_id = 'courier-order-modal-' . $order['id']; ?>
                    <div class="modal fade" id="<?php echo esc_attr($modal_id); ?>" tabindex="-1" aria-labelledby="<?php echo esc_attr($modal_id); ?>-label" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title" id="<?php echo esc_attr($modal_id); ?>-label"><?php echo esc_html($order['title'] !== '' ? $order['title'] : 'Order #' . $order['id']); ?></h5>
                                        <div class="text-body-secondary small">ID Order: #<?php echo esc_html((string) $order['id']); ?></div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-4">
                                        <div class="col-12 col-lg-5">
                                            <div class="card bg-light border-0 h-100">
                                                <div class="card-body">
                                                    <h6 class="mb-3">Detail Konsumen</h6>
                                                    <dl class="row mb-0">
                                                        <dt class="col-sm-4">Nama</dt>
                                                        <dd class="col-sm-8"><?php echo esc_html($order['customer_name'] !== '' ? $order['customer_name'] : '-'); ?></dd>

                                                        <dt class="col-sm-4">Telepon</dt>
                                                        <dd class="col-sm-8"><?php echo esc_html($order['customer_phone'] !== '' ? $order['customer_phone'] : '-'); ?></dd>

                                                        <dt class="col-sm-4">Alamat</dt>
                                                        <dd class="col-sm-8"><?php echo nl2br(esc_html($order['customer_address'])); ?></dd>

                                                        <dt class="col-sm-4">Titik GPS</dt>
                                                        <dd class="col-sm-8"><?php echo esc_html($order['customer_gps'] !== '' ? $order['customer_gps'] : '-'); ?></dd>

                                                        <dt class="col-sm-4">Jadwal</dt>
                                                        <dd class="col-sm-8"><?php echo esc_html(trim($order['delivery_date'] . ' ' . $order['delivery_time']) !== '' ? trim($order['delivery_date'] . ' ' . $order['delivery_time']) : '-'); ?></dd>

                                                        <dt class="col-sm-4">Pembayaran</dt>
                                                        <dd class="col-sm-8"><?php echo esc_html(isset($payment_labels[$order['payment_method']]) ? $payment_labels[$order['payment_method']] : '-'); ?></dd>
                                                    </dl>

                                                    <?php if (!empty($order['delivery_proof_url'])) : ?>
                                                        <div class="mt-3">
                                                            <div class="courier-proof-preview border rounded overflow-hidden bg-white text-center">
                                                                <img
                                                                    src="<?php echo esc_url($order['delivery_proof_url']); ?>"
                                                                    alt="Preview bukti pengiriman"
                                                                    class="img-fluid"
                                                                    loading="lazy">
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($order['cod_payment_proof_url'])) : ?>
                                                        <div class="mt-3">
                                                            <div class="courier-proof-preview border rounded overflow-hidden bg-white text-center">
                                                                <img
                                                                    src="<?php echo esc_url($order['cod_payment_proof_url']); ?>"
                                                                    alt="Preview bukti pembayaran COD"
                                                                    class="img-fluid"
                                                                    loading="lazy">
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if (!empty($order['can_edit'])) : ?>
                                            <div class="col-12 col-lg-7">
                                                <div class="card border-0 bg-body-tertiary h-100">
                                                    <div class="card-body">
                                                        <h6 class="mb-3">Update Pengiriman</h6>

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

                                                            <div class="col-12 d-grid d-sm-flex justify-content-sm-end gap-2">
                                                                <button type="button" class="btn btn-outline-secondary order-2 order-sm-1" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary order-1 order-sm-2">Simpan Update</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>