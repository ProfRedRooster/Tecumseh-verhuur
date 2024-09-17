<?php
/*
 * @link              https://url
 * @since             0.0.1
 * @package           Tecumseh_Verhuur
 *
 * @wordpress-plugin
 * Plugin Name:       Tecumseh Verhuur
 * Plugin URI:        https://github.com/ProfRedRooster/Tecumseh-verhuur/
 * Description:       Administratie verhuur en verhuur formulieren
 * Version:           0.0.2
 * Author:            Rohan de Graaf
 * Author URI:        https://rohandg.nl/
 * Text Domain:       tecumseh_beheer
*/

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';
include_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
include_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
include_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';


// Create the database table on activation
function scouting_rentals_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . "scouting_rentals";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        start_date date NOT NULL,
        end_date date NOT NULL,
        start_period enum('morning', 'evening') NOT NULL,
        end_period enum('morning', 'evening') NOT NULL,
        number_of_people enum('<25', '25-50', '50-100', '100+') NOT NULL,
        service enum('field_toilets', 'field_toilets_kitchen', 'field_toilets_kitchen_lokalen') NOT NULL,
        total_price float NOT NULL,
        status enum('pending', 'approved', 'rejected') DEFAULT 'pending',
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'scouting_rentals_install');
?>