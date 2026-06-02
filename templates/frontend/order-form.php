<?php

use CustomPlugin\Frontend\Frontend;

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="custom-plugin-order-form">
    <h2>Form Order</h2>

    <?php if (!empty($feedback)) : ?>
        <p><strong><?php echo esc_html($feedback); ?></strong></p>
    <?php endif; ?>

    <?php if (empty($products)) : ?>
        <p>Produk belum tersedia.</p>
    <?php else : ?>
        <form method="post">
            <?php wp_nonce_field($nonce_action, $nonce_name); ?>
            <input type="hidden" name="custom_plugin_frontend_action" value="submit_order">

            <p>
                <label for="product-id"><strong>Pilih Produk</strong></label><br>
                <select id="product-id" name="product_id" required>
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
            </p>

            <p>
                <label for="quantity"><strong>Jumlah</strong></label><br>
                <input type="number" min="1" step="1" id="quantity" name="quantity" value="<?php echo esc_attr((string) $old['quantity']); ?>" required>
            </p>

            <p>
                <label for="customer-name"><strong>Nama</strong></label><br>
                <input type="text" id="customer-name" name="customer_name" value="<?php echo esc_attr($old['customer_name']); ?>" required style="width:100%;max-width:480px;">
            </p>

            <p>
                <label for="customer-phone"><strong>No. Telepon</strong></label><br>
                <input type="text" id="customer-phone" name="customer_phone" value="<?php echo esc_attr($old['customer_phone']); ?>" required style="width:100%;max-width:480px;">
            </p>

            <p>
                <label for="customer-address"><strong>Alamat Lengkap</strong></label><br>
                <textarea id="customer-address" name="customer_address" rows="4" required style="width:100%;max-width:480px;"><?php echo esc_textarea($old['customer_address']); ?></textarea>
            </p>

            <p>
                <label for="customer-gps"><strong>Titik GPS</strong></label><br>
                <input type="text" id="customer-gps" name="customer_gps" value="<?php echo esc_attr($old['customer_gps']); ?>" placeholder="-6.200000, 106.816666" style="width:100%;max-width:480px;">
            </p>

            <p>
                <label for="delivery-date"><strong>Tanggal Kirim</strong></label><br>
                <input type="date" id="delivery-date" name="delivery_date" value="<?php echo esc_attr($old['delivery_date']); ?>" required>
            </p>

            <p>
                <label for="delivery-time"><strong>Jam Kirim</strong></label><br>
                <input type="time" id="delivery-time" name="delivery_time" value="<?php echo esc_attr($old['delivery_time']); ?>" required>
            </p>

            <p>
                <label for="payment-method"><strong>Metode Pembayaran</strong></label><br>
                <select id="payment-method" name="payment_method" required>
                    <option value="">Pilih metode pembayaran</option>
                    <?php foreach ($payment_methods as $value => $label) : ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($old['payment_method'], $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>

            <p>
                <button type="submit">Kirim Order</button>
            </p>
        </form>
    <?php endif; ?>
</div>