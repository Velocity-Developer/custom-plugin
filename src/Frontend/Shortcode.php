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
            return '<p>Silakan login terlebih dahulu untuk melihat nilai.</p>';
        }

        $current_user = wp_get_current_user();

        // 2. Fetch penilaian data for the current user
        $penilaian = get_posts(array(
            'post_type'      => 'penilaian',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => '_siswa_id',
                    'value' => $current_user->ID,
                ),
            ),
            'orderby'        => 'meta_value',
            'meta_key'       => '_tanggal_penilaian',
            'order'          => 'DESC',
        ));

        // 3. Render using Template Engine
        return Template::get('frontend/daftar-nilai', array(
            'penilaian'    => $penilaian,
            'current_user' => $current_user,
        ));
    }
}
