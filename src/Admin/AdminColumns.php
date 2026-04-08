<?php

namespace CustomPlugin\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class AdminColumns
{
    public function __construct()
    {
        // Add columns to 'penilaian' CPT
        add_filter('manage_penilaian_posts_columns', array($this, 'set_penilaian_columns'));
        add_action('manage_penilaian_posts_custom_column', array($this, 'render_penilaian_columns'), 10, 2);
        add_filter('manage_edit-penilaian_sortable_columns', array($this, 'set_penilaian_sortable_columns'));
    }

    public function set_penilaian_columns($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            if ($key === 'title') {
                $new_columns['id_penilaian'] = 'ID Penilaian';
                $new_columns['tanggal'] = 'Tanggal';
                $new_columns['siswa'] = 'Siswa';
                $new_columns['nilai'] = 'Nilai';
                continue; // Skip the original title column
            }
            $new_columns[$key] = $value;
        }
        return $new_columns;
    }

    public function render_penilaian_columns($column, $post_id)
    {
        switch ($column) {
            case 'id_penilaian':
                $edit_link = get_edit_post_link($post_id);
                echo '<strong><a href="' . esc_url($edit_link) . '">' . esc_html(get_the_title($post_id)) . '</a></strong>';
                break;
            case 'tanggal':
                $tanggal = get_post_meta($post_id, '_tanggal_penilaian', true);
                if ($tanggal) {
                    echo esc_html(date_i18n('d/m/Y', strtotime($tanggal)));
                } else {
                    echo '<span style="color: #ccc;">-</span>';
                }
                break;
            case 'siswa':
                $siswa_id = get_post_meta($post_id, '_siswa_id', true);
                if ($siswa_id) {
                    $user = get_userdata($siswa_id);
                    if ($user) {
                        $nis = get_user_meta($user->ID, '_nis', true);
                        $identifier = $nis ? $nis : $user->user_login;
                        echo esc_html($user->display_name) . ' (' . esc_html($identifier) . ')';
                    } else {
                        echo '<span style="color: #d63638;">User tidak ditemukan</span>';
                    }
                } else {
                    echo '<span style="color: #ccc;">(Belum diatur)</span>';
                }
                break;

            case 'nilai':
                $nilai = get_post_meta($post_id, '_nilai_siswa', true);
                if ($nilai !== '') {
                    echo '<strong>' . esc_html($nilai) . '</strong>';
                } else {
                    echo '<span style="color: #ccc;">-</span>';
                }
                break;
        }
    }

    public function set_penilaian_sortable_columns($columns)
    {
        $columns['id_penilaian'] = 'title';
        $columns['tanggal'] = 'tanggal';
        $columns['nilai'] = 'nilai';
        return $columns;
    }
}
