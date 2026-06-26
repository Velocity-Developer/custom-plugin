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
        add_shortcode('store_product_cat_list', array($this, 'store_product_cat_list'));
    }

    /**
     * Shortcode: [store_product_cat_list]
     * Menampilkan taxonomy store_product_cat - tampilan list sederhana 3 kolom.
     *
     * @return string
     */
    public function store_product_cat_list()
    {
        $terms = get_terms(array(
            'taxonomy'   => 'store_product_cat',
            'hide_empty' => false,
        ));

        if (is_wp_error($terms) || empty($terms)) {
            return '';
        }

        ob_start();
?>
<div class="row g-3">
    <?php foreach ($terms as $term):
                $term_link = get_term_link($term);
            ?>
    <span class="col-lg-4 col-md-4 col-6">
        <a href="<?php echo esc_url($term_link); ?>">
            <?php echo esc_html($term->name); ?>
        </a>
    </span>
    <?php endforeach; ?>
</div>
<?php
        return ob_get_clean();
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