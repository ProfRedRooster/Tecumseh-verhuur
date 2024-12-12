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
 * Version:           0.0.6
 * Author:            Rohan de Graaf
 * Author URI:        https://rohandg.nl/
 * Text Domain:       tecumseh_beheer
 * Requires PHP:      7.1
 * Requires at least: 5.2
*/

// Include necessary files
include_once plugin_dir_path(__FILE__) . 'includes/dompdf/autoload.inc.php';
include_once plugin_dir_path(__FILE__) . 'includes/form-handler.php';
include_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
include_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
include_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';

// Create the database table on activation
function scouting_rentals_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . "scouting_rentals";
    $charset_collate = $wpdb->get_charset_collate();
    $message_column = ", message text NOT NULL";
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        start_date date NOT NULL,
        end_date date NOT NULL,
        start_period enum('ochtend', 'avond') NOT NULL,
        end_period enum('ochtend', 'avond') NOT NULL,
        number_of_people enum('1 tot 25', '25 tot 50', '50 tot 100', '100 plus') NOT NULL,
        service enum('field_toilets', 'field_toilets_kitchen', 'field_toilets_kitchen_lokalen') NOT NULL,
        wood_included enum('yes', 'no') NOT NULL,
        total_price float NOT NULL,
        status enum('pending', 'approved', 'rejected') DEFAULT 'pending'
        $message_column,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    $table_name = $wpdb->prefix . 'scouting_rentals_disabled_dates';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        disabled_date date NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'scouting_rentals_install');
?>