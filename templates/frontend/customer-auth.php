<?php

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="custom-plugin-customer-auth container py-4">
    <style>
        .custom-plugin-customer-auth .nav-pills .nav-link.active {
            background-color: #F83C89;
        }

        .custom-plugin-customer-auth #loginform {
            display: grid;
            gap: 16px;
            margin: 0;
        }

        .custom-plugin-customer-auth #loginform p {
            margin: 0;
        }

        .custom-plugin-customer-auth #loginform label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .custom-plugin-customer-auth #loginform input[type="text"],
        .custom-plugin-customer-auth #loginform input[type="email"],
        .custom-plugin-customer-auth #loginform input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ced4da;
            border-radius: 8px;
        }

        .custom-plugin-customer-auth #loginform input[type="text"]:focus,
        .custom-plugin-customer-auth #loginform input[type="email"]:focus,
        .custom-plugin-customer-auth #loginform input[type="password"]:focus {
            border-color: #F83C89;
            outline: 0;
            box-shadow: 0 0 0 .25rem rgba(248, 60, 137, .25);
        }

        .custom-plugin-customer-auth #loginform .login-remember label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 400;
            margin: 0;
        }

        .custom-plugin-customer-auth #loginform .login-remember input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin: 0;
        }

        .custom-plugin-customer-auth #loginform input[type="submit"],
        .custom-plugin-customer-auth .custom-plugin-auth-btn {
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

        .custom-plugin-customer-auth #loginform input[type="submit"] {
            color: #fff;
            background-color: #F83C89;
            border-color: #F83C89;
            border-radius: 10px;
            padding: 12px 14px;
            width: 100%;
        }
    </style>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="mb-4">
                        <h2 class="h3 mb-2">Akses Profile Customer</h2>
                        <p class="text-body-secondary mb-0"><?php echo esc_html($message); ?></p>
                    </div>

                    <?php if (!empty($feedback)) : ?>
                        <div class="alert <?php echo isset($_GET['customer_auth_status']) && $_GET['customer_auth_status'] === 'registered' ? 'alert-success' : 'alert-warning'; ?> mb-4" role="alert">
                            <?php echo esc_html($feedback); ?>
                        </div>
                    <?php endif; ?>

                    <ul class="nav nav-pills mb-4" id="customer-auth-tabs" role="tablist">
                        <?php $register_tab_active = isset($_GET['customer_auth_status']) && sanitize_text_field(wp_unslash($_GET['customer_auth_status'])) !== 'registered'; ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $register_tab_active ? '' : 'active'; ?>" id="customer-login-tab" data-bs-toggle="pill" data-bs-target="#customer-login-panel" type="button" role="tab" aria-controls="customer-login-panel" aria-selected="<?php echo $register_tab_active ? 'false' : 'true'; ?>">Login</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo $register_tab_active ? 'active' : ''; ?>" id="customer-register-tab" data-bs-toggle="pill" data-bs-target="#customer-register-panel" type="button" role="tab" aria-controls="customer-register-panel" aria-selected="<?php echo $register_tab_active ? 'true' : 'false'; ?>">Register</button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade <?php echo $register_tab_active ? '' : 'show active'; ?>" id="customer-login-panel" role="tabpanel" aria-labelledby="customer-login-tab" tabindex="0">
                            <div class="mb-3">
                                <div class="fw-semibold mb-1">Login Customer</div>
                                <div class="text-body-secondary small">Masuk dengan akun yang sudah terdaftar.</div>
                            </div>
                            <?php
                            wp_login_form(array(
                                'redirect'       => get_permalink(),
                                'label_username' => 'Email / Username',
                                'label_password' => 'Password',
                                'label_remember' => 'Ingat saya',
                                'label_log_in'   => 'Login',
                                'remember'       => true,
                            ));
                            ?>
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mt-3">
                                <a class="small" href="<?php echo esc_url(wp_lostpassword_url(get_permalink())); ?>">Lupa password?</a>
                                <button type="button" class="btn btn-link p-0 small text-decoration-none" data-bs-toggle="pill" data-bs-target="#customer-register-panel" aria-controls="customer-register-panel">
                                    Belum punya akun? Register
                                </button>
                            </div>
                        </div>

                        <div class="tab-pane fade <?php echo $register_tab_active ? 'show active' : ''; ?>" id="customer-register-panel" role="tabpanel" aria-labelledby="customer-register-tab" tabindex="0">
                            <div class="mb-3">
                                <div class="fw-semibold mb-1">Register Customer</div>
                                <div class="text-body-secondary small">Buat akun baru untuk melihat profile dan riwayat order.</div>
                            </div>
                            <form method="post" class="row g-3">
                                <?php wp_nonce_field($nonce_action, $nonce_name); ?>
                                <input type="hidden" name="custom_plugin_frontend_action" value="register_customer_account">

                                <div class="col-12">
                                    <label for="register-full-name" class="form-label">Nama Lengkap</label>
                                    <input type="text" id="register-full-name" name="register_full_name" class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <label for="register-email" class="form-label">Email</label>
                                    <input type="email" id="register-email" name="register_email" class="form-control" required>
                                </div>

                                <div class="col-12">
                                    <label for="register-city" class="form-label">Kota</label>
                                    <select id="register-city" name="register_city" class="form-select" required>
                                        <option value="">Pilih kota</option>
                                        <?php foreach ($city_options as $city) : ?>
                                            <option value="<?php echo esc_attr($city['value']); ?>">
                                                <?php echo esc_html($city['label']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="register-password" class="form-label">Password</label>
                                    <input type="password" id="register-password" name="register_password" class="form-control" minlength="6" required>
                                </div>

                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-lg custom-plugin-auth-btn">Register</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>