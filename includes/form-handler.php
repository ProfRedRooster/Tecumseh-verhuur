<?php
function calculate_total_price($number_of_people, $service, $wood_included, $related_scouting, $start_date, $end_date, $start_period, $end_period) {
    // Retrieve prices from settings
    $field_toilets_price = get_option('field_toilets_price', 60);
    $field_toilets_kitchen_price = get_option('field_toilets_kitchen_price', 75);
    $field_toilets_kitchen_lokalen_price = get_option('field_toilets_kitchen_lokalen_price', 100);
    $wood_price = get_option('wood_price', 25);
    $scouting_discount = get_option('scouting_discount', 10);
    // Calculate the difference in days
    $startDateTime = new DateTime($start_date);
    $endDateTime = new DateTime($end_date);
    $diff = $startDateTime->diff($endDateTime);
    $days = $diff->days;
    if ($start_period === 'evening' && $end_period === 'evening') {
        $days += 0.5;
    } elseif ($start_period === 'morning' && $end_period === 'morning') {
        $days += 0.5;
    } elseif ($start_period === 'morning' && $end_period === 'evening') {
        $days += 1;
    } else {
        $days += 0;
    }
    // Determine base price based on service
    $price = 0;
    switch ($service) {
        case 'field_toilets':
            $price = $field_toilets_price;
            break;
        case 'field_toilets_kitchen':
            $price = $field_toilets_kitchen_price;
            break;
        case 'field_toilets_kitchen_lokalen':
            $price = $field_toilets_kitchen_lokalen_price;
            break;
    }
    // Adjust price based on the number of people
    switch ($number_of_people) {
        case '<25':
            $price *= 0.5;
            break;
        case '25-50':
            $price *= 0.65;
            break;
        case '50-100':
            $price *= 0.8;
            break;
        case '100+':
            $price *= 1.0;
            break;
    }

    $price *= $days;
    if ($wood_included === 'yes') {
        $price += $wood_price;
    }
    if ($related_scouting === 'yes') {
        $price -= ($price * $scouting_discount / 100);
    }

    return $price;
}

// Function to check availability
function is_reservation_available($start_date, $end_date, $start_period, $end_period) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'scouting_rentals';

    // Query to check for overlapping dates and day parts
    $query = $wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name 
        WHERE NOT (end_date < %s OR start_date > %s) 
        AND (start_period = %s OR end_period = %s OR start_period = %s OR end_period = %s)",
        $start_date,
        $end_date,
        $start_period,
        $start_period,
        $end_period,
        $end_period
    );
    $count = $wpdb->get_var($query);

    // If count is 0, no overlapping reservations with the same day part, return true
    return ($count == 0);
}

// Handle form submission
function handle_scouting_rentals_submission() {
    if (isset($_POST['submit_rental'])) {
        global $wpdb;
        $table_name = $wpdb->prefix . "scouting_rentals";
        // Sanitize input and provide defaults if not set
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $number_of_people = $_POST['number_of_people'] ?? '';
        $service = isset($_POST['service']) ? sanitize_text_field($_POST['service']) : '';
        $wood_included = isset($_POST['wood_included']) && $_POST['wood_included'] === 'yes' ? 'yes' : 'no'; // Default to 'no' if not set
        $related_scouting = isset($_POST['related_scouting']) && $_POST['related_scouting'] === 'yes' ? 'yes' : 'no'; // Default to 'no' if not set
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $start_period = isset($_POST['start_period']) ? sanitize_text_field($_POST['start_period']) : '';
        $end_period = isset($_POST['end_period']) ? sanitize_text_field($_POST['end_period']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        // Before inserting a new reservation, check availability
        if (is_reservation_available($start_date, $end_date, $start_period, $end_period)) {
            // Calculate the total price
            $total_price = calculate_total_price($number_of_people, $service, $wood_included, $related_scouting, $start_date, $end_date, $start_period, $end_period);
            // Insert into the database
            $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'email' => $email,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'start_period' => $start_period,
                    'end_period' => $end_period,
                    'number_of_people' => $number_of_people,
                    'service' => $service,
                    'wood_included' => $wood_included,
                    'total_price' => $total_price,
                    'message' => $message,
                    'status' => 'pending'
                ),
                array(
                    '%s', // name
                    '%s', // email
                    '%s', // start_date
                    '%s', // end_date
                    '%s', // start_period
                    '%s', // end_period
                    '%s', // number_of_people
                    '%s', // service
                    '%s', // wood_included
                    '%f', // total_price
                    '%s', // message
                    '%s'  // status
                )
            );
            wp_redirect(home_url('/home'));
            exit;
        } else {
            // Inform the user that the requested dates are not available
            wp_redirect(home_url('/Datumkanniet'));
        }
    }
}
add_action('wp', 'handle_scouting_rentals_submission');
?>