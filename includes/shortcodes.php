<?php
function enqueue_scouting_rentals_css_js() {
    // Enqueue jQuery UI and styles
    wp_enqueue_style('jquery-ui-css', '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    wp_enqueue_script('jquery-ui-datepicker');
    wp_enqueue_style('scouting-rentals-css', plugins_url('/css/form-style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'enqueue_scouting_rentals_css_js');

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
        <label for="name">Naam van organisatie of persoon:</label>
        <input type="text" id="name" name="name" required><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br>

        <!-- Date and Time -->
        <label for="start_date">Start Datum:</label>
        <input type="text" id="start_date" name="start_date" required onchange="calculatePrice()"><br>

        <label for="end_date">Eind datum:</label>
        <input type="text" id="end_date" name="end_date" required onchange="calculatePrice()"><br>

        <label for="start_period">Start dagdeel:</label>
        <p>Kies 2x middag voor een enkele avond</p>
        <select id="start_period" name="start_period" onchange="calculatePrice()">
            <option value="ochtend">Ochtend tot middag</option>
            <option value="avond">Middag tot avond</option>
        </select><br>

        <label for="end_period">Eind dagdeel:</label>
        <select id="end_period" name="end_period" onchange="calculatePrice()">
            <option value="ochtend">Ochtend tot middag</option>
            <option value="avond">Middag tot avond</option>
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
            <option value="1 tot 25">Minder dan 25</option>
            <option value="25 tot 50">25 tot 50</option>
            <option value="50 tot 100">50 tot 100</option>
            <option value="100 plus">Meer dan 100</option>
        </select><br>

        <!-- Wood Option -->
        <label for="wood_included">Ook hout erbij?</label>
        <input type="checkbox" id="wood_included" name="wood_included" value="yes" onchange="calculatePrice()"><br>

        <!-- Scouting Related Checkbox -->
        <label for="related_scouting">Bent u aan scouting of Tecumseh gerelateerd?</label>
        <input type="checkbox" id="related_scouting" name="related_scouting" value="yes" onchange="calculatePrice()"><br>

        <label for="message">Heb je nog wat te zeggen:</label>
        <textarea id="message" name="message"></textarea><br>

        <!-- Display the calculated total price -->
        <p>Prijs: €<span id="total_price">0.00</span></p>

        <input type="submit" name="submit_rental" value="Submit">
    </form>

    <script>
    var reservedDates = [];

    // Fetch the reserved dates from the server
    fetch("<?php echo admin_url('admin-ajax.php?action=get_reserved_dates'); ?>")
        .then(response => response.json())
        .then(data => {
            reservedDates = data;
            initializeDatePicker(); // Initialize the date picker after the data is loaded
        });

    function initializeDatePicker() {
        var today = new Date().toISOString().split('T')[0]; // Get today's date

        $("#start_date").datepicker({
            dateFormat: "yy-mm-dd",
            beforeShowDay: function (date) {
                var dateString = $.datepicker.formatDate('yy-mm-dd', date);

                // Disable reserved dates
                if (reservedDates.indexOf(dateString) !== -1) {
                    return [false, 'reserved-date', 'Reserved']; // Return false to disable
                }

                // Available dates
                return [true, 'available-date', 'Available'];
            },
            minDate: today,
            onSelect: function(selectedDate) {
                $("#end_date").datepicker("option", "minDate", selectedDate);
            }
        });

        $("#end_date").datepicker({
            dateFormat: "yy-mm-dd",
            beforeShowDay: function (date) {
                var dateString = $.datepicker.formatDate('yy-mm-dd', date);

                // Disable reserved dates
                if (reservedDates.indexOf(dateString) !== -1) {
                    return [false, 'reserved-date', 'Reserved']; // Return false to disable
                }

                // Available dates
                return [true, 'available-date', 'Available'];
            },
            minDate: today
        });
    }

function calculatePrice() {
    var startDate = document.getElementById('start_date').value;
    var endDate = document.getElementById('end_date').value;
    var startPeriod = document.getElementById('start_period').value;
    var endPeriod = document.getElementById('end_period').value;
    var service = document.getElementById('service').value;
    var woodIncluded = document.getElementById('wood_included').checked ? 'yes' : 'no';
    var relatedScouting = document.getElementById('related_scouting').checked ? 'yes' : 'no';
    var numberOfPeople = document.getElementById('number_of_people').value;

    var data = {
        action: 'calculate_price',
        start_date: startDate,
        end_date: endDate,
        start_period: startPeriod,
        end_period: endPeriod,
        service: service,
        wood_included: woodIncluded,
        related_scouting: relatedScouting,
        number_of_people: numberOfPeople
    };

    jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data, function(response) {
        if (response.success) {
            document.getElementById('total_price').innerText = response.data.price.toFixed(2);
        } else {
            alert('Error calculating price: ' + response.data);
        }
    });
}

window.onload = calculatePrice;
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('scouting_rentals_form', 'scouting_rentals_form_shortcode');

// Output reserved dates in JSON for AJAX call
function get_reserved_dates() {
    global $wpdb;
    $table_name = $wpdb->prefix . "scouting_rentals";
    $disabled_table = $wpdb->prefix . 'scouting_rentals_disabled_dates';

    // Get approved reservations
    $query = $wpdb->prepare("SELECT start_date, end_date FROM $table_name WHERE status = %s", 'approved');
    $results = $wpdb->get_results($query);

    // Get disabled dates
    $disabled_dates = $wpdb->get_col("SELECT disabled_date FROM $disabled_table");

    if ($wpdb->last_error) {
        echo json_encode([]);
        wp_die();
    }

    $reserved_dates = [];

    // Add reserved dates from approved reservations
    foreach ($results as $row) {
        $current_date = strtotime($row->start_date);
        $end_date = strtotime($row->end_date);
        while ($current_date <= $end_date) {
            $reserved_dates[] = date('Y-m-d', $current_date);
            $current_date = strtotime('+1 day', $current_date);
        }
    }

    // Add disabled dates
    foreach ($disabled_dates as $date) {
        $reserved_dates[] = $date;
    }

    // Remove duplicates
    $reserved_dates = array_unique($reserved_dates);

    echo json_encode(array_values($reserved_dates));
    wp_die(); // Required to terminate properly
}
add_action('wp_ajax_get_reserved_dates', 'get_reserved_dates');
add_action('wp_ajax_nopriv_get_reserved_dates', 'get_reserved_dates');

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
    ?>
       <div id="scouting-rentals-calendar"></div>

<script>
var reservedDates = [];

// Fetch the reserved dates from the server
fetch("<?php echo admin_url('admin-ajax.php?action=get_reserved_dates'); ?>")
    .then(response => response.json())
    .then(data => {
        reservedDates = data;
        initializeCalendar(); // Initialize the calendar after the data is loaded
    });

    function initializeCalendar() {
    var today = new Date().toISOString().split('T')[0]; // Get today's date

    $("#scouting-rentals-calendar").datepicker({
        dateFormat: "yy-mm-dd",
        beforeShowDay: function (date) {
            var dateString = $.datepicker.formatDate('yy-mm-dd', date);

            // Disable reserved dates
            if (reservedDates.indexOf(dateString) !== -1) {
                return [false, 'reserved-date', 'Reserved']; // Return false to disable and apply CSS class
            }

            // Available dates
            return [true, 'available-date', 'Available'];
        },
        minDate: today
    });
}
</script>
    <?php
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
        $start_period = isset($row->start_period) ? esc_html($row->start_period) : 'N/A'; 
        $end_period = isset($row->end_period) ? esc_html($row->end_period) : 'N/A'; 
        echo "<li>$name - $start_date $start_period to $end_date $end_period ($service)</li>";
    }
    echo '</ul>';
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_disabled_date'])) {
        $disabled_date = sanitize_text_field($_POST['disabled_date']);
        $table_name = $wpdb->prefix . 'scouting_rentals_disabled_dates';
        $wpdb->insert($table_name, ['disabled_date' => $disabled_date]);
    }
    
    // Handle form submission to remove disabled dates
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_disabled_date'])) {
        $reenable_date = sanitize_text_field($_POST['reenable_date']);
        $table_name = $wpdb->prefix . 'scouting_rentals_disabled_dates';
        $wpdb->delete($table_name, ['disabled_date' => $reenable_date]);
    }
    
    $table_name = $wpdb->prefix . 'scouting_rentals_disabled_dates';
    $disabled_dates = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    
    <h2>Geblockeerde verhuur datums:</h2>
