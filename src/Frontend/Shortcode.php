<?php

namespace CustomPlugin\Frontend;

use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Shortcode
 * 
 * Best practice for Shortcode implementation.
 */
class Shortcode
{

    public function __construct()
    {
        add_shortcode('nilai', array($this, 'nilai_shortcode'));
    }

    /**
     * Shortcode: [nilai]
     * 
     * @param array $atts
     * @return string
     */
    public function nilai_shortcode($atts)
    {
        // 1. Check if user is logged in
        if (!is_user_logged_in()) {
            $login_url = wp_login_url(get_permalink());
            return '
                <div class="custom-plugin-login-notice border p-4 text-center rounded bg-light">
                    <p class="mb-3">Silakan login terlebih dahulu untuk melihat nilai.</p>
                    <a href="' . esc_url($login_url) . '" class="btn btn-primary">Login ke Akun Siswa</a>
                </div>
            ';
        }

        $current_user = wp_get_current_user();

        // 2. Handle Filters
        $filter_month = isset($_GET['f_month']) ? sanitize_text_field($_GET['f_month']) : '';
        $filter_year = isset($_GET['f_year']) ? sanitize_text_field($_GET['f_year']) : '';

        $meta_query = array(
            'relation' => 'AND',
            array(
                'key'   => '_siswa_id',
                'value' => $current_user->ID,
            ),
        );

        if (!empty($filter_month) && !empty($filter_year)) {
            $date_prefix = $filter_year . '-' . $filter_month;
            $meta_query[] = array(
                'key'     => '_tanggal_penilaian',
                'value'   => '^' . $date_prefix,
                'compare' => 'REGEXP',
            );
        } elseif (!empty($filter_year)) {
            $meta_query[] = array(
                'key'     => '_tanggal_penilaian',
                'value'   => '^' . $filter_year,
                'compare' => 'REGEXP',
            );
        }

        // 3. Fetch penilaian data for the current user
        $penilaian = get_posts(array(
            'post_type'      => 'penilaian',
            'posts_per_page' => -1,
            'meta_query'     => $meta_query,
            'orderby'        => 'meta_value',
            'meta_key'       => '_tanggal_penilaian',
            'order'          => 'DESC',
        ));

        // 4. Render using Template Engine
        return Template::get('frontend/daftar-nilai', array(
            'penilaian'    => $penilaian,
            'current_user' => $current_user,
            'f_month'      => $filter_month,
            'f_year'       => $filter_year,
        ));
    }
}
