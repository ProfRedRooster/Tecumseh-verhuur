<?php
// Shortcode for the rental form with dynamic price display
function scouting_rentals_form_shortcode() {
    $field_toilets_price = get_option('field_toilets_price', 60);
    $field_toilets_kitchen_price = get_option('field_toilets_kitchen_price', 75);
    $field_toilets_kitchen_lokalen_price = get_option('field_toilets_kitchen_lokalen_price', 100);
    $wood_price = get_option('wood_price', 25);
    $scouting_discount = get_option('scouting_discount', 10);

    ob_start(); ?>
    <form id="scouting-rentals-form" method="POST">
        <!-- Customer Information -->
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>

        <!-- Date and Time -->
        <label for="start_date">Start Date:</label>
        <input type="date" id="start_date" name="start_date" required><br>

        <label for="end_date">End Date:</label>
        <input type="date" id="end_date" name="end_date" required><br>

        <label for="start_period">Start Period:</label>
        <select id="start_period" name="start_period">
            <option value="morning">Morning</option>
            <option value="evening">Evening</option>
        </select><br>

        <label for="end_period">End Period:</label>
        <select id="end_period" name="end_period">
            <option value="morning">Morning</option>
            <option value="evening">Evening</option>
        </select><br>

        <!-- Service Selection -->
        <label for="service">Choose Service:</label>
        <select id="service" name="service" onchange="calculatePrice()">
            <option value="field_toilets">Veld + Toiletten</option>
            <option value="field_toilets_kitchen">Veld + Toiletten + Keuken</option>
            <option value="field_toilets_kitchen_lokalen">Veld + Toiletten + Keuken + Speltaklokalen</option>
        </select><br>

        <!-- Wood Option -->
        <label for="wood">Include Wood:</label>
        <input type="checkbox" id="wood" name="wood" value="yes" onchange="calculatePrice()"><br>

        <!-- Scouting Related Checkbox -->
        <label for="related_scouting">Are you related to Scouting?</label>
        <input type="checkbox" id="related_scouting" name="related_scouting" value="yes" onchange="calculatePrice()"><br>

        <!-- Display the calculated total price -->
        <p>Total Price: €<span id="total_price">0.00</span></p>

        <input type="submit" name="submit_rental" value="Submit">
    </form>

    <script>
        function calculatePrice() {
            var fieldToiletsPrice = <?php echo $field_toilets_price; ?>;
            var fieldToiletsKitchenPrice = <?php echo $field_toilets_kitchen_price; ?>;
            var fieldToiletsKitchenLokalenPrice = <?php echo $field_toilets_kitchen_lokalen_price; ?>;
            var woodPrice = <?php echo $wood_price; ?>;
            var scoutingDiscount = <?php echo $scouting_discount; ?>;

            var service = document.getElementById('service').value;
            var woodIncluded = document.getElementById('wood').checked;
            var relatedScouting = document.getElementById('related_scouting').checked;

            var price = 0;
            if (service == 'field_toilets') {
                price = fieldToiletsPrice;
            } else if (service == 'field_toilets_kitchen') {
                price = fieldToiletsKitchenPrice;
            } else if (service == 'field_toilets_kitchen_lokalen') {
                price = fieldToiletsKitchenLokalenPrice;
            }

            if (woodIncluded) {
                price += woodPrice;
            }

            if (relatedScouting) {
                price -= (price * scoutingDiscount / 100);
            }

            document.getElementById('total_price').innerText = price.toFixed(2);
        }

        window.onload = calculatePrice;
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('scouting_rentals_form', 'scouting_rentals_form_shortcode');
// Shortcode for displaying upcoming reservations

function scouting_upcoming_reservations() {
    global $wpdb;
    $table_name = $wpdb->prefix . "scouting_rentals";

    // Prepare the SQL query
    $query = $wpdb->prepare(
        "SELECT * FROM $table_name WHERE status = %s ORDER BY start_date ASC",
        'approved'
    );
    $results = $wpdb->get_results($query);

    // Check for SQL errors
    if ($wpdb->last_error) {
        error_log('Database error: ' . $wpdb->last_error);
        return '<p>There was an error retrieving the reservations.</p>';
    }

    // Debug output
    if (empty($results)) {
        error_log('No results found for the query: ' . $query);
        return '<p>No upcoming reservations found.</p>';
    }

    // Log the structure of $results
    error_log(print_r($results, true));

    ob_start();
    echo '<h2>Upcoming Reservations</h2>';
    echo '<ul>';
    foreach ($results as $row) {
        // Check if the property exists
        $name = esc_html($row->name);
        $start_date = esc_html($row->start_date);
        $end_date = esc_html($row->end_date);
        $service = isset($row->service) ? esc_html($row->service) : 'N/A';

        echo "<li>$name - $start_date to $end_date ($service)</li>";
    }
    echo '</ul>';
    return ob_get_clean();
}
add_shortcode('scouting_upcoming_reservations', 'scouting_upcoming_reservations');

?>