<!-- Form to add disabled date -->
<form method="post">
    <label for="disabled_date">Selecteer datum om te blokkeren:</label>
    <input type="text" id="disable_datepicker" name="disabled_date" required>
    <input type="submit" name="add_disabled_date" value="Blokkeer datum">
</form>

<!-- Form to re-enable date -->
<form method="post">
    <label for="reenable_date">Selecteer datum om te deblokkeren:</label>
    <input type="text" id="reenable_datepicker" name="reenable_date" required>
    <input type="submit" name="remove_disabled_date" value="Deblokkeer datum">
</form>

<script>
    var reservedDates = [];
    var disabledDates = [];

    // Fetch reserved dates from the server
    fetch("<?php echo admin_url('admin-ajax.php?action=get_reserved_dates'); ?>")
        .then(response => response.json())
        .then(data => {
            reservedDates = data;
            initializeDatepickers();
        });

    // Fetch disabled dates from the server
    fetch("<?php echo admin_url('admin-ajax.php?action=get_disabled_dates'); ?>")
        .then(response => response.json())
        .then(data => {
            disabledDates = data;
            initializeDatepickers();
        });

    function initializeDatepickers() {
        // Initialize datepicker for disabling dates
        $("#disable_datepicker").datepicker({
            dateFormat: "yy-mm-dd",
            beforeShowDay: function(date) {
                var dateString = $.datepicker.formatDate('yy-mm-dd', date);
                // Exclude already disabled or reserved dates
                if (reservedDates.indexOf(dateString) !== -1 || disabledDates.indexOf(dateString) !== -1) {
                    return [false];
                }
                return [true];
            }
        });

        // Initialize datepicker for re-enabling dates
        $("#reenable_datepicker").datepicker({
            dateFormat: "yy-mm-dd",
            beforeShowDay: function(date) {
                var dateString = $.datepicker.formatDate('yy-mm-dd', date);
                // Only allow selection of disabled dates
                if (disabledDates.indexOf(dateString) !== -1) {
                    return [true];
                }
                return [false];
            }
        });
    }
</script>
    <?php
    return ob_get_clean();
}
add_shortcode('scouting_upcoming_reservations', 'scouting_upcoming_reservations');
add_action('wp_ajax_get_disabled_dates', 'get_disabled_dates');
add_action('wp_ajax_nopriv_get_disabled_dates', 'get_disabled_dates');
function get_disabled_dates() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'scouting_rentals_disabled_dates';
    $results = $wpdb->get_col("SELECT disabled_date FROM $table_name");
    wp_send_json($results);
}
?>