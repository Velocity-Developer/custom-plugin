<?php

if (!defined('ABSPATH')) {
    exit;
}

$lock_fields = isset($lock_fields) && is_array($lock_fields) ? $lock_fields : array();
?>
<div class="custom-plugin-order-form container py-4">
    <style>
        .custom-plugin-order-form .custom-plugin-submit-order-btn {
            --bs-btn-color: #fff;
            --bs-btn-bg: #F83C89;
            --bs-btn-border-color: #F83C89;
            --bs-btn-hover-color: #fff;
            --bs-btn-hover-bg: #e2357c;
            --bs-btn-hover-border-color: #e2357c;
            --bs-btn-focus-shadow-rgb: 248, 60, 137;
            --bs-btn-active-color: #fff;
            --bs-btn-active-bg: #d82f73;
            --bs-btn-active-border-color: #d82f73;
            --bs-btn-disabled-color: #fff;
            --bs-btn-disabled-bg: #F83C89;
            --bs-btn-disabled-border-color: #F83C89;
        }
    </style>
    <div class="row justify-content-center">
        <div class="col-12">
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
                                        <option
                                            value="<?php echo esc_attr((string) $product['id']); ?>"
                                            data-base-price="<?php echo esc_attr((string) $product['price']); ?>"
                                            data-city-prices="<?php echo esc_attr(wp_json_encode($product['city_prices'])); ?>"
                                            <?php selected($old['product_id'], $product['id']); ?>>
                                            <?php echo esc_html($product['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="quantity" class="form-label">Jumlah</label>
                                <input type="number" min="1" step="1" id="quantity" name="quantity" class="form-control" value="<?php echo esc_attr((string) $old['quantity']); ?>" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="customer-city" class="form-label">Kota</label>
                                <select id="customer-city" name="customer_city" class="form-select" required <?php disabled($is_logged_in && $logged_in_city !== ''); ?>>
                                    <option value="">Pilih kota</option>
                                    <?php foreach ($city_options as $city) : ?>
                                        <option value="<?php echo esc_attr($city['value']); ?>" <?php selected($old['customer_city'], $city['value']); ?>>
                                            <?php echo esc_html($city['label']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($is_logged_in && $logged_in_city !== '') : ?>
                                    <input type="hidden" name="customer_city" value="<?php echo esc_attr($logged_in_city); ?>">
                                    <div class="form-text">Kota mengikuti data akun customer yang sedang login.</div>
                                <?php endif; ?>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="payment-method" class="form-label">Metode Pembayaran</label>
                                <select id="payment-method" name="payment_method" class="form-select" required>
                                    <option value="">Pilih metode pembayaran</option>
                                    <?php foreach ($payment_methods as $value => $label) : ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($old['payment_method'], $value); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="alert alert-light border mb-0" id="product-price-info" role="alert">
                                    Pilih produk dan kota untuk melihat harga yang sesuai.
                                </div>
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
                                <input type="text" id="customer-name" name="customer_name" class="form-control" value="<?php echo esc_attr($old['customer_name']); ?>" required <?php echo $is_logged_in && !empty($lock_fields['customer_name']) ? 'readonly' : ''; ?>>
                            </div>

                            <?php if ($is_logged_in) : ?>
                                <div class="col-12 col-md-6">
                                    <label for="customer-email" class="form-label">Email</label>
                                    <input type="email" id="customer-email" class="form-control" value="<?php echo esc_attr($logged_in_email); ?>" disabled>
                                </div>
                            <?php else : ?>
                                <div class="col-12 col-md-6">
                                    <label for="customer-email" class="form-label">Email</label>
                                    <input type="email" id="customer-email" name="customer_email" class="form-control" value="<?php echo esc_attr($old['customer_email']); ?>" required>
                                    <div class="form-text">Gunakan email yang sama untuk melihat riwayat order setelah login.</div>
                                </div>
                            <?php endif; ?>

                            <div class="col-12 col-md-6">
                                <label for="customer-phone" class="form-label">No. WhatsApp</label>
                                <input type="text" id="customer-phone" name="customer_phone" class="form-control" value="<?php echo esc_attr($old['customer_phone']); ?>" required <?php echo $is_logged_in && !empty($lock_fields['customer_phone']) ? 'readonly' : ''; ?>>
                            </div>

                            <div class="col-12">
                                <label for="customer-address" class="form-label">Alamat Lengkap</label>
                                <textarea id="customer-address" name="customer_address" rows="4" class="form-control" required <?php echo $is_logged_in && !empty($lock_fields['customer_address']) ? 'readonly' : ''; ?>><?php echo esc_textarea($old['customer_address']); ?></textarea>
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
                                <select id="delivery-time" name="delivery_time" class="form-select" required>
                                    <option value="">Pilih jam kirim</option>
                                    <?php foreach ($delivery_time_options as $delivery_time_option) : ?>
                                        <option value="<?php echo esc_attr($delivery_time_option); ?>" <?php selected($old['delivery_time'], $delivery_time_option); ?>>
                                            <?php echo esc_html($delivery_time_option); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 pt-2">
                                <button type="submit" class="btn btn-lg custom-plugin-submit-order-btn">Kirim Order</button>
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
        var productField = document.getElementById('product-id');
        var cityField = document.getElementById('customer-city');
        var quantityField = document.getElementById('quantity');
        var priceInfo = document.getElementById('product-price-info');

        function formatPrice(price) {
            return 'Rp ' + Number(price || 0).toLocaleString('id-ID');
        }

        function updateProductPriceInfo() {
            if (!productField || !cityField || !priceInfo) {
                return;
            }

            var selectedOption = productField.options[productField.selectedIndex];
            if (!selectedOption || !selectedOption.value) {
                priceInfo.textContent = 'Pilih produk dan kota untuk melihat harga yang sesuai.';
                return;
            }

            if (!cityField.value) {
                priceInfo.textContent = 'Pilih kota untuk melihat harga produk.';
                return;
            }

            var basePrice = selectedOption.getAttribute('data-base-price') || '0';
            var cityPrices = {};

            try {
                cityPrices = JSON.parse(selectedOption.getAttribute('data-city-prices') || '{}');
            } catch (error) {
                cityPrices = {};
            }

            var activePrice = Number(cityPrices[cityField.value] ? cityPrices[cityField.value] : basePrice);
            var quantity = quantityField ? Math.max(1, parseInt(quantityField.value || '1', 10)) : 1;
            var totalPrice = activePrice * quantity;

            priceInfo.innerHTML = 'Harga satuan: <strong>' + formatPrice(activePrice) + '</strong><br>Total ' + quantity + ' item: <strong>' + formatPrice(totalPrice) + '</strong>';
        }

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

        if (productField) {
            productField.addEventListener('change', updateProductPriceInfo);
        }

        if (cityField) {
            cityField.addEventListener('change', updateProductPriceInfo);
        }

        if (quantityField) {
            quantityField.addEventListener('input', updateProductPriceInfo);
            quantityField.addEventListener('change', updateProductPriceInfo);
        }

        updateProductPriceInfo();

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