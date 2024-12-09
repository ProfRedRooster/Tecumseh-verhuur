<?php
add_action('admin_enqueue_scripts', 'scouting_rentals_admin_styles');
function scouting_rentals_admin_styles() {
    wp_enqueue_style('scouting-rentals-admin-css', plugin_dir_url(__FILE__) . '/css/admin-style.css');
}

add_action('admin_menu', 'scouting_rentals_admin_menu');
function scouting_rentals_admin_menu() {
    add_menu_page(
        'Scouting Rentals', 
        'Rentals', 
        'manage_options', 
        'scouting_rentals', 
        'scouting_rentals_admin_page', 
        'dashicons-admin-generic'
    );
}

function scouting_rentals_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'scouting_rentals';

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_rentals'])) {
        foreach ($_POST['id'] as $index => $id) {
            $name = sanitize_text_field($_POST['name'][$index]);
            $email = sanitize_email($_POST['email'][$index]);
            $start_date = sanitize_text_field($_POST['start_date'][$index]);
            $end_date = sanitize_text_field($_POST['end_date'][$index]);
            $start_period = sanitize_text_field($_POST['start_period'][$index]);
            $end_period = sanitize_text_field($_POST['end_period'][$index]);
            $number_of_people = sanitize_text_field($_POST['number_of_people'][$index]);
            $total_price = floatval($_POST['total_price'][$index]);
            $status = sanitize_text_field($_POST['status'][$index]);
            $service = sanitize_text_field($_POST['service'][$index]);
            $wood_included = sanitize_text_field($_POST['wood_included'][$index]);
            $message = sanitize_text_field($_POST['message'][$index]);

            $wpdb->update(
                $table_name,
                [
                    'name' => $name,
                    'email' => $email,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'start_period' => $start_period,
                    'end_period' => $end_period,
                    'number_of_people' => $number_of_people,
                    'total_price' => $total_price,
                    'status' => $status,
                    'service' => $service,
                    'wood_included' => $wood_included,
                    'message' => $message
                ],
                ['id' => $id]
            );
        }
    }

    $results = $wpdb->get_results("SELECT * FROM $table_name");
    echo '<div class="scouting-rentals-admin">';
    echo '<h1>Beheer verhuur aanvragen</h1>';
    echo '<form method="post" action="">';
    echo '<input type="submit" name="update_rentals" value="Update gegevens">';
    foreach ($results as $row) {
        $id = esc_html($row->id);
        $name = esc_html($row->name);
        $email = esc_html($row->email);
        $start_date = esc_html($row->start_date);
        $end_date = esc_html($row->end_date);
        $start_period = isset($row->start_period) ? esc_html($row->start_period) : 'N/A';
        $end_period = isset($row->end_period) ? esc_html($row->end_period) : 'N/A';
        $number_of_people = esc_html($row->number_of_people);
        $total_price = isset($row->total_price) ? esc_html($row->total_price) : 'N/A';
        $status = isset($row->status) ? esc_html($row->status) : 'N/A';
        $service = isset($row->service) ? esc_html($row->service) : 'N/A';
        $wood_included = isset($row->wood_included) ? esc_html($row->wood_included) : 'N/A';
        $message = esc_html($row->message);

        echo '<div class="rental-item">';
        echo '<input type="hidden" name="id[]" value="' . $id . '">';
        echo '<div><label>Naam</label><input type="text" name="name[]" value="' . $name . '"></div>';
        echo '<div><label>Email</label><input type="email" name="email[]" value="' . $email . '"></div>';
        echo '<div><label>Begin datum</label><input type="date" name="start_date[]" value="' . $start_date . '"></div>';
        echo '<div><label>End datum</label><input type="date" name="end_date[]" value="' . $end_date . '"></div>';
        echo '<div><label>Begin dagdeel</label><input type="text" name="start_period[]" value="' . $start_period . '"></div>';
        echo '<div><label>Eind dagdeel</label><input type="text" name="end_period[]" value="' . $end_period . '"></div>';
        echo '<div><label>Hoeveel mensen</label><input type="text" name="number_of_people[]" value="' . $number_of_people . '"></div>';
        echo '<div><label>Prijs</label><input type="text" name="total_price[]" value="' . $total_price . '"></div>';
        echo '<div><label>Wat huren ze</label><input type="text" name="service[]" value="' . $service . '"></div>';
        echo '<div><label>Hout er bij?</label><input type="text" name="wood_included[]" value="' . $wood_included . '"></div>';
        echo '<div><label>Bericht</label><input type="text" name="message[]" value="' . $message . '"></div>';
        echo '<div><label>Status</label><input type="text" name="status[]" value="' . $status . '"></div>';
        echo '<div><a href="?page=scouting_rentals&approve=' . intval($row->id) . '">Approve</a> | ';
        echo '<a href="?page=scouting_rentals&reject=' . intval($row->id) . '">Reject</a> | ';
        echo '<a href="?page=scouting_rentals&delete=' . intval($row->id) . '">Delete</a> | ';
        echo '<a href="?page=scouting_rentals&factuur=' . intval($row->id) . '">Maak factuur</a> | ';
        echo '<a href="?page=scouting_rentals&contract=' . intval($row->id) . '">Maak contract</a></div>';
        echo '</div>';
    }

    echo '</form>';
    echo '</div>';
}

add_action('admin_init', 'handle_scouting_rentals_actions');
function handle_scouting_rentals_actions() {
    global $wpdb;
    if (isset($_GET['approve'])) {
        $id = intval($_GET['approve']);
        $wpdb->update(
            $wpdb->prefix . 'scouting_rentals',
            array('status' => 'approved'),
            array('id' => $id)
        );
    }
    if (isset($_GET['reject'])) {
        $id = intval($_GET['reject']);
        $wpdb->update(
            $wpdb->prefix . 'scouting_rentals',
            array('status' => 'rejected'),
            array('id' => $id)
        );
    }
    if (isset($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $wpdb->delete(
            $wpdb->prefix . 'scouting_rentals',
            array('id' => $id)
        );
    }
    if (isset($_GET['factuur'])) {
        $id = intval($_GET['factuur']);
        echo '<div class="notice notice-success is-dismissible"><p>Factuur voor ID ' . $id . ' wordt aangemaakt aangemaakt (moet nog gemaakt worden).</p></div>';
    }
    if (isset($_GET['contract'])) {
        $id = intval($_GET['contract']);
        echo '<div class="notice notice-success is-dismissible"><p>Contract voor ID ' . $id . ' wordt aangemaakt aangemaakt (moet nog gemaakt worden).</p></div>';
    }
}