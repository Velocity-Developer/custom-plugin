<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Template
{
    /**
     * Render a template file
     *
     * @param string $template_name Template name without .php extension
     * @param array $args Data to be passed to the template
     * @return void
     */
    public static function render($template_name, $args = array())
    {
        // 1. Check if the theme has a template override (e.g., in your-theme/custom-plugin/template.php)
        $theme_file = locate_template('custom-plugin/' . $template_name . '.php');

        if ($theme_file) {
            $file = $theme_file;
        } else {
            // 2. Fallback to plugin template
            $file = CUSTOM_PLUGIN_DIR . 'templates/' . $template_name . '.php';
        }

        // 3. Allow developers to filter the template path (Custom Filter Hook)
        $file = apply_filters('custom_plugin_get_template', $file, $template_name, $args);

        if (file_exists($file)) {
            // Extract arguments to make them available in the template
            if (!empty($args)) {
                extract($args);
            }

            include $file;
        }
    }

    /**
     * Get a template content as string
     *
     * @param string $template_name
     * @param array $args
     * @return string
     */
    public static function get($template_name, $args = array())
    {
        ob_start();
        self::render($template_name, $args);
        return ob_get_clean();
    }
}
