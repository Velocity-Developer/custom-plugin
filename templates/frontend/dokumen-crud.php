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
        <h3><?php echo $edit_post ? 'Edit Dokumen' : 'Tambah Dokumen Baru'; ?></h3>
        <form method="POST">
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
                <label style="display: block; margin-bottom: 5px;">Konten/Deskripsi</label>
                <textarea name="post_content" style="width: 100%; padding: 8px;" rows="5"><?php echo $edit_post ? esc_textarea($edit_post->post_content) : ''; ?></textarea>
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
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Judul</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Kategori</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Zone</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query->have_posts()): while ($query->have_posts()): $query->the_post(); ?>
                        <tr>
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