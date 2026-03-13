<?php

namespace CustomPlugin\Api;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ExampleController
 * 
 * Best practice for WordPress REST API implementation.
 * Follows WP_REST_Controller patterns.
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
                'args'                => $this->get_collection_params(),
            ),
            array(
                'methods'             => \WP_REST_Server::CREATABLE, // POST
                'callback'            => array($this, 'create_item'),
                'permission_callback' => array($this, 'create_item_permissions_check'),
                'args'                => $this->get_endpoint_args_for_item_schema(\WP_REST_Server::CREATABLE),
            ),
            // Example of Schema endpoint
            'schema' => array($this, 'get_public_item_schema'),
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
        // Accessing query parameters (e.g., ?search=keyword)
        $search = $request->get_param('search');

        $data = array(
            'status'  => 'success',
            'message' => __('Ini adalah data dari REST API!', 'custom-plugin'),
            'search'  => $search,
            'time'    => current_time('mysql')
        );

        // Best practice: Always wrap response in rest_ensure_response
        return rest_ensure_response($data);
    }

    /**
     * Create item (POST callback).
     */
    public function create_item($request)
    {
        $params = $request->get_json_params();

        // 1. Validation (Best Practice)
        // Note: Basic validation is handled by 'args' in register_routes.
        if (empty($params['title'])) {
            return new \WP_Error('missing_field', __('Judul wajib diisi', 'custom-plugin'), array('status' => 400));
        }

        // 2. Logic (Business logic here)
        $title = sanitize_text_field($params['title']);

        // 3. Response (Best practice: return 201 Created status code for POST)
        return new \WP_REST_Response(
            array(
                'success' => true,
                'message' => sprintf(__('Item "%s" berhasil diproses!', 'custom-plugin'), $title),
                'id'      => 123 // Example ID
            ),
            201
        );
    }

    /**
     * Get the query params for collections (GET).
     */
    public function get_collection_params()
    {
        return array(
            'search' => array(
                'description'       => __('Search keyword.', 'custom-plugin'),
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'page' => array(
                'description'       => __('Current page of the collection.', 'custom-plugin'),
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
            ),
        );
    }

    /**
     * Get the arguments for creating an item (POST).
     */
    public function get_endpoint_args_for_item_schema($method = \WP_REST_Server::CREATABLE)
    {
        $args = array();
        if ($method === \WP_REST_Server::CREATABLE) {
            $args['title'] = array(
                'required'          => true,
                'type'              => 'string',
                'description'       => __('The title of the item.', 'custom-plugin'),
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => function($param, $request, $key) {
                    return !empty($param) && is_string($param);
                }
            );
        }
        return $args;
    }

    /**
     * Get the item's schema, conforming to JSON Schema.
     * Best practice for API documentation.
     */
    public function get_public_item_schema()
    {
        $schema = array(
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'example',
            'type'       => 'object',
            'properties' => array(
                'id' => array(
                    'description' => __('Unique identifier for the object.', 'custom-plugin'),
                    'type'        => 'integer',
                    'readonly'    => true,
                ),
                'title' => array(
                    'description' => __('The title of the item.', 'custom-plugin'),
                    'type'        => 'string',
                ),
            ),
        );

        return $schema;
    }
}
