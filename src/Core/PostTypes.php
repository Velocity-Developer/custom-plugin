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
    $this->register_product_post_type();
    $this->register_order_post_type();

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

  private function register_product_post_type()
  {
    $labels = array(
      'name'                  => 'Produk',
      'singular_name'         => 'Produk',
      'menu_name'             => 'Produk',
      'name_admin_bar'        => 'Produk',
      'archives'              => 'Arsip Produk',
      'attributes'            => 'Atribut Produk',
      'parent_item_colon'     => 'Induk Produk:',
      'all_items'             => 'Semua Produk',
      'add_new_item'          => 'Tambah Produk Baru',
      'add_new'               => 'Tambah Baru',
      'new_item'              => 'Produk Baru',
      'edit_item'             => 'Edit Produk',
      'update_item'           => 'Perbarui Produk',
      'view_item'             => 'Lihat Produk',
      'view_items'            => 'Lihat Produk',
      'search_items'          => 'Cari Produk',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
      'featured_image'        => 'Gambar Utama',
      'set_featured_image'    => 'Atur gambar utama',
      'remove_featured_image' => 'Hapus gambar utama',
      'use_featured_image'    => 'Gunakan sebagai gambar utama',
      'insert_into_item'      => 'Masukkan ke dalam produk',
      'uploaded_to_this_item' => 'Diunggah ke produk ini',
      'items_list'            => 'Daftar produk',
      'items_list_navigation' => 'Navigasi daftar produk',
      'filter_items_list'     => 'Filter daftar produk',
    );

    $args = array(
      'label'               => 'Produk',
      'description'         => 'Custom post type untuk data produk',
      'labels'              => $labels,
      'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 6,
      'menu_icon'           => 'dashicons-products',
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => true,
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'post',
      'show_in_rest'        => true,
      'rewrite'             => array('slug' => 'produk'),
    );

    register_post_type('produk', $args);
  }

  private function register_order_post_type()
  {
    $labels = array(
      'name'                  => 'Order',
      'singular_name'         => 'Order',
      'menu_name'             => 'Order',
      'name_admin_bar'        => 'Order',
      'archives'              => 'Arsip Order',
      'attributes'            => 'Atribut Order',
      'parent_item_colon'     => 'Induk Order:',
      'all_items'             => 'Semua Order',
      'add_new_item'          => 'Tambah Order Baru',
      'add_new'               => 'Tambah Baru',
      'new_item'              => 'Order Baru',
      'edit_item'             => 'Edit Order',
      'update_item'           => 'Perbarui Order',
      'view_item'             => 'Lihat Order',
      'view_items'            => 'Lihat Order',
      'search_items'          => 'Cari Order',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
      'featured_image'        => 'Bukti Utama',
      'set_featured_image'    => 'Atur bukti utama',
      'remove_featured_image' => 'Hapus bukti utama',
      'use_featured_image'    => 'Gunakan sebagai bukti utama',
      'insert_into_item'      => 'Masukkan ke dalam order',
      'uploaded_to_this_item' => 'Diunggah ke order ini',
      'items_list'            => 'Daftar order',
      'items_list_navigation' => 'Navigasi daftar order',
      'filter_items_list'     => 'Filter daftar order',
    );

    $args = array(
      'label'               => 'Order',
      'description'         => 'Custom post type untuk data pesanan pelanggan',
      'labels'              => $labels,
      'supports'            => array('title'),
      'hierarchical'        => false,
      'public'              => false,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 7,
      'menu_icon'           => 'dashicons-cart',
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => false,
      'can_export'          => true,
      'has_archive'         => false,
      'exclude_from_search' => true,
      'publicly_queryable'  => false,
      'capability_type'     => 'post',
      'show_in_rest'        => true,
      'map_meta_cap'        => true,
      'rewrite'             => false,
    );

    register_post_type('order', $args);
  }
}
