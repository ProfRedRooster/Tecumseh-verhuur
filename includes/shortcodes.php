<?php
// Shortcode for the rental form with dynamic price display
function scouting_rentals_form_shortcode() {
    $field_toilets_price = get_option('field_toilets_price', 60);
    $field_toilets_kitchen_price = get_option('field_toilets_kitchen_price', 75);
    $field_toilets_kitchen_lokalen_price = get_option('field_toilets_kitchen_lokalen_price', 100);
    $wood_price = get_option('wood_price', 25);
    $scouting_discount = get_option('scouting_discount', 10);
    $today_date = date('Y-m-d');

    ob_start(); ?>
    <form id="scouting-rentals-form" method="POST">
        <!-- Customer Information -->
        <label for="name">Naam van organistie( of persoon:)</label>
        <input type="text" id="name" name="name" required><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>

        <!-- Date and Time -->
        <label for="start_date">Start Datum:</label>
          <!-- Set min attribute to today's date for start_date and add onchange event -->
        <input type="date" id="start_date" name="start_date" required onchange="calculatePrice(); updateEndDateMin();" min="<?php echo $today_date; ?>"><br>
        <label for="end_date">Einde datum:</label>
         <!-- Set min attribute to today's date for end_date -->
        <input type="date" id="end_date" name="end_date" required onchange="calculatePrice();" min="<?php echo $today_date; ?>"><br>

        <label for="start_period">Start dagdeel:</label>
        <select id="start_period" name="start_period" onchange="calculatePrice()">
            <option value="morning">Ochtend tot middag</option>
            <option value="evening">Middag tot avond</option>
        </select><br>

        <label for="end_period">Einde periode:</label>
        <select id="end_period" name="end_period" onchange="calculatePrice()">
            <option value="morning">Ochtend tot middag</option>
            <option value="evening">Middag tot avond</option>
        </select><br>

        <!-- Service Selection -->
        <label for="service">Waar wilt u gebruik van maken:</label>
        <select id="service" name="service" onchange="calculatePrice()">
            <option value="field_toilets">Veld + Toiletten</option>
            <option value="field_toilets_kitchen">Veld + Toiletten + Keuken</option>
            <option value="field_toilets_kitchen_lokalen">Veld + Toiletten + Keuken + Speltaklokalen</option>
        </select><br>

        <label for="number_of_people">Met hoeveel mensen ben je:</label>
        <select id="number_of_people" name="number_of_people" required onchange="calculatePrice()">
            <option value="<25">Minder dan 25</option>
            <option value="25-50">25 tot 50</option>
            <option value="50-100">50 tot 100</option>
            <option value="100+">meer dan 100</option>
        </select>

        <!-- Wood Option -->
        <label for="wood_included">Ook hout er bij? (10 euro per dagdeel)</label>
        <input type="checkbox" id="wood_included" name="wood_included" value="yes" onchange="calculatePrice()"><br>

        <!-- Scouting Related Checkbox -->
        <label for="related_scouting">Bent u aan scouting of Tecumseh gerelateerd?</label>
        <input type="checkbox" id="related_scouting" name="related_scouting" value="yes" onchange="calculatePrice()"><br>
        
        <label for="message">Heb je nog wat te zeggen:</label>
        <textarea id="message" name="message"></textarea>
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

    var startDate = new Date(document.getElementById('start_date').value);
    var endDate = new Date(document.getElementById('end_date').value);
    var startPeriod = document.getElementById('start_period').value;
    var endPeriod = document.getElementById('end_period').value;

    var timeDiff = endDate.getTime() - startDate.getTime();
    var days = timeDiff / (1000 * 3600 * 24);

    if (startPeriod === 'evening' && endPeriod === 'evening') {
        days += 0.5;
    }
    if (startPeriod === 'morning' && endPeriod === 'morning') {
        days += 0.5;
    }
    if (startPeriod === 'morning' && endPeriod === 'evening') {
        days += 1;
    }
    else {
        days += 0;
    }

    var service = document.getElementById('service').value;
    var woodIncluded = document.getElementById('wood_included').checked;
    var relatedScouting = document.getElementById('related_scouting').checked;
    var numberOfPeople = document.getElementById('number_of_people').value; // Get the selected number of people

    var price = 0;
    if (service == 'field_toilets') {
        price = fieldToiletsPrice;
    } else if (service == 'field_toilets_kitchen') {
        price = fieldToiletsKitchenPrice;
    } else if (service == 'field_toilets_kitchen_lokalen') {
        price = fieldToiletsKitchenLokalenPrice;
    }

    price *= days;

    if (woodIncluded) {
        price += woodPrice * days;
    }

    if (relatedScouting) {
        price -= (price * scoutingDiscount / 100);
    }

    // Adjust price based on the number of people
    switch (numberOfPeople) {
        case '<25':
            price *= 0.5;
            break;
        case '25-50':
            price *= 0.65; // Example adjustment
            break;
        case '50-100':
            price *= 0.8; // Example adjustment
            break;
        case '100+':
            price *= 1.0; // Example adjustment
            break;
    }

    document.getElementById('total_price').innerText = price.toFixed(2);
}


        window.onload = calculatePrice;

        function updateEndDateMin() {
    var startDate = document.getElementById('start_date').value;
    document.getElementById('end_date').setAttribute('min', startDate);
}
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('scouting_rentals_form', 'scouting_rentals_form_shortcode');
// Shortcode for displaying upcoming reservations

