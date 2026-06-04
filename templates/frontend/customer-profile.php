<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="custom-plugin-customer-profile container py-4">
    <div class="row justify-content-center g-4">
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <h2 class="h4 mb-1">Ringkasan Customer</h2>
                        <p class="text-body-secondary mb-0">Status akun dan data utama customer.</p>
                    </div>

                    <dl class="row mb-0">
                        <dt class="col-5 text-body-secondary fw-normal">Nama</dt>
                        <dd class="col-7"><?php echo esc_html($profile['full_name'] !== '' ? $profile['full_name'] : '-'); ?></dd>

                        <dt class="col-5 text-body-secondary fw-normal">Role</dt>
                        <dd class="col-7"><?php echo esc_html($profile['has_customer_role'] ? 'Customer' : 'Belum customer'); ?></dd>

                        <dt class="col-5 text-body-secondary fw-normal">Kota</dt>
                        <dd class="col-7"><?php echo esc_html($profile['city_label'] !== '' ? $profile['city_label'] : '-'); ?></dd>

                        <dt class="col-5 text-body-secondary fw-normal">Alamat</dt>
                        <dd class="col-7"><?php echo nl2br(esc_html($profile['address'] !== '' ? $profile['address'] : '-')); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <?php echo \CustomPlugin\Core\Template::get('frontend/customer-registration', array(
                'profile' => $profile,
                'city_options' => $city_options,
                'nonce_action' => $nonce_action,
                'nonce_name' => $nonce_name,
                'feedback' => $feedback,
                'form_title' => $form_title,
                'form_subtitle' => $form_subtitle,
                'submit_label' => $submit_label,
            )); ?>
        </div>
    </div>
</div>
