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
    register_setting('scouting_rentals_settings_group', 'onder25');
    register_setting('scouting_rentals_settings_group', '25tot50');
    register_setting('scouting_rentals_settings_group', '50tot100');
    register_setting('scouting_rentals_settings_group', '100plus');
}

// Create settings page
function scouting_rentals_settings_page() {
    ?>
    <div class="wrap">
        <h1>De verhuur instellingen</h1>
        <form method="post" action="options.php">
            <?php settings_fields('scouting_rentals_settings_group'); ?>
            <?php do_settings_sections('scouting_rentals_settings_group'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Price for Veld + Toiletten (Per Day)</th>
                    <td><input type="text" name="field_toilets_price" value="<?php echo esc_attr(get_option('field_toilets_price', '60')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Prijs voor Veld + Toiletten + Keuken (Per dag)</th>
                    <td><input type="text" name="field_toilets_kitchen_price" value="<?php echo esc_attr(get_option('field_toilets_kitchen_price', '75')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Prijs voor Veld + Toiletten + Keuken + Speltaklokalen (Per dag)</th>
                    <td><input type="text" name="field_toilets_kitchen_lokalen_price" value="<?php echo esc_attr(get_option('field_toilets_kitchen_lokalen_price', '100')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Scoutkorting (Percentage)</th>
                    <td><input type="text" name="scouting_discount" value="<?php echo esc_attr(get_option('scouting_discount', '10')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Prijs voor hout (Optional)</th>
                    <td><input type="text" name="wood_price" value="<?php echo esc_attr(get_option('wood_price', '25')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Factor voor minder dan 25 mensen</th>
                    <td><input type="text" name="onder25" value="<?php echo esc_attr(get_option('onder25', '0.50')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Factor voor 25 tot 50 mensen</th>
                    <td><input type="text" name="25tot50" value="<?php echo esc_attr(get_option('25tot50', '0.65')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Factor voor 50 tot 100 mensen</th>
                    <td><input type="text" name="50tot100" value="<?php echo esc_attr(get_option('50tot100', '0.8')); ?>" /></td>
                </tr>

                <tr valign="top">
                    <th scope="row">Factor voor meer dan 100 mensen</th>
                    <td><input type="text" name="100plus" value="<?php echo esc_attr(get_option('100plus', '1')); ?>" /></td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
?>