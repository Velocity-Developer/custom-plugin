<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
    exit;
}

class SubmissionModel
{
    /**
     * Get table name
     *
     * @return string
     */
    public static function get_table_name()
    {
        global $wpdb;
        return $wpdb->prefix . 'custom_plugin_data';
    }

    /**
     * Insert a new submission
     *
     * @param array $data
     * @return int|bool
     */
    public static function create($data)
    {
        global $wpdb;
        $table_name = self::get_table_name();

        return $wpdb->insert(
            $table_name,
            array(
                'name' => sanitize_text_field($data['name']),
                'email' => sanitize_email($data['email']),
                'message' => sanitize_textarea_field($data['message'])
            ),
            array('%s', '%s', '%s')
        );
    }

    /**
     * Get all submissions
     *
     * @param array $args
     * @return array
     */
    public static function get_all($args = array())
    {
        global $wpdb;
        $table_name = self::get_table_name();

        $defaults = array(
            'limit' => 10,
            'orderby' => 'created_at',
            'order' => 'DESC'
        );

        $args = wp_parse_args($args, $defaults);

        $limit = intval($args['limit']);
        $orderby = sanitize_sql_orderby($args['orderby']);
        $order = in_array(strtoupper($args['order']), array('ASC', 'DESC')) ? strtoupper($args['order']) : 'DESC';

        $query = $wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY {$orderby} {$order} LIMIT %d",
            $limit
        );

        return $wpdb->get_results($query);
    }
}
