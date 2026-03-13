<?php

namespace CustomPlugin\Frontend;

if (!defined('ABSPATH')) {
    exit;
}

class Shortcode
{

    public function __construct()
    {
        // add_shortcode('custom_form', array($this, 'custom_form_shortcode'));
        add_shortcode('custom_message', array($this, 'custom_message_shortcode'));
        add_shortcode('custom_data', array($this, 'custom_data_shortcode'));
    }

    public function custom_form_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'button_text' => 'Kirim'
        ), $atts, 'custom_form');

        return Frontend::display_form();
    }

    public function custom_message_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'text' => 'Halo Dunia!',
            'style' => 'default'
        ), $atts, 'custom_message');

        $class = 'custom-plugin-message-' . sanitize_html_class($atts['style']);
        return '<div class="' . esc_attr($class) . '">' . esc_html($atts['text']) . '</div>';
    }

    public function custom_data_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ), $atts, 'custom_data');

        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_plugin_data';

        $limit = intval($atts['limit']);
        $orderby = sanitize_sql_orderby($atts['orderby']);
        $order = in_array(strtoupper($atts['order']), array('ASC', 'DESC')) ? strtoupper($atts['order']) : 'DESC';

        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY {$orderby} {$order} LIMIT %d",
            $limit
        );

        $results = $wpdb->get_results($query);

        if (empty($results)) {
            return '<p>Tidak ada data ditemukan.</p>';
        }

        ob_start();
?>
        <div class="custom-plugin-data">
            <h3>Kiriman Terbaru</h3>
            <table class="custom-plugin-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Pesan</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row->name); ?></td>
                            <td><?php echo esc_html($row->email); ?></td>
                            <td><?php echo esc_html(wp_trim_words($row->message, 10)); ?></td>
                            <td><?php echo esc_html(date('Y-m-d H:i', strtotime($row->created_at))); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
<?php
        return ob_get_clean();
    }
}
