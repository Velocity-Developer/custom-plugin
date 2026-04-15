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
        // Example: To activate, uncomment the line below.
        // add_shortcode('custom_hello', array($this, 'hello_shortcode'));
        add_shortcode('dokumen_crud', array($this, 'dokumen_crud_shortcode'));

        // Handle form submissions
        add_action('init', array($this, 'handle_post_requests'));
    }

    /**
     * Handle CRUD POST requests from the frontend
     */
    public function handle_post_requests()
    {
        if (!isset($_POST['dokumen_action']) || !wp_verify_nonce($_POST['dokumen_nonce'], 'dokumen_action_nonce')) {
            return;
        }

        if (!current_user_can('edit_posts')) {
            wp_die(__('Maaf, Anda tidak memiliki izin untuk melakukan aksi ini.', 'custom-plugin'));
        }

        $action = sanitize_text_field($_POST['dokumen_action']);

        switch ($action) {
            case 'create':
            case 'update':
                $this->save_document($action);
                break;
            case 'delete':
                $this->delete_document();
                break;
        }
    }

    /**
     * Save (Create or Update) a document
     */
    private function save_document($action)
    {
        $post_id = ($action === 'update') ? intval($_POST['post_id']) : 0;

        $post_data = array(
            'post_title'   => sanitize_text_field($_POST['post_title']),
            'post_content' => wp_kses_post($_POST['post_content']),
            'post_status'  => 'publish',
            'post_type'    => 'dokumen',
        );

        if ($action === 'update') {
            $post_data['ID'] = $post_id;
            $result = wp_update_post($post_data);
        } else {
            $result = wp_insert_post($post_data);
            $post_id = $result;
        }

        if ($result && !is_wp_error($result)) {
            // Update taxonomies
            if (isset($_POST['document_category'])) {
                wp_set_object_terms($post_id, intval($_POST['document_category']), 'document_category');
            }
            if (isset($_POST['zone'])) {
                wp_set_object_terms($post_id, intval($_POST['zone']), 'zone');
            }

            // Handle Image Upload
            if (!empty($_FILES['document_image']['name'])) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');

                $attachment_id = media_handle_upload('document_image', $post_id);

                if (!is_wp_error($attachment_id)) {
                    set_post_thumbnail($post_id, $attachment_id);
                }
            }

            wp_redirect(add_query_arg('message', ($action === 'update' ? 'updated' : 'created'), wp_get_referer()));
            exit;
        }
    }

    /**
     * Delete a document
     */
    private function delete_document()
    {
        $post_id = intval($_POST['post_id']);
        if ($post_id > 0) {
            wp_delete_post($post_id, true);
            wp_redirect(add_query_arg('message', 'deleted', wp_get_referer()));
            exit;
        }
    }

    /**
     * Shortcode: [dokumen_crud]
     */
    public function dokumen_crud_shortcode($atts)
    {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $args = array(
            'post_type'      => 'dokumen',
            'posts_per_page' => 10,
            'paged'          => $paged,
            'post_status'    => 'publish',
        );

        $query = new \WP_Query($args);

        // Get taxonomies for form
        $categories = get_terms(array('taxonomy' => 'document_category', 'hide_empty' => false));
        $zones      = get_terms(array('taxonomy' => 'zone', 'hide_empty' => false));

        // Check if editing
        $edit_post = null;
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['post_id'])) {
            $edit_post = get_post(intval($_GET['post_id']));
        }

        $data = array(
            'query'      => $query,
            'categories' => $categories,
            'zones'      => $zones,
            'edit_post'  => $edit_post,
            'message'    => isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '',
        );

        return \CustomPlugin\Core\Template::get('frontend/dokumen-crud', $data);
    }
}
