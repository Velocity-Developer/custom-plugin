<?php

namespace CustomPlugin\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class PostAutomation
{
    public function __construct()
    {
        // Set automatic title for 'penilaian' post type
        add_filter('wp_insert_post_data', array($this, 'set_automatic_penilaian_title'), 10, 2);
    }

    /**
     * Set automatic title for 'penilaian' post type
     * Format: #dmy-random3digit (e.g. #080426-123)
     */
    public function set_automatic_penilaian_title($data, $postarr)
    {
        // Only target 'penilaian' post type
        if ($data['post_type'] !== 'penilaian') {
            return $data;
        }

        // Always set the title if it's new or empty
        // We check if it's a new post (no ID in $postarr or ID is 0) or if title is one of WP's defaults
        $is_auto_draft = $data['post_status'] === 'auto-draft' || $data['post_title'] === 'Auto Draft' || empty($data['post_title']);

        if ($is_auto_draft || $postarr['ID'] == 0) {
            $date_part = date('dmy');
            $random_part = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

            $data['post_title'] = '#' . $date_part . '-' . $random_part;

            // Also update the slug (post_name) based on the new title
            $data['post_name'] = sanitize_title($data['post_title']);
        }

        return $data;
    }
}
