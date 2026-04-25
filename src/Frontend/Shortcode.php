<?php

namespace CustomPlugin\Frontend;

use CustomPlugin\Core\Template;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Shortcode
 * 
 * Best practice for Shortcode implementation.
 */
class Shortcode
{

    public function __construct()
    {
        add_shortcode('compare', array($this, 'compare_shortcode'));

        // add_shortcode('custom_hello', array($this, 'hello_shortcode'));
    }

    public function compare_shortcode($atts)
    {
        $atts = shortcode_atts(
            array(),
            $atts,
            'compare'
        );

        $properties = get_posts(array(
            'post_type'      => 'property',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));

        $first_id = isset($_GET['compare_property_1']) ? absint(wp_unslash($_GET['compare_property_1'])) : 0;
        $second_id = isset($_GET['compare_property_2']) ? absint(wp_unslash($_GET['compare_property_2'])) : 0;

        if (!$this->is_valid_property($first_id)) {
            $first_id = 0;
        }

        if (!$this->is_valid_property($second_id)) {
            $second_id = 0;
        }

        $compare_rows = array();

        if ($first_id && $second_id) {
            $compare_rows = $this->get_compare_rows($first_id, $second_id);
        }

        return Template::get('frontend/property-compare', array(
            'properties'   => $properties,
            'first_id'     => $first_id,
            'second_id'    => $second_id,
            'compare_rows' => $compare_rows,
        ));
    }

    private function is_valid_property($post_id)
    {
        if (!$post_id) {
            return false;
        }

        return get_post_type($post_id) === 'property' && get_post_status($post_id) === 'publish';
    }

    private function get_compare_rows($first_id, $second_id)
    {
        $rows = array(
            array(
                'label'  => 'Gambar',
                'first'  => $this->get_property_thumbnail($first_id),
                'second' => $this->get_property_thumbnail($second_id),
            ),
            array(
                'label'  => 'Tipe Properti',
                'first'  => $this->get_terms_text($first_id, 'property_type'),
                'second' => $this->get_terms_text($second_id, 'property_type'),
            ),
            array(
                'label'  => 'Lokasi',
                'first'  => $this->get_terms_text($first_id, 'location'),
                'second' => $this->get_terms_text($second_id, 'location'),
            ),
            array(
                'label'  => 'Proyek',
                'first'  => $this->get_terms_text($first_id, 'property_project'),
                'second' => $this->get_terms_text($second_id, 'property_project'),
            ),
            array(
                'label'  => 'Harga',
                'first'  => $this->format_price($this->get_meta($first_id, 'property_price')),
                'second' => $this->format_price($this->get_meta($second_id, 'property_price')),
            ),
            array(
                'label'  => 'Catatan Harga',
                'first'  => $this->get_meta_text($first_id, 'property_price_note'),
                'second' => $this->get_meta_text($second_id, 'property_price_note'),
            ),
            array(
                'label'  => 'Tipe Transaksi',
                'first'  => $this->get_mapped_meta($first_id, 'property_transaction_type', $this->transaction_type_options()),
                'second' => $this->get_mapped_meta($second_id, 'property_transaction_type', $this->transaction_type_options()),
            ),
            array(
                'label'  => 'Status Listing',
                'first'  => $this->get_mapped_meta($first_id, 'property_listing_status', $this->listing_status_options()),
                'second' => $this->get_mapped_meta($second_id, 'property_listing_status', $this->listing_status_options()),
            ),
            array(
                'label'  => 'Kamar Tidur',
                'first'  => $this->get_meta_text($first_id, 'property_bedrooms'),
                'second' => $this->get_meta_text($second_id, 'property_bedrooms'),
            ),
            array(
                'label'  => 'Kamar Mandi',
                'first'  => $this->get_meta_text($first_id, 'property_bathrooms'),
                'second' => $this->get_meta_text($second_id, 'property_bathrooms'),
            ),
            array(
                'label'  => 'Garasi / Carport',
                'first'  => $this->get_meta_text($first_id, 'property_garage'),
                'second' => $this->get_meta_text($second_id, 'property_garage'),
            ),
            array(
                'label'  => 'Luas Bangunan',
                'first'  => $this->format_area($this->get_meta($first_id, 'property_building_area')),
                'second' => $this->format_area($this->get_meta($second_id, 'property_building_area')),
            ),
            array(
                'label'  => 'Luas Tanah',
                'first'  => $this->format_area($this->get_meta($first_id, 'property_land_area')),
                'second' => $this->format_area($this->get_meta($second_id, 'property_land_area')),
            ),
            array(
                'label'  => 'Jumlah Lantai',
                'first'  => $this->get_meta_text($first_id, 'property_floors'),
                'second' => $this->get_meta_text($second_id, 'property_floors'),
            ),
            array(
                'label'  => 'Sertifikat',
                'first'  => $this->get_mapped_meta($first_id, 'property_certificate', $this->certificate_options()),
                'second' => $this->get_mapped_meta($second_id, 'property_certificate', $this->certificate_options()),
            ),
            array(
                'label'  => 'Kondisi Furnitur',
                'first'  => $this->get_mapped_meta($first_id, 'property_furnishing', $this->furnishing_options()),
                'second' => $this->get_mapped_meta($second_id, 'property_furnishing', $this->furnishing_options()),
            ),
            array(
                'label'  => 'Alamat',
                'first'  => $this->get_meta_text($first_id, 'property_address'),
                'second' => $this->get_meta_text($second_id, 'property_address'),
            ),
            array(
                'label'  => 'Kota',
                'first'  => $this->get_meta_text($first_id, 'property_city'),
                'second' => $this->get_meta_text($second_id, 'property_city'),
            ),
            array(
                'label'  => 'Provinsi',
                'first'  => $this->get_meta_text($first_id, 'property_province'),
                'second' => $this->get_meta_text($second_id, 'property_province'),
            ),
            array(
                'label'  => 'Galeri Foto',
                'first'  => $this->format_gallery_count($first_id),
                'second' => $this->format_gallery_count($second_id),
            ),
        );

        return apply_filters('custom_plugin_property_compare_rows', $rows, $first_id, $second_id);
    }

