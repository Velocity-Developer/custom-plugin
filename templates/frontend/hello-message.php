<?php
/**
 * Frontend Template: Hello Message
 * 
 * @var string $name
 * @var string $color
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="custom-hello-box" style="padding: 15px; background: #f9f9f9; border-left: 5px solid <?php echo esc_attr($color); ?>; margin-bottom: 20px;">
    <h3 style="margin-top: 0;"><?php printf(esc_html__('Halo, %s!', 'custom-plugin'), esc_html($name)); ?></h3>
    <p><?php esc_html_e('Ini adalah contoh penggunaan sistem template kustom dalam plugin WordPress Anda.', 'custom-plugin'); ?></p>
</div>
