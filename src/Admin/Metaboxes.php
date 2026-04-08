<?php

namespace CustomPlugin\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class Metaboxes
{
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_penilaian_metabox'));
        add_action('save_post', array($this, 'save_penilaian_metabox'));
    }

    public function add_penilaian_metabox()
    {
        add_meta_box(
            'penilaian_details',
            'Detail Penilaian',
            array($this, 'render_penilaian_metabox'),
            'penilaian',
            'normal',
            'high'
        );
    }

    public function render_penilaian_metabox($post)
    {
        // Add nonce for security
        wp_nonce_field('save_penilaian_details', 'penilaian_details_nonce');

        // Get current values
        $siswa_id = get_post_meta($post->ID, '_siswa_id', true);
        $nilai = get_post_meta($post->ID, '_nilai_siswa', true);

        // Get all students (Users with role 'siswa' or 'subscriber')
        $students = get_users(array(
            'role__in' => array('siswa', 'subscriber'),
            'orderby'  => 'display_name',
            'order'    => 'ASC',
        ));

?>
        <div class="penilaian-metabox-content">
            <p>
                <label for="siswa_id"><strong>Pilih Siswa (User):</strong></label><br>
                <select name="siswa_id" id="siswa_id" class="widefat" required>
                    <option value="">-- Pilih Siswa --</option>
                    <?php foreach ($students as $student) :
                        $nis = get_user_meta($student->ID, '_nis', true);
                        $display_name = $student->display_name;
                        $identifier = $nis ? $nis : $student->user_login;
                    ?>
                        <option value="<?php echo esc_attr($student->ID); ?>" <?php selected($siswa_id, $student->ID); ?>>
                            <?php echo esc_html($display_name); ?> (<?php echo esc_html($identifier); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="nilai_siswa"><strong>Nilai:</strong></label><br>
                <input type="number" name="nilai_siswa" id="nilai_siswa" value="<?php echo esc_attr($nilai); ?>" class="widefat" step="0.01" min="0" max="100" required>
            </p>
        </div>
<?php
    }

    public function save_penilaian_metabox($post_id)
    {
        // Check nonce
        if (!isset($_POST['penilaian_details_nonce']) || !wp_verify_nonce($_POST['penilaian_details_nonce'], 'save_penilaian_details')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save Siswa ID
        if (isset($_POST['siswa_id'])) {
            update_post_meta($post_id, '_siswa_id', sanitize_text_field($_POST['siswa_id']));
        }

        // Save Nilai
        if (isset($_POST['nilai_siswa'])) {
            update_post_meta($post_id, '_nilai_siswa', sanitize_text_field($_POST['nilai_siswa']));
        }
    }
}
