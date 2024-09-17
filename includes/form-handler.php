<?php
function calculate_total_price($number_of_people, $service, $wood_included, $related_scouting) {
 // Price structure based on provided sheet
    $field_toilets_price = get_option('field_toilets_price', 60);
    $field_toilets_kitchen_price = get_option('field_toilets_kitchen_price', 75);
    $field_toilets_kitchen_lokalen_price = get_option('field_toilets_kitchen_lokalen_price', 100);
    $wood_price = get_option('wood_price', 25);
    $scouting_discount = get_option('scouting_discount', 10);

    $price = 0;
    if ($service == 'field_toilets') {
        $price = $field_toilets_price;
    } elseif ($service == 'field_toilets_kitchen') {
        $price = $field_toilets_kitchen_price;
    } elseif ($service == 'field_toilets_kitchen_lokalen') {
        $price = $field_toilets_kitchen_lokalen_price;
    }

    if ($wood_included === 'yes') {
        $price += $wood_price;
    }

    if ($related_scouting === 'yes') {
        $price -= ($price * $scouting_discount / 100);
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
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $start_period = isset($_POST['start_period']) ? sanitize_text_field($_POST['start_period']) : '';
        $end_period = isset($_POST['end_period']) ? sanitize_text_field($_POST['end_period']) : '';
        $number_of_people = isset($_POST['number_of_people']) ? intval($_POST['number_of_people']) : 0;
        $service = isset($_POST['service']) ? sanitize_text_field($_POST['service']) : '';
        $wood_included = isset($_POST['wood']) ? sanitize_text_field($_POST['wood']) : 'no';
        $related_scouting = isset($_POST['related_scouting']) ? sanitize_text_field($_POST['related_scouting']) : 'no';

        // Calculate the total price
        $total_price = calculate_total_price($number_of_people, $service, $wood_included, $related_scouting);

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
            )
        );
        wp_redirect(home_url('/home'));
    }
}
add_action('wp', 'handle_scouting_rentals_submission');
?>