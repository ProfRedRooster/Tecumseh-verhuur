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
    } else {
        $days += 1;
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
    $price *= $days;

    if ($wood_included === 'yes') {
        $price += $wood_price * $days;
    }

    if ($related_scouting === 'yes') {
        $price -= ($price * $scouting_discount / 100);
    }

    // Adjust price based on the number of people
    switch ($number_of_people) {
        case '<25':
            $price *= 0.3;
            break;
        case '25-50':
            $price *= 0.4;
            break;
        case '50-100':
            $price *= 0.7;
            break;
        case '100+':
            $price *= 1.0;
            break;
    }

    return $price;
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
                'total_price' => $total_price,
                'status' => 'pending'
            ),
            array(
                '%s', // name
                '%s', // email
                '%s', // start_date
                '%s', // end_date
                '%s', // start_period
                '%s', // end_period
                '%d', // number_of_people
                '%f', // total_price
                '%s'  // status
            )
        );
        wp_redirect(home_url('/home'));
        exit;
    }
}
add_action('wp', 'handle_scouting_rentals_submission');
?>