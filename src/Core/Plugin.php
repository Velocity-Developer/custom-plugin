<?php

namespace CustomPlugin\Core;

use CustomPlugin\Admin\Admin;
use CustomPlugin\Frontend\Frontend;
use CustomPlugin\Frontend\Shortcode;
use CustomPlugin\Api\ExampleController;
use CustomPlugin\Core\PostTypes;
use CustomPlugin\Core\Taxonomies;
use CustomPlugin\Core\CoreFeatures;

if (!defined('ABSPATH')) {
  exit;
}

class Plugin
{

  private static $instance = null;

  public static function get_instance()
  {
    if (null === self::$instance) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  private function __construct()
  {
    // Hooks that need to run immediately
    add_action('plugins_loaded', array($this, 'init'));
  }

  public function init()
  {
    // Load internationalization
    add_action('init', array($this, 'load_textdomain'));

    // Initialize core modules
    new PostTypes();
    new Taxonomies();
    new CoreFeatures();

    // Example modules - uncomment to use or for reference
    // new Admin();
    // new Frontend();
    // new Shortcode();
    // new ExampleController();
  }

  public function activate()
  {
    add_option('custom_plugin_version', CUSTOM_PLUGIN_VERSION);
    flush_rewrite_rules();
  }

  public function deactivate()
  {
    flush_rewrite_rules();
  }

  public function load_textdomain()
  {
    load_plugin_textdomain(
      'custom-plugin',
      false,
      dirname(plugin_basename(CUSTOM_PLUGIN_FILE)) . '/languages/'
    );
  }
}
