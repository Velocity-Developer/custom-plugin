<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="custom-plugin-customer-profile container py-4">
    <div class="row justify-content-center g-4">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link active" data-custom-plugin-tab="profile" role="tab" aria-controls="custom-plugin-tab-profile" aria-selected="true">
                            Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link" data-custom-plugin-tab="orders" role="tab" aria-controls="custom-plugin-tab-orders" aria-selected="false">
                            Riwayat Order
                        </button>
                    </li>
                </ul>

                <a class="btn btn-outline-secondary" href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>">Logout</a>
            </div>
        </div>

        <div class="col-12">
            <div id="custom-plugin-tab-profile" data-custom-plugin-tab-pane="profile" role="tabpanel">
                <?php echo \CustomPlugin\Core\Template::get('frontend/customer-registration', array(
                    'profile' => $profile,
                    'city_options' => $city_options,
                    'nonce_action' => $nonce_action,
                    'nonce_name' => $nonce_name,
                    'feedback' => $feedback,
                    'form_title' => $form_title,
                    'form_subtitle' => $form_subtitle,
                    'submit_label' => $submit_label,
                    'embedded' => true,
                )); ?>
            </div>

            <div id="custom-plugin-tab-orders" class="d-none" data-custom-plugin-tab-pane="orders" role="tabpanel">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
                            <div>
                                <h2 class="h4 mb-1">Riwayat Order</h2>
                                <p class="text-body-secondary mb-0">Daftar pesanan yang dibuat dari akun customer ini.</p>
                            </div>
                        </div>

                        <?php if (empty($orders)) : ?>
                            <div class="alert alert-secondary mb-0" role="alert">
                                Belum ada riwayat order untuk akun ini.
                            </div>
                        <?php else : ?>
                            <div class="table-responsive d-none d-lg-block">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Produk</th>
                                            <th>Pengiriman</th>
                                            <th>Pembayaran</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order) : ?>
                                            <?php $status_label = isset($status_labels[$order['order_status']]) ? $status_labels[$order['order_status']] : $order['order_status']; ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold"><?php echo esc_html($order['title']); ?></div>
                                                    <div class="small text-body-secondary"><?php echo esc_html($order['date']); ?></div>
                                                </td>
                                                <td>
                                                    <div><?php echo esc_html($order['product_name'] !== '' ? $order['product_name'] : '-'); ?></div>
                                                    <div class="small text-body-secondary">Qty: <?php echo esc_html((string) $order['quantity']); ?></div>
                                                </td>
                                                <td>
                                                    <div><?php echo esc_html($order['delivery_date'] !== '' ? $order['delivery_date'] : '-'); ?></div>
                                                    <div class="small text-body-secondary"><?php echo esc_html($order['delivery_time'] !== '' ? $order['delivery_time'] : '-'); ?></div>
                                                </td>
                                                <td><?php echo esc_html(isset($payment_labels[$order['payment_method']]) ? $payment_labels[$order['payment_method']] : '-'); ?></td>
                                                <td>
                                                    <div><?php echo esc_html(\CustomPlugin\Frontend\Frontend::get_formatted_price($order['total_price'])); ?></div>
                                                    <div class="small text-body-secondary">Satuan: <?php echo esc_html(\CustomPlugin\Frontend\Frontend::get_formatted_price($order['unit_price'])); ?></div>
                                                </td>
                                                <td><span class="badge text-bg-secondary"><?php echo esc_html($status_label); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-lg-none">
                                <div class="row g-3">
                                    <?php foreach ($orders as $order) : ?>
                                        <?php $status_label = isset($status_labels[$order['order_status']]) ? $status_labels[$order['order_status']] : $order['order_status']; ?>
                                        <div class="col-12">
                                            <div class="border rounded p-3">
                                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                                    <div>
                                                        <div class="fw-semibold"><?php echo esc_html($order['title']); ?></div>
                                                        <div class="small text-body-secondary"><?php echo esc_html($order['date']); ?></div>
                                                    </div>
                                                    <span class="badge text-bg-secondary"><?php echo esc_html($status_label); ?></span>
                                                </div>

                                                <dl class="row mb-0 small">
                                                    <dt class="col-4 text-body-secondary fw-normal">Produk</dt>
                                                    <dd class="col-8"><?php echo esc_html($order['product_name'] !== '' ? $order['product_name'] : '-'); ?></dd>

                                                    <dt class="col-4 text-body-secondary fw-normal">Jumlah</dt>
                                                    <dd class="col-8"><?php echo esc_html((string) $order['quantity']); ?></dd>

                                                    <dt class="col-4 text-body-secondary fw-normal">Kirim</dt>
                                                    <dd class="col-8"><?php echo esc_html(($order['delivery_date'] !== '' ? $order['delivery_date'] : '-') . ' ' . ($order['delivery_time'] !== '' ? $order['delivery_time'] : '')); ?></dd>

                                                    <dt class="col-4 text-body-secondary fw-normal">Bayar</dt>
                                                    <dd class="col-8"><?php echo esc_html(isset($payment_labels[$order['payment_method']]) ? $payment_labels[$order['payment_method']] : '-'); ?></dd>

                                                    <dt class="col-4 text-body-secondary fw-normal">Total</dt>
                                                    <dd class="col-8"><?php echo esc_html(\CustomPlugin\Frontend\Frontend::get_formatted_price($order['total_price'])); ?></dd>
                                                </dl>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function() {
        function setActive(tabKey) {
            var buttons = document.querySelectorAll('[data-custom-plugin-tab]');
            var panes = document.querySelectorAll('[data-custom-plugin-tab-pane]');

            buttons.forEach(function(btn) {
                var isActive = btn.getAttribute('data-custom-plugin-tab') === tabKey;
                btn.classList.toggle('active', isActive);
                btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panes.forEach(function(pane) {
                var isActive = pane.getAttribute('data-custom-plugin-tab-pane') === tabKey;
                pane.classList.toggle('d-none', !isActive);
            });
        }

        document.addEventListener('click', function(e) {
            var button = e.target.closest('[data-custom-plugin-tab]');
            if (!button) {
                return;
            }
            e.preventDefault();
            var tabKey = button.getAttribute('data-custom-plugin-tab');
            setActive(tabKey);
            if (tabKey === 'orders') {
                history.replaceState(null, '', '#orders');
            } else {
                history.replaceState(null, '', '#profile');
            }
        });

        var initial = location.hash === '#orders' ? 'orders' : 'profile';
        setActive(initial);
    })();
</script>