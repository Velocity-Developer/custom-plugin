<?php

namespace CustomPlugin\Core;

if (!defined('ABSPATH')) {
    exit;
}

class PropertyMetaBoxes
{

    public function __construct()
    {
        add_filter('rwmb_meta_boxes', array($this, 'register_meta_boxes'));
    }

    public function register_meta_boxes($meta_boxes)
    {
        $meta_boxes[] = array(
            'title'      => 'Detail Properti',
            'id'         => 'property_details',
            'post_types' => array('property'),
            'context'    => 'normal',
            'priority'   => 'high',
            'fields'     => array(
                array(
                    'name' => 'Harga',
                    'id'   => 'property_price',
                    'type' => 'number',
                    'min'  => 0,
                    'step' => 1000000,
                ),
                array(
                    'name' => 'Catatan Harga',
                    'id'   => 'property_price_note',
                    'type' => 'text',
                    'desc' => 'Contoh: nego, per bulan, atau mulai dari.',
                ),
                array(
                    'name'    => 'Tipe Transaksi',
                    'id'      => 'property_transaction_type',
                    'type'    => 'select',
                    'options' => array(
                        'sale' => 'Dijual',
                        'rent' => 'Disewa',
                    ),
                ),
                array(
                    'name'    => 'Status Listing',
                    'id'      => 'property_listing_status',
                    'type'    => 'select',
                    'options' => array(
                        'available' => 'Tersedia',
                        'booked'    => 'Booked',
                        'sold'      => 'Terjual',
                        'rented'    => 'Tersewa',
                    ),
                ),
                array(
                    'name' => 'Properti Unggulan',
                    'id'   => 'property_featured',
                    'type' => 'checkbox',
                ),
                array(
                    'name' => 'URL Video',
                    'id'   => 'property_video_url',
                    'type' => 'url',
                ),
            ),
        );

        $meta_boxes[] = array(
            'title'      => 'Spesifikasi Properti',
            'id'         => 'property_specifications',
            'post_types' => array('property'),
            'context'    => 'normal',
            'priority'   => 'default',
            'fields'     => array(
                array(
                    'name' => 'Kamar Tidur',
                    'id'   => 'property_bedrooms',
                    'type' => 'number',
                    'min'  => 0,
                ),
                array(
                    'name' => 'Kamar Mandi',
                    'id'   => 'property_bathrooms',
                    'type' => 'number',
                    'min'  => 0,
                ),
                array(
                    'name' => 'Garasi / Carport',
                    'id'   => 'property_garage',
                    'type' => 'number',
                    'min'  => 0,
                ),
                array(
                    'name' => 'Luas Bangunan (m2)',
                    'id'   => 'property_building_area',
                    'type' => 'number',
                    'min'  => 0,
                ),
                array(
                    'name' => 'Luas Tanah (m2)',
                    'id'   => 'property_land_area',
                    'type' => 'number',
                    'min'  => 0,
                ),
                array(
                    'name' => 'Jumlah Lantai',
                    'id'   => 'property_floors',
                    'type' => 'number',
                    'min'  => 0,
                ),
                array(
                    'name'    => 'Sertifikat',
                    'id'      => 'property_certificate',
                    'type'    => 'select',
                    'options' => array(
                        'shm'          => 'SHM',
                        'hgb'          => 'HGB',
                        'girik'        => 'Girik',
                        'strata_title' => 'Strata Title',
                        'other'        => 'Lainnya',
                    ),
                ),
                array(
                    'name'    => 'Kondisi Furnitur',
                    'id'      => 'property_furnishing',
                    'type'    => 'select',
                    'options' => array(
                        'unfurnished'    => 'Unfurnished',
                        'semi_furnished' => 'Semi Furnished',
                        'furnished'      => 'Furnished',
                    ),
                ),
            ),
        );

        $meta_boxes[] = array(
            'title'      => 'Lokasi Properti',
            'id'         => 'property_location_details',
            'post_types' => array('property'),
            'context'    => 'normal',
            'priority'   => 'default',
            'fields'     => array(
                array(
                    'name' => 'Alamat Lengkap',
                    'id'   => 'property_address',
                    'type' => 'textarea',
                    'rows' => 3,
                ),
                array(
                    'name' => 'Kota',
                    'id'   => 'property_city',
                    'type' => 'text',
                ),
                array(
                    'name' => 'Provinsi',
                    'id'   => 'property_province',
                    'type' => 'text',
                ),
                array(
                    'name' => 'Kode Pos',
                    'id'   => 'property_postal_code',
                    'type' => 'text',
                ),
                array(
                    'name' => 'Latitude',
                    'id'   => 'property_latitude',
                    'type' => 'number',
                    'step' => 'any',
                ),
                array(
                    'name' => 'Longitude',
                    'id'   => 'property_longitude',
                    'type' => 'number',
                    'step' => 'any',
                ),
            ),
        );

        $meta_boxes[] = array(
            'title'      => 'Galeri Properti',
            'id'         => 'property_gallery_meta',
            'post_types' => array('property'),
            'context'    => 'normal',
            'priority'   => 'default',
            'fields'     => array(
                array(
                    'name'             => 'Galeri Foto',
                    'id'               => 'property_gallery',
                    'type'             => 'image_advanced',
                    'max_file_uploads' => 20,
                ),
            ),
        );

        return $meta_boxes;
    }
}
