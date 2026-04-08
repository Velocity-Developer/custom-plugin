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
        $this->register_mata_pelajaran();
        $this->register_jenis_penilaian();
    }

    /**
     * Register Mata Pelajaran Taxonomy
     */
    private function register_mata_pelajaran()
    {
        $labels = array(
            'name'              => 'Mata Pelajaran',
            'singular_name'     => 'Mata Pelajaran',
            'search_items'      => 'Cari Mata Pelajaran',
            'all_items'         => 'Semua Mata Pelajaran',
            'parent_item'       => 'Induk Mata Pelajaran',
            'parent_item_colon' => 'Induk Mata Pelajaran:',
            'edit_item'         => 'Edit Mata Pelajaran',
            'update_item'       => 'Perbarui Mata Pelajaran',
            'add_new_item'      => 'Tambah Mata Pelajaran Baru',
            'new_item_name'     => 'Nama Mata Pelajaran Baru',
            'menu_name'         => 'Mata Pelajaran',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'mata-pelajaran'),
            'show_in_rest'      => true,
        );

        register_taxonomy('mata_pelajaran', array('penilaian'), $args);
    }

    /**
     * Register Jenis Penilaian Taxonomy
     */
    private function register_jenis_penilaian()
    {
        $labels = array(
            'name'              => 'Jenis Penilaian',
            'singular_name'     => 'Jenis Penilaian',
            'search_items'      => 'Cari Jenis Penilaian',
            'all_items'         => 'Semua Jenis Penilaian',
            'parent_item'       => 'Induk Jenis Penilaian',
            'parent_item_colon' => 'Induk Jenis Penilaian:',
            'edit_item'         => 'Edit Jenis Penilaian',
            'update_item'       => 'Perbarui Jenis Penilaian',
            'add_new_item'      => 'Tambah Jenis Penilaian Baru',
            'new_item_name'     => 'Nama Jenis Penilaian Baru',
            'menu_name'         => 'Jenis Penilaian',
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'jenis-penilaian'),
            'show_in_rest'      => true,
        );

        register_taxonomy('jenis_penilaian', array('penilaian'), $args);
    }
}
