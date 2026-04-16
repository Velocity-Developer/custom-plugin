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

        // Shortcode: [daftar_dokumen zone="" kategori="" limit="10"]
        add_shortcode('daftar_dokumen', array($this, 'daftar_dokumen_shortcode'));

        // Shortcode: [custom_login]
        add_shortcode('custom_login', array($this, 'custom_login_shortcode'));
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
            case 'login':
                $this->handle_login();
                break;
        }
    }

    /**
     * Handle Login Request
     */
    private function handle_login()
    {
        if (!isset($_POST['login_nonce']) || !wp_verify_nonce($_POST['login_nonce'], 'custom_login_nonce')) {
            return;
        }

        // Verify Captcha
        $user_captcha = isset($_POST['captcha']) ? sanitize_text_field($_POST['captcha']) : '';
        $stored_captcha = isset($_COOKIE['custom_captcha']) ? $_COOKIE['custom_captcha'] : '';

        if (empty($user_captcha) || $user_captcha !== $stored_captcha) {
            wp_redirect(add_query_arg('login_error', 'captcha', wp_get_referer()));
            exit;
        }

        $creds = array(
            'user_login'    => sanitize_user($_POST['log']),
            'user_password' => $_POST['pwd'],
            'remember'      => isset($_POST['rememberme']),
        );

        $user = wp_signon($creds, is_ssl());

        if (is_wp_error($user)) {
            wp_redirect(add_query_arg('login_error', 'failed', wp_get_referer()));
            exit;
        }

        // Success, redirect to home or data page
        wp_redirect(home_url('/data'));
        exit;
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

    /**
     * Shortcode: [daftar_dokumen]
     * Menampilkan daftar dokumen dalam tabel dengan pagination
     */
    public function daftar_dokumen_shortcode($atts)
    {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $atts = shortcode_atts(array(
            'zone'     => '',
            'kategori' => '',
            'limit'    => 10,
        ), $atts);

        $args = array(
            'post_type'      => 'dokumen',
            'post_status'    => 'publish',
            'posts_per_page' => intval($atts['limit']),
            'paged'          => $paged,
        );

        $tax_query = array();

        if (!empty($atts['zone'])) {
            $tax_query[] = array(
                'taxonomy' => 'zone',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($atts['zone']),
            );
        }

        if (!empty($atts['kategori'])) {
            $tax_query[] = array(
                'taxonomy' => 'document_category',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($atts['kategori']),
            );
        }

        if (count($tax_query) > 1) {
            $tax_query['relation'] = 'AND';
        }

        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }

        $query = new \WP_Query($args);

        $data = array(
            'query' => $query,
            'paged' => $paged,
        );

        return \CustomPlugin\Core\Template::get('frontend/daftar-dokumen', $data);
    }

    /**
     * Shortcode: [custom_login]
     */
    public function custom_login_shortcode()
    {
        if (is_user_logged_in()) {
            return '<div class="alert alert-info">Anda sudah login. <a href="' . wp_logout_url(get_permalink()) . '">Logout</a></div>';
        }

        // Generate captcha
        $captcha_str = substr(str_shuffle("ABCDEFGHJKLMNPQRSTUVWXYZ23456789"), 0, 5);
        setcookie('custom_captcha', $captcha_str, time() + 300, '/');

        $data = array(
            'captcha_text' => $captcha_str,
            'error'        => isset($_GET['login_error']) ? $_GET['login_error'] : '',
        );

        return \CustomPlugin\Core\Template::get('frontend/login-form', $data);
    }
}
