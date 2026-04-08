<?php

namespace CustomPlugin\Admin;

if (!defined('ABSPATH')) {
    exit;
}

class UserFields
{
    public function __construct()
    {
        // Add field to profile edit screen
        add_action('show_user_profile', array($this, 'render_nis_field'));
        add_action('edit_user_profile', array($this, 'render_nis_field'));

        // Add field to "Add New User" screen
        add_action('user_new_form', array($this, 'render_nis_field_new'));

        // Save field
        add_action('personal_options_update', array($this, 'save_nis_field'));
        add_action('edit_user_profile_update', array($this, 'save_nis_field'));
        add_action('user_register', array($this, 'save_nis_field'));

        // Add column to users list
        add_filter('manage_users_columns', array($this, 'add_nis_column'));
        add_filter('manage_users_custom_column', array($this, 'render_nis_column'), 10, 3);

        // Allow login with NIS
        add_filter('authenticate', array($this, 'allow_nis_login'), 20, 3);
    }

    /**
     * Render NIS field on profile edit screen
     */
    public function render_nis_field($user)
    {
        $nis = get_user_meta($user->ID, '_nis', true);
?>
        <h3>Informasi Siswa</h3>
        <table class="form-table">
            <tr>
                <th><label for="nis">NIS (Nomor Induk Siswa)</label></th>
                <td>
                    <input type="text" name="nis" id="nis" value="<?php echo esc_attr($nis); ?>" class="regular-text" />
                    <p class="description">Masukkan NIS siswa. Siswa dapat login menggunakan nomor ini.</p>
                </td>
            </tr>
        </table>
    <?php
    }

    /**
     * Render NIS field on "Add New User" screen
     */
    public function render_nis_field_new($type)
    {
        if ($type !== 'add-new-user') return;
    ?>
        <table class="form-table">
            <tr>
                <th><label for="nis">NIS (Nomor Induk Siswa)</label></th>
                <td>
                    <input type="text" name="nis" id="nis" value="" class="regular-text" />
                    <p class="description">Siswa dapat login menggunakan nomor ini.</p>
                </td>
            </tr>
        </table>
<?php
    }

    /**
     * Save NIS field
     */
    public function save_nis_field($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        if (isset($_POST['nis'])) {
            update_user_meta($user_id, '_nis', sanitize_text_field($_POST['nis']));
        }
    }

    /**
     * Add NIS column to users list
     */
    public function add_nis_column($columns)
    {
        $columns['nis'] = 'NIS';
        return $columns;
    }

    /**
     * Render NIS column in users list
     */
    public function render_nis_column($output, $column_name, $user_id)
    {
        if ($column_name === 'nis') {
            $nis = get_user_meta($user_id, '_nis', true);
            return $nis ? '<strong>' . esc_html($nis) . '</strong>' : '-';
        }
        return $output;
    }

    /**
     * Allow login with NIS
     */
    public function allow_nis_login($user, $username, $password)
    {
        // If already authenticated or no input, bail
        if ($user instanceof \WP_User || empty($username) || empty($password)) {
            return $user;
        }

        // Try to find user by NIS meta
        $users = get_users(array(
            'meta_key'   => '_nis',
            'meta_value' => $username,
            'number'     => 1,
            'count_total' => false,
        ));

        if (!empty($users)) {
            $user_obj = $users[0];
            // Verify password
            if (wp_check_password($password, $user_obj->user_pass, $user_obj->ID)) {
                return $user_obj;
            }
        }

        return $user;
    }
}
