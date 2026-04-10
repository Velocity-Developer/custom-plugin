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
        $tanggal = get_post_meta($post->ID, '_tanggal_penilaian', true);
        $deskripsi = get_post_meta($post->ID, '_deskripsi_kegiatan', true);
        $catatan = get_post_meta($post->ID, '_catatan_guru', true);

        // Default date if empty
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        // Get all students (Users with role 'siswa' or 'subscriber')
        $students = get_users(array(
            'role__in' => array('siswa', 'subscriber'),
            'orderby'  => 'display_name',
            'order'    => 'ASC',
        ));

?>
        <div class="penilaian-metabox-content">
            <p>
                <label for="tanggal_penilaian"><strong>Tanggal Penilaian:</strong></label><br>
                <input type="date" name="tanggal_penilaian" id="tanggal_penilaian" value="<?php echo esc_attr($tanggal); ?>" class="widefat" required>
            </p>
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
            <p>
                <label for="deskripsi_kegiatan"><strong>Deskripsi Kegiatan:</strong></label><br>
                <input type="text" name="deskripsi_kegiatan" id="deskripsi_kegiatan" value="<?php echo esc_attr($deskripsi); ?>" class="widefat">
            </p>
            <p>
                <label for="catatan_guru"><strong>Catatan Guru:</strong></label><br>
                <textarea name="catatan_guru" id="catatan_guru" rows="4" class="widefat"><?php echo esc_textarea($catatan); ?></textarea>
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

        // Save Deskripsi Kegiatan
        if (isset($_POST['deskripsi_kegiatan'])) {
            update_post_meta($post_id, '_deskripsi_kegiatan', sanitize_text_field($_POST['deskripsi_kegiatan']));
        }

        // Save Tanggal
        if (isset($_POST['tanggal_penilaian'])) {
            update_post_meta($post_id, '_tanggal_penilaian', sanitize_text_field($_POST['tanggal_penilaian']));
        }

        // Save Catatan
        if (isset($_POST['catatan_guru'])) {
            update_post_meta($post_id, '_catatan_guru', sanitize_textarea_field($_POST['catatan_guru']));
        }
    }
}
