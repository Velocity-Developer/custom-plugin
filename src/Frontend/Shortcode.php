<?php

namespace CustomPlugin\Frontend;

use CustomPlugin\Core\SubmissionModel;
use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

class Shortcode
{

    public function __construct()
    {
        add_shortcode('custom_form', array($this, 'custom_form_shortcode'));
        add_shortcode('custom_message', array($this, 'custom_message_shortcode'));
        add_shortcode('custom_data', array($this, 'custom_data_shortcode'));
    }

    public function custom_form_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'button_text' => 'Kirim'
        ), $atts, 'custom_form');

        return Frontend::display_form();
    }

    public function custom_message_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'text' => 'Halo Dunia!',
            'style' => 'default'
        ), $atts, 'custom_message');

        $class = 'custom-plugin-message-' . sanitize_html_class($atts['style']);
        return '<div class="' . esc_attr($class) . '">' . esc_html($atts['text']) . '</div>';
    }

    public function custom_data_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'limit' => 10,
            'orderby' => 'created_at',
            'order' => 'DESC'
        ), $atts, 'custom_data');

        $results = SubmissionModel::get_all($atts);

        return Template::get('frontend/data-table', array(
            'results' => $results
        ));
    }
}
