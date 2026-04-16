<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template for Custom Login Form with Captcha
 * 
 * Available variables:
 * @var string $captcha_text
 * @var string $error
 */
?>

<div class="custom-login-form-container" style="max-width: 400px; margin: 20px auto; padding: 30px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <h3 style="text-align: center; margin-bottom: 25px; color: #333;">Login ke Portal</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger" style="background: #fee; border: 1px solid #fcc; color: #a00; padding: 10px; border-radius: 4px; margin-bottom: 20px; font-size: 14px;">
            <?php
            if ($error === 'captcha') echo 'Captcha salah, silakan coba lagi.';
            else if ($error === 'failed') echo 'Username atau password salah.';
            else echo 'Terjadi kesalahan, silakan coba lagi.';
            ?>
        </div>
    <?php endif; ?>

    <form name="loginform" id="loginform" action="<?php echo esc_url(get_permalink()); ?>" method="post">
        <?php wp_nonce_field('custom_login_nonce', 'login_nonce'); ?>
        <input type="hidden" name="dokumen_action" value="login">

        <div class="form-group" style="margin-bottom: 15px;">
            <label for="user_login" style="display: block; margin-bottom: 5px; font-weight: 600;">Username atau Email</label>
            <input type="text" name="log" id="user_login" class="input" value="" size="20" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div class="form-group" style="margin-bottom: 15px;">
            <label for="user_pass" style="display: block; margin-bottom: 5px; font-weight: 600;">Password</label>
            <input type="password" name="pwd" id="user_pass" class="input" value="" size="20" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Verifikasi Keamanan (Captcha)</label>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div id="captcha_image" style="background: #000; color: #fff; padding: 10px 15px; border-radius: 4px; font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 20px; letter-spacing: 5px; user-select: none; pointer-events: none; background-image: radial-gradient(circle at 50% 50%, #333 1px, transparent 1px); background-size: 5px 5px;">
                    <?php echo $captcha_text; ?>
                </div>
                <input type="text" name="captcha" id="captcha" class="input" required placeholder="Ketik kode di samping" style="flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <small style="color: #777;">*Captcha bersifat case-sensitive.</small>
        </div>

        <div class="form-group" style="margin-bottom: 20px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input name="rememberme" type="checkbox" id="rememberme" value="forever"> Ingat Saya
            </label>
        </div>

        <div class="form-group">
            <button type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" style="width: 100%; padding: 12px; background: #0073aa; color: #fff; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s;">Login</button>
        </div>
    </form>

    <p style="text-align: center; margin-top: 15px; font-size: 14px;">
        <a href="<?php echo wp_lostpassword_url(); ?>" style="text-decoration: none; color: #0073aa;">Lupa password?</a>
    </p>
</div>

<style>
    #wp-submit:hover {
        background: #005177 !important;
    }
</style>