<?php

namespace CustomPlugin\Api;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ExampleController
 * 
 * Best practice for WordPress REST API implementation.
 */
class ExampleController
{
    /**
     * The namespace for the API routes.
     * @var string
     */
    protected $namespace = 'custom-plugin/v1';

    /**
     * The base resource name.
     * @var string
     */
    protected $rest_base = 'example';

    public function __construct()
    {
        // Example: To activate REST API routes, uncomment the line below.
        // add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'             => \WP_REST_Server::READABLE, // GET
                'callback'            => array($this, 'get_items'),
                'permission_callback' => array($this, 'get_items_permissions_check'),
            ),
            array(
                'methods'             => \WP_REST_Server::CREATABLE, // POST
                'callback'            => array($this, 'create_item'),
                'permission_callback' => array($this, 'create_item_permissions_check'),
                'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
            ),
        ));
    }

    /**
     * Permissions check for GET request.
     */
    public function get_items_permissions_check($request)
    {
        // Best practice: Always check permissions
        return current_user_can('manage_options');
    }

    /**
     * Permissions check for POST request.
     */
    public function create_item_permissions_check($request)
    {
        // For public forms, you might return true, but check nonces.
        // return true;
        return current_user_can('manage_options');
    }

    /**
     * Get items (GET callback).
     */
    public function get_items($request)
    {
        $data = array(
            'status'  => 'success',
            'message' => __('Ini adalah data dari REST API!', 'custom-plugin'),
            'time'    => current_time('mysql')
        );

        return rest_ensure_response($data);
    }

    /**
     * Create item (POST callback).
     */
    public function create_item($request)
    {
        $params = $request->get_json_params();

        // 1. Validation (Best Practice)
        if (empty($params['title'])) {
            return new \WP_Error('missing_field', __('Judul wajib diisi', 'custom-plugin'), array('status' => 400));
        }

        // 2. Logic
        $title = sanitize_text_field($params['title']);

        // 3. Response
        return rest_ensure_response(array(
            'success' => true,
            'message' => sprintf(__('Item "%s" berhasil diproses!', 'custom-plugin'), $title)
        ));
    }

    /**
     * Get the query params for collections.
     */
    public function get_endpoint_args_for_item_schema($method = \WP_REST_Server::CREATABLE)
    {
        $args = array();
        if ($method === \WP_REST_Server::CREATABLE) {
            $args['title'] = array(
                'required'          => true,
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function($param, $request, $key) {
                    return !empty($param);
                }
            );
        }
        return $args;
    }
}
