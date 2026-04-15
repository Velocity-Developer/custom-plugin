<?php

/**
 * Plugin Name: Custom Plugin
 * Plugin URI: https://velocitydeveloper.com/
 * Description: Plugin kustom untuk menambahkan fitur-fitur khusus ke website WordPress Anda.
 * Version: 1.0.0
 * Author: Velocity Developer
 * Author URI: https://velocitydeveloper.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CUSTOM_PLUGIN_VERSION', '1.0.0');
define('CUSTOM_PLUGIN_FILE', __FILE__);
define('CUSTOM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CUSTOM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Always register a local PSR-4 autoloader so the plugin can boot even if
// Composer's generated autoload files are missing or outdated on the server.
spl_autoload_register(function ($class) {
    $prefix = 'CustomPlugin\\';
    $base_dir = CUSTOM_PLUGIN_DIR . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Load Composer's autoloader too when it is available.
if (file_exists(CUSTOM_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once CUSTOM_PLUGIN_DIR . 'vendor/autoload.php';
}

// Last-resort direct include for the main plugin class in case deployment
// contains an outdated autoload configuration.
if (!class_exists('\CustomPlugin\Core\Plugin')) {
    require_once CUSTOM_PLUGIN_DIR . 'src/Core/Plugin.php';
}

// Initialize Plugin
function custom_plugin_init()
{
    return \CustomPlugin\Core\Plugin::get_instance();
}
custom_plugin_init();

// Activation Hooks
register_activation_hook(__FILE__, array(custom_plugin_init(), 'activate'));
register_deactivation_hook(__FILE__, array(custom_plugin_init(), 'deactivate'));
