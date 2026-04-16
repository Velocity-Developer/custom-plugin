<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template for Archive Document & History List
 * 
 * Available variables:
 * @var WP_Query $query
 * @var int $paged
 * @var string $post_type
 */
?>

<div class="custom-plugin-archive-container">
    <?php if (current_user_can('edit_posts')): ?>
        <div style="margin-bottom: 20px; text-align: right;">
            <?php
            $label = $post_type === 'history' ? 'History' : 'Dokumen';
            $tambah_url = $post_type === 'history' ? home_url('/data-history/') : home_url('/data');
            $current_queried_object = get_queried_object();

            // Get taxonomies slug based on post type
            $cat_taxonomy = ($post_type === 'dokumen') ? 'document_category' : 'history_category';
            $zone_taxonomy = ($post_type === 'dokumen') ? 'zone' : 'zone_history';

            if ($current_queried_object instanceof WP_Term) {
                $param_name = ($current_queried_object->taxonomy === $zone_taxonomy) ? 'zone_id' : 'cat_id';
                $tambah_url = add_query_arg($param_name, $current_queried_object->term_id, $tambah_url);
            }
            ?>
            <a href="<?php echo esc_url($tambah_url); ?>" class="btn-tambah" style="display: inline-block; padding: 10px 20px; background: #28a745; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                + Tambah <?php echo $label; ?>
            </a>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead style="background: #f8f9fa;">
                <tr>
                    <th style="padding: 12px; border: 1px solid #dee2e6; text-align: center; width: 80px;">Gambar</th>
                    <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Judul</th>
                    <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Kategori</th>
                    <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left;">Zone</th>
                    <th style="padding: 12px; border: 1px solid #dee2e6; text-align: left; width: 120px;">Tanggal</th>
                    <th style="padding: 12px; border: 1px solid #dee2e6; text-align: center; width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($query->have_posts()): while ($query->have_posts()): $query->the_post(); ?>
                        <tr>
                            <td style="padding: 12px; border: 1px solid #dee2e6; text-align: center;">
                                <a href="<?php the_permalink(); ?>">
                                    <?php if (has_post_thumbnail()): ?>
                                        <?php the_post_thumbnail(array(50, 50), array('style' => 'max-width: 50px; height: auto; border-radius: 4px; border: 1px solid #ddd;')); ?>
                                    <?php else: ?>
                                        <span style="font-size: 10px; color: #999;">No Image</span>
                                    <?php endif; ?>
                                </a>
                            </td>
                            <td style="padding: 12px; border: 1px solid #dee2e6;">
                                <a href="<?php the_permalink(); ?>" style="text-decoration: none; color: #333; font-weight: 600;">
                                    <?php the_title(); ?>
                                </a>
                            </td>
                            <td style="padding: 12px; border: 1px solid #dee2e6;">
                                <?php
                                $cat_taxonomy = ($post_type === 'dokumen') ? 'document_category' : 'history_category';
                                echo get_the_term_list(get_the_ID(), $cat_taxonomy, '', ', ');
                                ?>
                            </td>
                            <td style="padding: 12px; border: 1px solid #dee2e6;">
                                <?php
                                $zone_taxonomy = ($post_type === 'dokumen') ? 'zone' : 'zone_history';
                                echo get_the_term_list(get_the_ID(), $zone_taxonomy, '', ', ');
                                ?>
                            </td>
                            <td style="padding: 12px; border: 1px solid #dee2e6;">
                                <?php echo get_the_date('d M Y'); ?>
                            </td>
                            <td style="padding: 12px; border: 1px solid #dee2e6; text-align: center;">
                                <a href="<?php the_permalink(); ?>" class="btn-detail" style="display: inline-block; padding: 6px 12px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; margin-bottom: 5px; width: 100%;">
                                    Lihat Detail
                                </a>
                                <?php if (current_user_can('edit_posts')): ?>
                                    <?php $label = $post_type === 'history' ? 'History' : 'Dokumen'; ?>
                                    <?php $edit_url = $post_type === 'history' ? home_url('/data-history/') : home_url('/data'); ?>
                                    <a href="<?php echo esc_url(add_query_arg(array('action' => 'edit', 'post_id' => get_the_ID()), $edit_url)); ?>" class="btn-edit" style="display: inline-block; padding: 6px 12px; background: #ffc107; color: #000; text-decoration: none; border-radius: 4px; font-size: 12px; width: 100%;">
                                        Edit <?php echo $label; ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center; color: #666; border: 1px solid #dee2e6;">Belum ada <?php echo $post_type === 'history' ? 'history' : 'dokumen'; ?> yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
    // Pagination
    $total_pages = $query->max_num_pages;
    if ($total_pages > 1) {
        $current_page = max(1, $paged);
        echo '<div class="pagination" style="display: flex; gap: 5px; margin-top: 20px; justify-content: center;">';
        echo paginate_links(array(
            'base'      => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format'    => '?paged=%#%',
            'current'   => $current_page,
            'total'     => $total_pages,
            'prev_text' => '&laquo; Prev',
            'next_text' => 'Next &raquo;',
            'type'      => 'plain',
        ));
        echo '</div>';
    }
    wp_reset_postdata();
    ?>
</div>

<style>
    .custom-plugin-archive-container .pagination .page-numbers {
        display: inline-block;
        padding: 8px 12px;
        background: #fff;
        border: 1px solid #ddd;
        text-decoration: none;
        color: #0073aa;
        border-radius: 4px;
    }

    .custom-plugin-archive-container .pagination .page-numbers.current {
        background: #0073aa;
        color: #fff;
        border-color: #0073aa;
    }

    .custom-plugin-archive-container .pagination .page-numbers:hover:not(.current) {
        background: #f0f0f0;
    }
</style>