<?php

/**
 * Template: Daftar Nilai Siswa
 * 
 * @var array $penilaian
 * @var WP_User $current_user
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="custom-plugin-nilai-container">
    <h3>Data Nilai: <?php echo esc_html($current_user->display_name); ?> (<?php echo esc_html(get_user_meta($current_user->ID, '_nis', true) ?: $current_user->user_login); ?>)</h3>

    <?php if (empty($penilaian)) : ?>
        <p>Belum ada data nilai yang tersedia.</p>
    <?php else : ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Mata Pelajaran</th>
                        <th>Jenis</th>
                        <th>Nilai</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($penilaian as $item) :
                        $post_id = $item->ID;
                        $tanggal = get_post_meta($post_id, '_tanggal_penilaian', true);
                        $nilai = get_post_meta($post_id, '_nilai_siswa', true);
                        $catatan = get_post_meta($post_id, '_catatan_guru', true);

                        $mapel = get_the_terms($post_id, 'mata_pelajaran');
                        $jenis = get_the_terms($post_id, 'jenis_penilaian');
                    ?>
                        <tr>
                            <td><?php echo $tanggal ? esc_html(date_i18n('d F Y', strtotime($tanggal))) : '-'; ?></td>
                            <td><?php echo $mapel ? esc_html($mapel[0]->name) : '-'; ?></td>
                            <td><?php echo $jenis ? esc_html($jenis[0]->name) : '-'; ?></td>
                            <td><strong><?php echo esc_html($nilai); ?></strong></td>
                            <td><?php echo $catatan ? nl2br(esc_html($catatan)) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<style>
    .custom-plugin-nilai-container {
        margin: 20px 0;
    }

    .custom-plugin-nilai-container table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    .custom-plugin-nilai-container th,
    .custom-plugin-nilai-container td {
        padding: 12px;
        border: 1px solid #ddd;
        text-align: left;
    }

    .custom-plugin-nilai-container th {
        background-color: #f8f9fa;
    }

    .custom-plugin-nilai-container tr:nth-child(even) {
        background-color: #f2f2f2;
    }
</style>