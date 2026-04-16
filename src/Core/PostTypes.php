<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
  exit;
}

class PostTypes
{
  private $document_categories = array(
    'Production oil',
    'Production gas',
    'Kemitraan Idle Well',
    'Kemitraan Sumur Masyarakat (BKU)',
    'Kemitraan Sumur Tua',
    'Kemitraan Idle Structure',
    'Non Operator',
    'DS LNG',
    'KSO Existing',
  );

  public function __construct()
  {
    add_action('init', array($this, 'register_post_types'));
  }

  public function register_post_types()
  {
    $this->register_document_category_taxonomy();
    $this->register_zone_taxonomy();
    $this->register_document_post_type();
    $this->register_history_post_type();
    $this->register_default_document_categories();
  }

  private function register_document_post_type()
  {
    $labels = array(
      'name'                  => 'Dokumen',
      'singular_name'         => 'Dokumen',
      'menu_name'             => 'Dokumen',
      'name_admin_bar'        => 'Dokumen',
      'archives'              => 'Arsip Dokumen',
      'attributes'            => 'Atribut Dokumen',
      'parent_item_colon'     => 'Induk Dokumen:',
      'all_items'             => 'Semua Dokumen',
      'add_new_item'          => 'Tambah Dokumen Baru',
      'add_new'               => 'Tambah Baru',
      'new_item'              => 'Dokumen Baru',
      'edit_item'             => 'Edit Dokumen',
      'update_item'           => 'Perbarui Dokumen',
      'view_item'             => 'Lihat Dokumen',
      'view_items'            => 'Lihat Dokumen',
      'search_items'          => 'Cari Dokumen',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
      'featured_image'        => 'Gambar Utama',
      'set_featured_image'    => 'Atur gambar utama',
      'remove_featured_image' => 'Hapus gambar utama',
      'use_featured_image'    => 'Gunakan sebagai gambar utama',
      'insert_into_item'      => 'Masukkan ke dalam dokumen',
      'uploaded_to_this_item' => 'Diunggah ke dokumen ini',
      'items_list'            => 'Daftar dokumen',
      'items_list_navigation' => 'Navigasi daftar dokumen',
      'filter_items_list'     => 'Filter daftar dokumen',
    );

    $args = array(
      'label'               => 'Dokumen',
      'description'         => 'Dokumen operasional dan kemitraan',
      'labels'              => $labels,
      'supports'            => array('title', 'editor', 'excerpt', 'thumbnail'),
      'taxonomies'          => array('document_category', 'zone'),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 5,
      'menu_icon'           => 'dashicons-media-document',
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => true,
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'post',
      'show_in_rest'        => true,
      'rewrite'             => array('slug' => 'dokumen'),
    );

    register_post_type('dokumen', $args);
  }

  private function register_history_post_type()
  {
    $labels = array(
      'name'                  => 'History',
      'singular_name'         => 'History',
      'menu_name'             => 'History',
      'name_admin_bar'        => 'History',
      'archives'              => 'Arsip History',
      'attributes'            => 'Atribut History',
      'parent_item_colon'     => 'Induk History:',
      'all_items'             => 'Semua History',
      'add_new_item'          => 'Tambah History Baru',
      'add_new'               => 'Tambah Baru',
      'new_item'              => 'History Baru',
      'edit_item'             => 'Edit History',
      'update_item'           => 'Perbarui History',
      'view_item'             => 'Lihat History',
      'view_items'            => 'Lihat History',
      'search_items'          => 'Cari History',
      'not_found'             => 'Tidak ditemukan',
      'not_found_in_trash'    => 'Tidak ditemukan di Tong Sampah',
      'featured_image'        => 'Gambar Utama',
      'set_featured_image'    => 'Atur gambar utama',
      'remove_featured_image' => 'Hapus gambar utama',
      'use_featured_image'    => 'Gunakan sebagai gambar utama',
      'insert_into_item'      => 'Masukkan ke dalam history',
      'uploaded_to_this_item' => 'Diunggah ke history ini',
      'items_list'            => 'Daftar history',
      'items_list_navigation' => 'Navigasi daftar history',
      'filter_items_list'     => 'Filter daftar history',
    );

    $args = array(
      'label'               => 'History',
      'description'         => 'History data dan transaksi',
      'labels'              => $labels,
      'supports'            => array('title', 'editor', 'excerpt', 'thumbnail'),
      'taxonomies'          => array('document_category', 'zone'),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'menu_position'       => 6,
      'menu_icon'           => 'dashicons-backup',
      'show_in_admin_bar'   => true,
      'show_in_nav_menus'   => true,
      'can_export'          => true,
      'has_archive'         => true,
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'post',
      'show_in_rest'        => true,
      'rewrite'             => array('slug' => 'history'),
    );

    register_post_type('history', $args);
  }

  private function register_document_category_taxonomy()
  {
    $labels = array(
      'name'              => 'Kategori Dokumen',
      'singular_name'     => 'Kategori Dokumen',
      'search_items'      => 'Cari Kategori Dokumen',
      'all_items'         => 'Semua Kategori Dokumen',
      'parent_item'       => 'Induk Kategori Dokumen',
      'parent_item_colon' => 'Induk Kategori Dokumen:',
      'edit_item'         => 'Edit Kategori Dokumen',
      'update_item'       => 'Perbarui Kategori Dokumen',
      'add_new_item'      => 'Tambah Kategori Dokumen Baru',
      'new_item_name'     => 'Nama Kategori Dokumen Baru',
      'menu_name'         => 'Kategori Dokumen',
    );

    $args = array(
      'hierarchical'      => true,
      'labels'            => $labels,
      'show_ui'           => true,
      'show_admin_column' => true,
      'query_var'         => true,
      'rewrite'           => array('slug' => 'kategori-dokumen'),
      'show_in_rest'      => true,
    );

    register_taxonomy('document_category', array('dokumen', 'history'), $args);
  }

  private function register_zone_taxonomy()
  {
    $labels = array(
      'name'              => 'Zone',
      'singular_name'     => 'Zone',
      'search_items'      => 'Cari Zone',
      'all_items'         => 'Semua Zone',
      'parent_item'       => 'Induk Zone',
      'parent_item_colon' => 'Induk Zone:',
      'edit_item'         => 'Edit Zone',
      'update_item'       => 'Perbarui Zone',
      'add_new_item'      => 'Tambah Zone Baru',
      'new_item_name'     => 'Nama Zone Baru',
      'menu_name'         => 'Zone',
    );

    $args = array(
      'hierarchical'      => true,
      'labels'            => $labels,
      'show_ui'           => true,
      'show_admin_column' => true,
      'query_var'         => true,
      'rewrite'           => array('slug' => 'zone'),
      'show_in_rest'      => true,
    );

    register_taxonomy('zone', array('dokumen', 'history'), $args);
  }

  private function register_default_document_categories()
  {
    foreach ($this->document_categories as $category_name) {
      if (term_exists($category_name, 'document_category')) {
        continue;
      }

      wp_insert_term(
        $category_name,
        'document_category',
        array(
          'slug' => sanitize_title($category_name),
        )
      );
    }
  }
}
