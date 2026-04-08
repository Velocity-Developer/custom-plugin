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
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h3 class="mb-0">Data Nilai: <?php echo esc_html($current_user->display_name); ?> (<?php echo esc_html(get_user_meta($current_user->ID, '_nis', true) ?: $current_user->user_login); ?>)</h3>
        <a href="<?php echo esc_url(wp_logout_url(get_permalink())); ?>" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>

    <!-- Filter Form -->
    <div class="nilai-filter-form mb-4 p-3 bg-light border rounded">
        <form method="GET" action="" class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="f_month" class="form-label small">Bulan</label>
                <select name="f_month" id="f_month" class="form-select form-select-sm w-100" style="min-width: 220px;">
                    <option value="">Semua Bulan</option>
                    <?php
                    $months = array(
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember'
                    );
                    foreach ($months as $val => $label) : ?>
                        <option value="<?php echo $val; ?>" <?php selected($f_month, $val); ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label for="f_year" class="form-label small">Tahun</label>
                <select name="f_year" id="f_year" class="form-select form-select-sm w-100" style="min-width: 220px;">
                    <option value="">Semua Tahun</option>
                    <?php
                    $current_year = (int)date('Y');
                    for ($y = $current_year + 1; $y >= $current_year - 5; $y--) : ?>
                        <option value="<?php echo $y; ?>" <?php selected($f_year, $y); ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <?php if (!empty($f_month) || !empty($f_year)) : ?>
                    <a href="<?php echo strtok($_SERVER["REQUEST_URI"], '?'); ?>" class="btn btn-secondary btn-sm">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <?php if (empty($penilaian)) : ?>
        <p class="alert alert-info">Belum ada data nilai yang tersedia untuk periode ini.</p>
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

    /* Additional Buttons & Layout */
    .d-flex {
        display: flex;
    }

    .justify-content-between {
        justify-content: space-between;
    }

    .align-items-center {
        align-items: center;
    }

    .flex-wrap {
        flex-wrap: wrap;
    }

    .mb-0 {
        margin-bottom: 0 !important;
    }

    .mb-3 {
        margin-bottom: 1rem !important;
    }

    .mb-4 {
        margin-bottom: 1.5rem !important;
    }

    .text-center {
        text-align: center;
    }

    .p-4 {
        padding: 1.5rem !important;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .border {
        border: 1px solid #dee2e6 !important;
    }

    .rounded {
        border-radius: 0.25rem !important;
    }

    .btn {
        display: inline-block;
        font-weight: 400;
        line-height: 1.5;
        color: #212529;
        text-align: center;
        text-decoration: none;
        vertical-align: middle;
        cursor: pointer;
        user-select: none;
        background-color: transparent;
        border: 1px solid transparent;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        border-radius: 0.25rem;
        transition: color .15s ease-in-out, background-color .15s ease-in-out, border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }

    .btn-primary {
        color: #fff;
        background-color: #0073aa;
        border-color: #0073aa;
    }

    .btn-outline-danger {
        color: #dc3545;
        border-color: #dc3545;
    }

    .btn-outline-danger:hover {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: .875rem;
        border-radius: 0.2rem;
    }

    /* Filter Form Styles */
    .nilai-filter-form {
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f9f9f9;
        border: 1px solid #eee;
        border-radius: 4px;
    }

    .nilai-filter-form form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: flex-end;
    }

    .nilai-filter-form .col-auto {
        display: flex;
        flex-direction: column;
    }

    .nilai-filter-form label {
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 5px;
        color: #666;
    }

    .nilai-filter-form select,
    .nilai-filter-form button,
    .nilai-filter-form a {
        padding: 6px 12px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .nilai-filter-form button {
        background-color: #0073aa;
        color: white;
        border-color: #0073aa;
        cursor: pointer;
    }

    .nilai-filter-form button:hover {
        background-color: #006799;
    }

    .nilai-filter-form .btn-secondary {
        background-color: #eee;
        color: #333;
        text-decoration: none;
        text-align: center;
    }

    .alert-info {
        padding: 15px;
        background-color: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
        border-radius: 4px;
    }
</style>