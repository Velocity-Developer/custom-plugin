<?php
/**
 * Admin Template: Dashboard
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="card" style="max-width: 600px; margin-top: 20px;">
        <h2><?php esc_html_e('Selamat Datang di Custom Plugin Starter Kit!', 'custom-plugin'); ?></h2>
        <p><?php esc_html_e('Ini adalah halaman dashboard contoh. Anda dapat menggunakan Template Engine untuk merender konten admin dengan rapi.', 'custom-plugin'); ?></p>
        <p>
            <code>Template::render('admin/dashboard');</code>
        </p>
    </div>
</div>
