<?php
// Admin settings page to edit prices
function scouting_rentals_register_settings() {
    add_menu_page(
        'Scouting Rentals Settings',
        'Rental Settings',
        'manage_options',
        'scouting_rentals_settings',
        'scouting_rentals_settings_page',
        'dashicons-admin-settings'
    );
    add_action('admin_init', 'scouting_rentals_settings_init');
}

add_action('admin_menu', 'scouting_rentals_register_settings');

// Initialize settings
function scouting_rentals_settings_init() {
    register_setting('scouting_rentals_settings_group', 'field_toilets_price');
    register_setting('scouting_rentals_settings_group', 'field_toilets_kitchen_price');
    register_setting('scouting_rentals_settings_group', 'field_toilets_kitchen_lokalen_price');
    register_setting('scouting_rentals_settings_group', 'scouting_discount');
    register_setting('scouting_rentals_settings_group', 'wood_price');
}

// Create settings page
function scouting_rentals_settings_page() {
    ?>
    <div class="wrap">
        <h1>Scouting Rentals Price Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('scouting_rentals_settings_group'); ?>
            <?php do_settings_sections('scouting_rentals_settings_group'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Price for Veld + Toiletten (Per Day)</th>
                    <td><input type="text" name="field_toilets_price" value="<?php echo esc_attr(get_option('field_toilets_price', '60')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Price for Veld + Toiletten + Keuken (Per Day)</th>
                    <td><input type="text" name="field_toilets_kitchen_price" value="<?php echo esc_attr(get_option('field_toilets_kitchen_price', '75')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Price for Veld + Toiletten + Keuken + Speltaklokalen (Per Day)</th>
                    <td><input type="text" name="field_toilets_kitchen_lokalen_price" value="<?php echo esc_attr(get_option('field_toilets_kitchen_lokalen_price', '100')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Discount for Scouting-Related Users (Percentage)</th>
                    <td><input type="text" name="scouting_discount" value="<?php echo esc_attr(get_option('scouting_discount', '10')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Price for Wood (Optional)</th>
                    <td><input type="text" name="wood_price" value="<?php echo esc_attr(get_option('wood_price', '25')); ?>" /></td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
?>