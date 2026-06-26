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
     * Menampilkan taxonomy store_product_cat dengan Bootstrap 5.3 grid 3 kolom → 2 kolom.
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
        <div class="row g-4">
            <?php foreach ($terms as $term):
                $term_link = get_term_link($term);
            ?>
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="border p-3 rounded h-100">
                        <span class="d-block mb-2">
                            <a href="<?php echo esc_url($term_link); ?>" class="text-decoration-none fw-semibold">
                                <?php echo esc_html($term->name); ?>
                            </a>
                        </span>
                        <?php if (!empty($term->description)): ?>
                            <span class="d-block text-muted small">
                                <?php echo esc_html($term->description); ?>
                            </span>
                        <?php endif; ?>
                        <span class="d-block mt-2">
                            <a href="<?php echo esc_url($term_link); ?>" class="btn btn-outline-primary btn-sm">Lihat Produk</a>
                        </span>
                    </div>
                </div>
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