    private function get_property_thumbnail($post_id)
    {
        if (!has_post_thumbnail($post_id)) {
            return '-';
        }

        return get_the_post_thumbnail($post_id, 'medium', array(
            'class' => 'custom-property-compare__image',
            'alt'   => get_the_title($post_id),
        ));
    }

    private function get_terms_text($post_id, $taxonomy)
    {
        $terms = wp_get_post_terms($post_id, $taxonomy, array('fields' => 'names'));

        if (is_wp_error($terms) || empty($terms)) {
            return '-';
        }

        return implode(', ', $terms);
    }

    private function get_meta($post_id, $key)
    {
        return get_post_meta($post_id, $key, true);
    }

    private function get_meta_text($post_id, $key)
    {
        $value = $this->get_meta($post_id, $key);

        if ($value === '' || $value === array()) {
            return '-';
        }

        return is_array($value) ? implode(', ', array_filter($value)) : (string) $value;
    }

    private function get_mapped_meta($post_id, $key, $options)
    {
        $value = $this->get_meta($post_id, $key);

        if ($value === '') {
            return '-';
        }

        return isset($options[$value]) ? $options[$value] : $value;
    }

    private function format_price($price)
    {
        if ($price === '' || !is_numeric($price)) {
            return '-';
        }

        return Frontend::get_formatted_price((float) $price);
    }

    private function format_area($area)
    {
        if ($area === '' || !is_numeric($area)) {
            return '-';
        }

        return number_format((float) $area, 0, ',', '.') . ' m2';
    }

    private function format_gallery_count($post_id)
    {
        $gallery = get_post_meta($post_id, 'property_gallery', false);
        $gallery = array_filter($gallery);

        if (empty($gallery)) {
            return '-';
        }

        return count($gallery) . ' foto';
    }

    private function transaction_type_options()
    {
        return array(
            'sale' => 'Dijual',
            'rent' => 'Disewa',
        );
    }

    private function listing_status_options()
    {
        return array(
            'available' => 'Tersedia',
            'booked'    => 'Booked',
            'sold'      => 'Terjual',
            'rented'    => 'Tersewa',
        );
    }

    private function certificate_options()
    {
        return array(
            'shm'          => 'SHM',
            'hgb'          => 'HGB',
            'girik'        => 'Girik',
            'strata_title' => 'Strata Title',
            'other'        => 'Lainnya',
        );
    }

    private function furnishing_options()
    {
        return array(
            'unfurnished'    => 'Unfurnished',
            'semi_furnished' => 'Semi Furnished',
            'furnished'      => 'Furnished',
        );
    }

    /**
     * Shortcode: [custom_hello name="User"]
     * 
     * @param array $atts
     * @return string
     */
    public function hello_shortcode($atts)
    {
        // 1. Define default attributes and merge with user inputs
        $atts = shortcode_atts(
            array(
                'name' => 'User',
                'color' => 'blue'
            ),
            $atts,
            'custom_hello'
        );

        // 2. Data to pass to template (Logic)
        $data = array(
            'name'  => sanitize_text_field($atts['name']),
            'color' => sanitize_hex_color($atts['color']) ?: 'blue'
        );

        // 3. Render using Template Engine (Separation of Concerns)
        return Template::get('frontend/hello-message', $data);
    }
}
