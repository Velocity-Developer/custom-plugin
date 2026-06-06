<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="custom-plugin-customer-form container py-4">
    <style>
        .custom-plugin-customer-form .custom-plugin-submit-customer-btn {
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
        }
    </style>
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <h2 class="h3 mb-2"><?php echo esc_html($form_title); ?></h2>
                        <p class="text-body-secondary mb-0"><?php echo esc_html($form_subtitle); ?></p>
                    </div>

                    <?php if (!empty($feedback)) : ?>
                        <div class="alert <?php echo isset($_GET['customer_status']) && $_GET['customer_status'] === 'saved' ? 'alert-success' : 'alert-warning'; ?> mb-4" role="alert">
                            <?php echo esc_html($feedback); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data" class="row g-3">
                        <?php wp_nonce_field($nonce_action, $nonce_name); ?>
                        <input type="hidden" name="custom_plugin_frontend_action" value="submit_customer_profile">

                        <div class="col-12">
                            <label for="customer-full-name" class="form-label">Nama Lengkap</label>
                            <input type="text" id="customer-full-name" name="customer_full_name" class="form-control" value="<?php echo esc_attr($profile['full_name']); ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="customer-whatsapp" class="form-label">No. WhatsApp</label>
                            <input type="text" id="customer-whatsapp" name="customer_whatsapp" class="form-control" value="<?php echo esc_attr(isset($profile['whatsapp']) ? $profile['whatsapp'] : ''); ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="customer-address" class="form-label">Alamat</label>
                            <textarea id="customer-address" name="customer_address" rows="4" class="form-control" required><?php echo esc_textarea($profile['address']); ?></textarea>
                        </div>

                        <div class="col-12">
                            <label for="customer-city" class="form-label">Kota</label>
                            <select id="customer-city" name="customer_city" class="form-select" required>
                                <option value="">Pilih kota</option>
                                <?php foreach ($city_options as $city) : ?>
                                    <option value="<?php echo esc_attr($city['value']); ?>" <?php selected($profile['city'], $city['value']); ?>>
                                        <?php echo esc_html($city['label']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="customer-ktp-photo" class="form-label">Foto KTP</label>
                            <input type="file" id="customer-ktp-photo" name="customer_ktp_photo" class="form-control" accept="image/*" <?php echo $profile['ktp_attachment_id'] > 0 ? '' : 'required'; ?>>
                            <div class="form-text">Format gambar umum seperti JPG, JPEG, atau PNG.</div>
                        </div>

                        <?php if (!empty($profile['ktp_image_html'])) : ?>
                            <div class="col-12">
                                <div class="border rounded p-3 bg-light">
                                    <div class="fw-semibold mb-2">Foto KTP Saat Ini</div>
                                    <?php echo $profile['ktp_image_html']; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-lg custom-plugin-submit-customer-btn"><?php echo esc_html($submit_label); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>