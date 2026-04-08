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
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['siswa'] = 'Siswa';
                $new_columns['nilai'] = 'Nilai';
            }
        }
        return $new_columns;
    }

    public function render_penilaian_columns($column, $post_id)
    {
        switch ($column) {
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
        $columns['nilai'] = 'nilai';
        return $columns;
    }
}
