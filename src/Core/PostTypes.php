<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
  exit;
}

class PostTypes
{

  public function __construct()
  {
    add_action('init', array($this, 'register_post_types'));
  }

  public function register_post_types()
  {
    // Register 'Penilaian' Custom Post Type
    $penilaian_labels = array(
      'name'                  => 'Penilaian',
      'singular_name'         => 'Penilaian',
      'menu_name'             => 'Penilaian',
      'name_admin_bar'        => 'Penilaian',
      'all_items'             => 'Semua Penilaian',
      'add_new_item'          => 'Tambah Penilaian Baru',
      'add_new'               => 'Tambah Baru',
      'new_item'              => 'Penilaian Baru',
      'edit_item'             => 'Edit Penilaian',
      'update_item'           => 'Perbarui Penilaian',
      'view_item'             => 'Lihat Penilaian',
      'search_items'          => 'Cari Penilaian',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
    );
    $penilaian_args = array(
      'label'                 => 'Penilaian',
      'labels'                => $penilaian_labels,
      'supports'              => array(''), // Removed title support for auto-title
      'hierarchical'          => false,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 6,
      'menu_icon'             => 'dashicons-welcome-write-blog',
      'show_in_rest'          => true,
    );
    register_post_type('penilaian', $penilaian_args);
  }
}