function scouting_upcoming_reservations_public() {
    global $wpdb;
    $table_name = $wpdb->prefix . "scouting_rentals";
    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE status = %s ORDER BY start_date ASC", 'approved');
    $results = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        error_log('Database error: ' . $wpdb->last_error);
        return '<p>There was an error retrieving the reservations.</p>';
    }
    if (empty($results)) {
        error_log('No results found for the query: ' . $query);
        return '<p>Geen (goedgekeurde) reservaties gevonden</p>';
    }
    ob_start();
    echo '<h2>Gereseveerde datums:</h2><ul>';
    foreach ($results as $row) {
        $start_date = esc_html($row->start_date);
        $end_date = esc_html($row->end_date);
        $start_period = esc_html($row->start_period); // Added start_period
        $end_period = esc_html($row->end_period); // Added end_period
        echo "<li>$start_date $start_period to $end_date $end_period</li>";
    }
    echo '</ul>';
    return ob_get_clean();
}
add_shortcode('scouting_upcoming_reservations_public', 'scouting_upcoming_reservations_public');

function scouting_upcoming_reservations() {
    global $wpdb;
    $table_name = $wpdb->prefix . "scouting_rentals";
    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE status = %s ORDER BY start_date ASC", 'approved');
    $results = $wpdb->get_results($query);
    if ($wpdb->last_error) {
        error_log('Database error: ' . $wpdb->last_error);
        return '<p>There was an error retrieving the reservations.</p>';
    }
    if (empty($results)) {
        error_log('No results found for the query: ' . $query);
        return '<p>Geen (goedgekeurde) reservaties gevonden</p>';
    }
    ob_start();
    echo '<h2>Gereseveerde datums:</h2><ul>';
    foreach ($results as $row) {
        $name = esc_html($row->name);
        $start_date = esc_html($row->start_date);
        $end_date = esc_html($row->end_date);
        $service = isset($row->service) ? esc_html($row->service) : 'N/A';
        $start_period = isset($row->start_period) ? esc_html($row->start_period) : 'N/A'; // Added start_period
        $end_period = isset($row->end_period) ? esc_html($row->end_period) : 'N/A'; // Added end_period
        echo "<li>$name - $start_date $start_period to $end_date $end_period ($service)</li>";
    }
    echo '</ul>';
    return ob_get_clean();
}
add_shortcode('scouting_upcoming_reservations', 'scouting_upcoming_reservations');
?>