<?php

use CustomPlugin\Frontend\Frontend;

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="custom-plugin-order-form container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <h2 class="h3 mb-2">Form Order</h2>
                        <p class="text-body-secondary mb-0">Isi data pesanan dan jadwal pengiriman yang Anda inginkan.</p>
                    </div>

                    <?php if (!empty($feedback)) : ?>
                        <div class="alert <?php echo isset($_GET['order_status']) && $_GET['order_status'] === 'created' ? 'alert-success' : 'alert-warning'; ?> mb-4" role="alert">
                            <?php echo esc_html($feedback); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($products)) : ?>
                        <div class="alert alert-secondary mb-0" role="alert">
                            Produk belum tersedia.
                        </div>
                    <?php else : ?>
                        <form method="post" class="row g-3">
                            <?php wp_nonce_field($nonce_action, $nonce_name); ?>
                            <input type="hidden" name="custom_plugin_frontend_action" value="submit_order">

                            <div class="col-12">
                                <label for="product-id" class="form-label">Pilih Produk</label>
                                <select id="product-id" name="product_id" class="form-select" required>
                                    <option value="">Pilih produk</option>
                                    <?php foreach ($products as $product) : ?>
                                        <option value="<?php echo esc_attr((string) $product['id']); ?>" <?php selected($old['product_id'], $product['id']); ?>>
                                            <?php
                                            $label = $product['title'];
                                            if ($product['price'] !== '') {
                                                $label .= ' - ' . Frontend::get_formatted_price((int) $product['price']);
                                            }
                                            echo esc_html($label);
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="quantity" class="form-label">Jumlah</label>
                                <input type="number" min="1" step="1" id="quantity" name="quantity" class="form-control" value="<?php echo esc_attr((string) $old['quantity']); ?>" required>
                            </div>

                            <div class="col-12 col-md-8">
                                <label for="payment-method" class="form-label">Metode Pembayaran</label>
                                <select id="payment-method" name="payment_method" class="form-select" required>
                                    <option value="">Pilih metode pembayaran</option>
                                    <?php foreach ($payment_methods as $value => $label) : ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($old['payment_method'], $value); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 d-none" id="payment-info-wrapper">
                                <div class="alert alert-light border mb-0" role="alert">
                                    <div class="fw-semibold mb-2">Informasi Pembayaran</div>
                                    <?php if (!empty($store_settings['banks'])) : ?>
                                        <div class="mb-3 d-none" id="bank-payment-info">
                                            <?php foreach ($store_settings['banks'] as $bank) : ?>
                                                <div class="border rounded p-3 mb-2 bg-white">
                                                    <div>Bank: <?php echo esc_html($bank['bank_name'] !== '' ? $bank['bank_name'] : '-'); ?></div>
                                                    <div>Rekening: <?php echo esc_html($bank['bank_account_number'] !== '' ? $bank['bank_account_number'] : '-'); ?></div>
                                                    <div>Atas Nama: <?php echo esc_html($bank['bank_account_name'] !== '' ? $bank['bank_account_name'] : '-'); ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($store_settings['qris_image_id'])) : ?>
                                        <div class="d-none" id="qris-payment-info">
                                            <?php echo wp_get_attachment_image((int) $store_settings['qris_image_id'], 'medium', false, array('class' => 'img-fluid rounded border', 'alt' => 'QRIS')); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                                <h3 class="h5 mb-0">Data Konsumen</h3>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="customer-name" class="form-label">Nama</label>
                                <input type="text" id="customer-name" name="customer_name" class="form-control" value="<?php echo esc_attr($old['customer_name']); ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="customer-phone" class="form-label">No. Telepon</label>
                                <input type="text" id="customer-phone" name="customer_phone" class="form-control" value="<?php echo esc_attr($old['customer_phone']); ?>" required>
                            </div>

                            <div class="col-12">
                                <label for="customer-address" class="form-label">Alamat Lengkap</label>
                                <textarea id="customer-address" name="customer_address" rows="4" class="form-control" required><?php echo esc_textarea($old['customer_address']); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label for="customer-gps" class="form-label">Titik GPS</label>
                                <div class="input-group">
                                    <input type="text" id="customer-gps" name="customer_gps" class="form-control" value="<?php echo esc_attr($old['customer_gps']); ?>" placeholder="-6.200000, 106.816666">
                                    <button type="button" class="btn btn-outline-secondary" id="detect-location-button">Gunakan Lokasi Saya</button>
                                </div>
                                <div class="form-text" id="location-help-text">Browser akan meminta izin akses lokasi untuk mengisi koordinat otomatis.</div>
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                                <h3 class="h5 mb-0">Jadwal Pengiriman</h3>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="delivery-date" class="form-label">Tanggal Kirim</label>
                                <input type="date" id="delivery-date" name="delivery_date" class="form-control" value="<?php echo esc_attr($old['delivery_date']); ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="delivery-time" class="form-label">Jam Kirim</label>
                                <input type="time" id="delivery-time" name="delivery_time" class="form-control" value="<?php echo esc_attr($old['delivery_time']); ?>" required>
                            </div>

                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-primary btn-lg">Kirim Order</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var button = document.getElementById('detect-location-button');
        var gpsField = document.getElementById('customer-gps');
        var helpText = document.getElementById('location-help-text');
        var paymentMethod = document.getElementById('payment-method');
        var paymentInfoWrapper = document.getElementById('payment-info-wrapper');
        var bankPaymentInfo = document.getElementById('bank-payment-info');
        var qrisPaymentInfo = document.getElementById('qris-payment-info');

        if (!button || !gpsField || !helpText) {
            return;
        }

        function updatePaymentInfo() {
            if (!paymentMethod || !paymentInfoWrapper) {
                return;
            }

            paymentInfoWrapper.classList.add('d-none');
            if (bankPaymentInfo) {
                bankPaymentInfo.classList.add('d-none');
            }
            if (qrisPaymentInfo) {
                qrisPaymentInfo.classList.add('d-none');
            }

            if (paymentMethod.value === 'bank_transfer' && bankPaymentInfo) {
                paymentInfoWrapper.classList.remove('d-none');
                bankPaymentInfo.classList.remove('d-none');
            }

            if (paymentMethod.value === 'digital_wallet' && qrisPaymentInfo) {
                paymentInfoWrapper.classList.remove('d-none');
                qrisPaymentInfo.classList.remove('d-none');
            }
        }

        if (paymentMethod) {
            paymentMethod.addEventListener('change', updatePaymentInfo);
            updatePaymentInfo();
        }

        button.addEventListener('click', function() {
            if (!navigator.geolocation) {
                helpText.textContent = 'Browser ini tidak mendukung akses lokasi.';
                return;
            }

            button.disabled = true;
            helpText.textContent = 'Meminta izin akses lokasi dan mengambil koordinat...';

            navigator.geolocation.getCurrentPosition(function(position) {
                gpsField.value = position.coords.latitude.toFixed(6) + ', ' + position.coords.longitude.toFixed(6);
                helpText.textContent = 'Koordinat berhasil diisi otomatis.';
                button.disabled = false;
            }, function() {
                helpText.textContent = 'Izin lokasi ditolak atau koordinat tidak bisa diambil. Silakan isi manual.';
                button.disabled = false;
            }, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            });
        });
    });
</script>