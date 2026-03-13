<?php
/**
 * Shortcode Template: Data Table
 *
 * @var array $results
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="custom-plugin-data">
    <h3><?php esc_html_e('Kiriman Terbaru', 'custom-plugin'); ?></h3>
    <table class="custom-plugin-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Nama', 'custom-plugin'); ?></th>
                <th><?php esc_html_e('Email', 'custom-plugin'); ?></th>
                <th><?php esc_html_e('Pesan', 'custom-plugin'); ?></th>
                <th><?php esc_html_e('Tanggal', 'custom-plugin'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($results)) : ?>
                <tr>
                    <td colspan="4"><?php esc_html_e('Tidak ada data ditemukan.', 'custom-plugin'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($results as $row): ?>
                    <tr>
                        <td><?php echo esc_html($row->name); ?></td>
                        <td><?php echo esc_html($row->email); ?></td>
                        <td><?php echo esc_html(wp_trim_words($row->message, 10)); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($row->created_at))); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
