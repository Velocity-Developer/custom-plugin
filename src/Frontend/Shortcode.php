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
        add_shortcode('login_logout_btn', array($this, 'login_logout_btn'));
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
    <span class="col-4 col-md-3">
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
     * Shortcode: [login_logout_btn]
     * Tombol Login / Logout dengan ikon panah merah.
     *
     * @return string
     */
    public function login_logout_btn()
    {
        if (is_user_logged_in()) {
            $label = 'Logout';
            $url   = wp_logout_url(home_url());
        } else {
            $label = 'Login';
            $url   = wp_login_url(home_url());
        }

        // SVG panah keluar (merah) — hanya tampil saat Logout
        $arrow_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#dc3545" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M6 12.5a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v2a.5.5 0 0 1-1 0v-2A1.5 1.5 0 0 1 6.5 2h8A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-8A1.5 1.5 0 0 1 5 12.5v-2a.5.5 0 0 1 1 0v2z"/>
            <path fill-rule="evenodd" d="M.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L1.707 7.5H10.5a.5.5 0 0 1 0 1H1.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3z"/>
        </svg>';

        $icon = is_user_logged_in() ? '<span class="me-1">' . $arrow_svg . '</span>' : '';

        return sprintf(
            '<span class="login-logout-btn"><a href="%s">%s%s</a></span>',
            esc_url($url),
            $icon,
            esc_html($label)
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