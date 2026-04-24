<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
    exit;
}

class Taxonomies
{

    public function __construct()
    {
        add_action('init', array($this, 'register_taxonomies'));
    }

    public function register_taxonomies()
    {
        $this->register_location_taxonomy();
        $this->register_property_type_taxonomy();
        $this->register_property_project_taxonomy();
        // Example: Register 'project_category' Taxonomy
        // $this->register_project_category();
    }

    /**
     * Register Location Taxonomy
     */
    private function register_location_taxonomy()
    {
        $labels = array(
            'name'              => 'Lokasi',
            'singular_name'     => 'Lokasi',
            'search_items'      => 'Cari Lokasi',
            'all_items'         => 'Semua Lokasi',
            'parent_item'       => 'Induk Lokasi',
            'parent_item_colon' => 'Induk Lokasi:',
            'edit_item'         => 'Edit Lokasi',
            'update_item'       => 'Perbarui Lokasi',
            'add_new_item'      => 'Tambah Lokasi Baru',
            'new_item_name'     => 'Nama Lokasi Baru',
            'menu_name'         => 'Lokasi',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'location'),
            'show_in_rest'      => true,
        );

        register_taxonomy('location', array('property'), $args);
    }

    /**
     * Register Property Type Taxonomy
     */
    private function register_property_type_taxonomy()
    {
        $labels = array(
            'name'              => 'Tipe Properti',
            'singular_name'     => 'Tipe Properti',
            'search_items'      => 'Cari Tipe Properti',
            'all_items'         => 'Semua Tipe Properti',
            'parent_item'       => 'Induk Tipe Properti',
            'parent_item_colon' => 'Induk Tipe Properti:',
            'edit_item'         => 'Edit Tipe Properti',
            'update_item'       => 'Perbarui Tipe Properti',
            'add_new_item'      => 'Tambah Tipe Properti Baru',
            'new_item_name'     => 'Nama Tipe Properti Baru',
            'menu_name'         => 'Tipe Properti',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-type'),
            'show_in_rest'      => true,
        );

        register_taxonomy('property_type', array('property'), $args);
    }

    /**
     * Register Property Project Taxonomy
     */
    private function register_property_project_taxonomy()
    {
        $labels = array(
            'name'              => 'Proyek',
            'singular_name'     => 'Proyek',
            'search_items'      => 'Cari Proyek',
            'all_items'         => 'Semua Proyek',
            'parent_item'       => 'Induk Proyek',
            'parent_item_colon' => 'Induk Proyek:',
            'edit_item'         => 'Edit Proyek',
            'update_item'       => 'Perbarui Proyek',
            'add_new_item'      => 'Tambah Proyek Baru',
            'new_item_name'     => 'Nama Proyek Baru',
            'menu_name'         => 'Proyek',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-project'),
            'show_in_rest'      => true,
        );

        register_taxonomy('property_project', array('property'), $args);
    }

    /**
     * Register Project Category Taxonomy
     */
    private function register_project_category()
    {
        $labels = array(
            'name'              => 'Kategori Proyek',
            'singular_name'     => 'Kategori Proyek',
            'search_items'      => 'Cari Kategori Proyek',
            'all_items'         => 'Semua Kategori Proyek',
            'parent_item'       => 'Induk Kategori Proyek',
            'parent_item_colon' => 'Induk Kategori Proyek:',
            'edit_item'         => 'Edit Kategori Proyek',
            'update_item'       => 'Perbarui Kategori Proyek',
            'add_new_item'      => 'Tambah Kategori Proyek Baru',
            'new_item_name'     => 'Nama Kategori Proyek Baru',
            'menu_name'         => 'Kategori Proyek',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'project-category'),
            'show_in_rest'      => true, // Enable Gutenberg editor support
        );

        register_taxonomy('project_category', array('project'), $args);
    }
}
