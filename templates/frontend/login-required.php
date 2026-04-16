<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="login-required-container" style="max-width: 600px; margin: 50px auto; text-align: center; padding: 40px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="color: #333; margin-bottom: 20px;">Anda harus login terlebih dahulu</h2>
    <p style="color: #666; margin-bottom: 30px;">Halaman ini hanya dapat diakses oleh pengguna yang sudah login.</p>
    <a href="<?php echo esc_url(home_url('/')); ?>" style="display: inline-block; padding: 12px 30px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 16px;">
        Ke Halaman Utama
    </a>
</div>