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
    $this->register_property_post_type();
    // Example: Register 'Project' Custom Post Type
    // Uncomment the lines below to enable
    /*
        $labels = array(
            'name'                  => 'Proyek',
            'singular_name'         => 'Proyek',
            'menu_name'             => 'Proyek',
            'name_admin_bar'        => 'Proyek',
            'archives'              => 'Arsip Proyek',
            'attributes'            => 'Atribut Proyek',
            'parent_item_colon'     => 'Induk Proyek:',
            'all_items'             => 'Semua Proyek',
            'add_new_item'          => 'Tambah Proyek Baru',
            'add_new'               => 'Tambah Baru',
            'new_item'              => 'Proyek Baru',
            'edit_item'             => 'Edit Proyek',
            'update_item'           => 'Perbarui Proyek',
            'view_item'             => 'Lihat Proyek',
            'view_items'            => 'Lihat Proyek',
            'search_items'          => 'Cari Proyek',
            'not_found'             => 'Tidak ditemukan',
            'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
            'featured_image'        => 'Gambar Utama',
            'set_featured_image'    => 'Atur gambar utama',
            'remove_featured_image' => 'Hapus gambar utama',
            'use_featured_image'    => 'Gunakan sebagai gambar utama',
            'insert_into_item'      => 'Masukkan ke dalam proyek',
            'uploaded_to_this_item' => 'Diunggah ke proyek ini',
            'items_list'            => 'Daftar proyek',
            'items_list_navigation' => 'Navigasi daftar proyek',
            'filter_items_list'     => 'Filter daftar proyek',
        );
        $args = array(
            'label'                 => 'Proyek',
            'description'           => 'Deskripsi Tipe Postingan',
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
            'taxonomies'            => array('project_category'), // Make sure this taxonomy is registered
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-portfolio', // https://developer.wordpress.org/resource/dashicons/
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true, // Enable Gutenberg
        );
        register_post_type('project', $args);
        */

    // You can add more Custom Post Types here
  }

  /**
   * Register Property Custom Post Type
   */
  private function register_property_post_type()
  {
    $labels = array(
      'name'                  => 'Properti',
      'singular_name'         => 'Properti',
      'menu_name'             => 'Properti',
      'name_admin_bar'        => 'Properti',
      'archives'              => 'Arsip Properti',
      'attributes'            => 'Atribut Properti',
      'parent_item_colon'     => 'Induk Properti:',
      'all_items'             => 'Semua Properti',
      'add_new_item'          => 'Tambah Properti Baru',
      'add_new'               => 'Tambah Baru',
      'new_item'              => 'Properti Baru',
      'edit_item'             => 'Edit Properti',
      'update_item'           => 'Perbarui Properti',
      'view_item'             => 'Lihat Properti',
      'view_items'            => 'Lihat Properti',
      'search_items'          => 'Cari Properti',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
      'featured_image'        => 'Gambar Utama',
      'set_featured_image'    => 'Atur gambar utama',
      'remove_featured_image' => 'Hapus gambar utama',
      'use_featured_image'    => 'Gunakan sebagai gambar utama',
      'insert_into_item'      => 'Masukkan ke dalam properti',
      'uploaded_to_this_item' => 'Diunggah ke properti ini',
      'items_list'            => 'Daftar properti',
      'items_list_navigation' => 'Navigasi daftar properti',
      'filter_items_list'     => 'Filter daftar properti',
    );
    $args = array(
      'label'                 => 'Properti',
      'description'           => 'Tipe Postingan Properti',
      'labels'                => $labels,
      'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions'),
      'taxonomies'            => array('location', 'property_type', 'property_project'),
      'hierarchical'          => false,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 5,
      'menu_icon'             => 'dashicons-admin-home',
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'can_export'            => true,
      'has_archive'           => true,
      'exclude_from_search'   => false,
      'publicly_queryable'    => true,
      'capability_type'       => 'post',
      'show_in_rest'          => true,
    );
    register_post_type('property', $args);
  }
}
