<?php
// Add admin menu
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
add_action('admin_menu', 'scouting_rentals_admin_menu');

// Admin page content
function scouting_rentals_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'scouting_rentals';
    $results = $wpdb->get_results("SELECT * FROM $table_name");
    echo '<h1>Manage Rental Requests</h1>';
    echo '<table class="widefat fixed" cellspacing="0">';
    // Add headers for Start Period and End Period
    echo '<thead><tr><th>Name</th><th>Email</th><th>Start Date</th><th>End Date</th><th>Start Period</th><th>End Period</th><th>People</th><th>Price</th><th>Status</th><th>Service</th><th>Wood Included</th><th>Actions</th></tr></thead>';
    echo '<tbody>';
    foreach ($results as $row) {
        // Ensure properties exist before accessing them
        $name = esc_html($row->name);
        $email = esc_html($row->email);
        $start_date = esc_html($row->start_date);
        $end_date = esc_html($row->end_date);
        $start_period = isset($row->start_period) ? esc_html($row->start_period) : 'N/A'; // Fetch start_period
        $end_period = isset($row->end_period) ? esc_html($row->end_period) : 'N/A'; // Fetch end_period
        $number_of_people = isset($row->number_of_people) ? esc_html($row->number_of_people) : 'N/A';
        $total_price = isset($row->total_price) ? esc_html($row->total_price) : 'N/A';
        $status = isset($row->status) ? esc_html($row->status) : 'N/A';
        $service = isset($row->service) ? esc_html($row->service) : 'N/A';
        $wood_included = isset($row->wood_included) ? esc_html($row->wood_included) : 'N/A';
        echo '<tr>';
        echo '<td>' . $name . '</td>';
        echo '<td>' . $email . '</td>';
        echo '<td>' . $start_date . '</td>';
        echo '<td>' . $end_date . '</td>';
        echo '<td>' . $start_period . '</td>'; // Display start_period
        echo '<td>' . $end_period . '</td>'; // Display end_period
        echo '<td>' . $number_of_people . '</td>';
        echo '<td>' . $total_price . '</td>';
        echo '<td>' . $status . '</td>';
        echo '<td>' . $service . '</td>';
        echo '<td>' . $wood_included . '</td>';
        echo '<td>';
        echo '<a href="?page=scouting_rentals&approve=' . intval($row->id) . '">Approve</a> | ';
        echo '<a href="?page=scouting_rentals&reject=' . intval($row->id) . '">Reject</a> | ';
        echo '<a href="?page=scouting_rentals&delete=' . intval($row->id) . '">Delete</a>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}

// Handle approve, reject, delete
function handle_scouting_rentals_actions() {
    if (isset($_GET['approve'])) {
        global $wpdb;
        $id = intval($_GET['approve']);
        $wpdb->update(
            $wpdb->prefix . 'scouting_rentals',
            array('status' => 'approved'),
            array('id' => $id)
        );
    }

    if (isset($_GET['reject'])) {
        global $wpdb;
        $id = intval($_GET['reject']);
        $wpdb->update(
            $wpdb->prefix . 'scouting_rentals',
            array('status' => 'rejected'),
            array('id' => $id)
        );
    }

    if (isset($_GET['delete'])) {
        global $wpdb;
        $id = intval($_GET['delete']);
        $wpdb->delete(
            $wpdb->prefix . 'scouting_rentals',
            array('id' => $id)
        );
    }
}
add_action('admin_init', 'handle_scouting_rentals_actions');
?>