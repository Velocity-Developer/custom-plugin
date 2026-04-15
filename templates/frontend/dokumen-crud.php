<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template for Document CRUD Frontend
 * 
 * Available variables:
 * @var WP_Query $query
 * @var array $categories
 * @var array $zones
 * @var WP_Post|null $edit_post
 * @var string $message
 */
?>

<div class="custom-plugin-crud-container">
    <?php if ($message): ?>
        <div class="notice notice-success">
            <p>
                <?php
                switch ($message) {
                    case 'created':
                        echo 'Dokumen berhasil dibuat.';
                        break;
                    case 'updated':
                        echo 'Dokumen berhasil diperbarui.';
                        break;
                    case 'deleted':
                        echo 'Dokumen berhasil dihapus.';
                        break;
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="crud-form-section" style="margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h3><?php echo $edit_post ? 'Edit Data' : 'Tambah Data'; ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <?php wp_nonce_field('dokumen_action_nonce', 'dokumen_nonce'); ?>
            <input type="hidden" name="dokumen_action" value="<?php echo $edit_post ? 'update' : 'create'; ?>">
            <?php if ($edit_post): ?>
                <input type="hidden" name="post_id" value="<?php echo $edit_post->ID; ?>">
            <?php endif; ?>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Judul Dokumen</label>
                <input type="text" name="post_title" required value="<?php echo $edit_post ? esc_attr($edit_post->post_title) : ''; ?>" style="width: 100%; padding: 8px;">
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Gambar Dokumen</label>
                <input type="file" name="document_image" id="document_image_input" accept="image/*" style="width: 100%; padding: 8px;">
                <div id="image_preview_container" style="margin-top: 10px;">
                    <?php if ($edit_post && has_post_thumbnail($edit_post->ID)): ?>
                        <?php echo get_the_post_thumbnail($edit_post->ID, 'thumbnail', array('id' => 'image_preview_img', 'style' => 'max-width: 150px; height: auto; border: 1px solid #ddd; padding: 5px;')); ?>
                    <?php else: ?>
                        <img id="image_preview_img" src="#" alt="Preview" style="display: none; max-width: 150px; height: auto; border: 1px solid #ddd; padding: 5px;" />
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Konten/Deskripsi</label>
                <?php
                $content = $edit_post ? $edit_post->post_content : '';
                $editor_id = 'post_content_editor';
                $settings = array(
                    'textarea_name' => 'post_content',
                    'media_buttons' => false,
                    'textarea_rows' => 10,
                    'teeny'         => true,
                    'quicktags'     => true
                );
                wp_editor($content, $editor_id, $settings);
                ?>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Kategori Dokumen</label>
                <select name="document_category" style="width: 100%; padding: 8px;">
                    <option value="">Pilih Kategori</option>
                    <?php
                    $current_cat = $edit_post ? wp_get_object_terms($edit_post->ID, 'document_category', array('fields' => 'ids')) : array();
                    foreach ($categories as $cat):
                    ?>
                        <option value="<?php echo $cat->term_id; ?>" <?php echo in_array($cat->term_id, $current_cat) ? 'selected' : ''; ?>>
                            <?php echo esc_html($cat->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">Zone</label>
                <select name="zone" style="width: 100%; padding: 8px;">
                    <option value="">Pilih Zone</option>
                    <?php
                    $current_zone = $edit_post ? wp_get_object_terms($edit_post->ID, 'zone', array('fields' => 'ids')) : array();
                    foreach ($zones as $zone):
                    ?>
                        <option value="<?php echo $zone->term_id; ?>" <?php echo in_array($zone->term_id, $current_zone) ? 'selected' : ''; ?>>
                            <?php echo esc_html($zone->name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="button" style="padding: 10px 20px; background: #0073aa; color: white; border: none; cursor: pointer;">
                <?php echo $edit_post ? 'Perbarui Dokumen' : 'Simpan Dokumen'; ?>
            </button>
            <?php if ($edit_post): ?>
                <a href="<?php echo remove_query_arg(array('action', 'post_id')); ?>" style="margin-left: 10px;">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="crud-list-section">
        <h3>Daftar Dokumen</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f4f4f4;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left; width: 80px;">Gambar</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Judul</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Kategori</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Zone</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query->have_posts()): while ($query->have_posts()): $query->the_post(); ?>
                        <tr>
                            <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail(array(50, 50), array('style' => 'max-width: 50px; height: auto;')); ?>
                                <?php else: ?>
                                    <span style="font-size: 10px; color: #999;">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 10px; border: 1px solid #ddd;"><?php the_title(); ?></td>
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <?php echo get_the_term_list(get_the_ID(), 'document_category', '', ', '); ?>
                            </td>
                            <td style="padding: 10px; border: 1px solid #ddd;">
                                <?php echo get_the_term_list(get_the_ID(), 'zone', '', ', '); ?>
                            </td>
                            <td style="padding: 10px; border: 1px solid #ddd; text-align: center;">
                                <a href="<?php echo add_query_arg(array('action' => 'edit', 'post_id' => get_the_ID())); ?>">Edit</a> |
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus dokumen ini?');">
                                    <?php wp_nonce_field('dokumen_action_nonce', 'dokumen_nonce'); ?>
                                    <input type="hidden" name="dokumen_action" value="delete">
                                    <input type="hidden" name="post_id" value="<?php the_ID(); ?>">
                                    <button type="submit" style="background: none; border: none; color: #a00; cursor: pointer; padding: 0; font-family: inherit; font-size: inherit;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile;
                    wp_reset_postdata();
                else: ?>
                    <tr>
                        <td colspan="4" style="padding: 10px; border: 1px solid #ddd; text-align: center;">Belum ada dokumen.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('document_image_input');
        const preview = document.getElementById('image_preview_img');

        if (input && preview) {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    });
</script>