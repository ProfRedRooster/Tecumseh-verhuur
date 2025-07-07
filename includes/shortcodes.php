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
        <div class="legend">
            <div class="legend-item"><span class="legend-color available"></span><span>Beschikbaar</span></div>
            <div class="legend-item"><span class="legend-color reserved"></span><span>Hele dag gereserveerd</span></div>
            <div class="legend-item"><span class="legend-color morning"></span><span>Ochtend gereserveerd</span></div>
            <div class="legend-item"><span class="legend-color evening"></span><span>Avond gereserveerd</span></div>
        </div>
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
    var reservedDates = {};

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

                // Determine reservation status
                var info = reservedDates[dateString];
                if (info === 'both') {
                    return [false, 'reserved-date', 'Hele dag gereserveerd'];
                } else if (info === 'morning') {
                    return [true, 'morning-reserved', 'Ochtend gereserveerd'];
                } else if (info === 'evening') {
                    return [true, 'evening-reserved', 'Avond gereserveerd'];
                }
                return [true, 'available-date', 'Beschikbaar'];
            },
            minDate: today,
            onSelect: function(selectedDate) {
                $("#end_date").datepicker("option", "minDate", selectedDate);
                updatePeriodOptions('start_period', selectedDate);
                // if only morning is open (evening reserved), restrict to single-day booking
                var info = reservedDates[selectedDate] || '';
                if (info === 'evening') {
                    $("#end_date").datepicker("option", { maxDate: selectedDate });
                    $("#end_date").val(selectedDate);
                } else {
                    // allow booking up to next reservation date unless fully booked
                    var futureDates = Object.keys(reservedDates).filter(function(d) { return d > selectedDate; }).sort();
                    if (futureDates.length > 0) {
                        var nextDateStr = futureDates[0];
                        var nextInfo = reservedDates[nextDateStr] || '';
                        var dt = new Date(nextDateStr);
                        // if fully booked or morning-reserved, treat as full-day block
                        if (nextInfo === 'both' || nextInfo === 'morning') {
                            dt.setDate(dt.getDate() - 1);
                        }
                        // evening-reserved allows booking that morning period (dt stays on nextDate)
                        $("#end_date").datepicker("option", "maxDate", dt);
                    } else {
                        $("#end_date").datepicker("option", "maxDate", null);
                    }
                }
            }
        });

        $("#end_date").datepicker({
            dateFormat: "yy-mm-dd",
            beforeShowDay: function (date) {
                var dateString = $.datepicker.formatDate('yy-mm-dd', date);

                // Determine reservation status
                var info = reservedDates[dateString];
                if (info === 'both') {
                    return [false, 'reserved-date', 'Hele dag gereserveerd'];
                } else if (info === 'morning') {
                    return [true, 'morning-reserved', 'Ochtend gereserveerd'];
                } else if (info === 'evening') {
                    return [true, 'evening-reserved', 'Avond gereserveerd'];
                }
                return [true, 'available-date', 'Beschikbaar'];
            },
            minDate: today,
            onSelect: function(selectedDate) {
                updatePeriodOptions('end_period', selectedDate);
            }
        });

        // Utility to disable period options based on reservation status
        function updatePeriodOptions(selectId, dateString) {
            var info = reservedDates[dateString] || '';
            var $sel = $('#' + selectId);
            // enable all first
            $sel.find('option').prop('disabled', false);
            if (info === 'morning') {
                // disable morning if already booked
                $sel.find('option[value="ochtend"]').prop('disabled', true);
                // always switch to evening when morning is unavailable
                $sel.val('avond');
            } else if (info === 'evening') {
                // disable evening if already booked
                $sel.find('option[value="avond"]').prop('disabled', true);
                // always switch to morning when evening is unavailable
                $sel.val('ochtend');
            } else if (info === 'both') {
                // fully booked -> disable all
                $sel.find('option').prop('disabled', true);
            }
        }

        // Attach update on date change to disable appropriate period options
        $('#start_date').on('change', function() {
            updatePeriodOptions('start_period', $(this).val());
        });
        $('#end_date').on('change', function() {
            updatePeriodOptions('end_period', $(this).val());
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
    $query = $wpdb->prepare("SELECT start_date, end_date, start_period, end_period FROM $table_name WHERE status = %s", 'approved');
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
        $start = $row->start_date;
        $end   = $row->end_date;
        $start_period = $row->start_period;
        $end_period   = $row->end_period;
        $date = $start;
        while ($date <= $end) {
            if ($start === $end) {
                // Single-day booking
                if ($start_period === $end_period) {
                    $info = $start_period === 'ochtend' ? 'morning' : 'evening';
                } else {
                    $info = 'both';
                }
            } else {
                // Multi-day booking covers full days
                $info = 'both';
            }
            // Merge if multiple bookings affect same day
            if (isset($reserved_dates[$date]) && $reserved_dates[$date] !== $info) {
                $reserved_dates[$date] = 'both';
            } else {
                $reserved_dates[$date] = $info;
            }
            $date = date('Y-m-d', strtotime('+1 day', strtotime($date)));
        }
    }
    // Add disabled dates or periods
    $disabled_rows = $wpdb->get_results("SELECT disabled_date, disabled_period FROM $disabled_table");
    foreach ($disabled_rows as $row) {
        $d = $row->disabled_date;
        $period = $row->disabled_period;
        if ($period === 'both') {
            $reserved_dates[$d] = 'both';
        } elseif ($period === 'ochtend') {
            // mark morning reserved
            if (isset($reserved_dates[$d]) && $reserved_dates[$d] === 'evening') {
                $reserved_dates[$d] = 'both';
            } else {
                $reserved_dates[$d] = 'morning';
            }
        } elseif ($period === 'avond') {
            // mark evening reserved
            if (isset($reserved_dates[$d]) && $reserved_dates[$d] === 'morning') {
                $reserved_dates[$d] = 'both';
            } else {
                $reserved_dates[$d] = 'evening';
            }
        }
    }
    echo json_encode($reserved_dates);
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
var reservedDates = {};

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

            // Determine reservation status
            var info = reservedDates[dateString];
            if (info === 'both') {
                return [false, 'reserved-date', 'Hele dag gereserveerd'];
            } else if (info === 'morning') {
                return [true, 'morning-reserved', 'Ochtend gereserveerd'];
            } else if (info === 'evening') {
                return [true, 'evening-reserved', 'Avond gereserveerd'];
            }
            // No reservation
            return [true, 'available-date', 'Beschikbaar'];
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
    
    // Handle partial-day block submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_disabled_slot'])) {
        $block_date   = sanitize_text_field($_POST['block_date']);
        $block_period = sanitize_text_field($_POST['block_period']);
        // Skip if already disabled
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}scouting_rentals_disabled_dates WHERE disabled_date = %s AND disabled_period = %s",
                $block_date, $block_period
            )
        );
        if (!$exists) {
            // Only insert when no existing reservation covers this slot
            if ( function_exists('is_reservation_available') && is_reservation_available($block_date, $block_date, $block_period, $block_period) ) {
                $wpdb->insert(
                    $wpdb->prefix . 'scouting_rentals_disabled_dates',
                    [ 'disabled_date' => $block_date, 'disabled_period' => $block_period ],
                    [ '%s', '%s' ]
                );
            }
        }
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_disabled_slot'])) {
        $remove_id = intval($_POST['remove_id']);
        $wpdb->delete(
            $wpdb->prefix . 'scouting_rentals_disabled_dates',
            [ 'id' => $remove_id ],
            [ '%d' ]
        );
    }

    // Fetch up-to-date disabled slots
    $disabled_slots = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}scouting_rentals_disabled_dates");

    echo '<h2>Geblockeerde dagdelen:</h2>';
    echo '<table><tr><th>Datum</th><th>Dagdeel</th><th>Actie</th></tr>';
    foreach ($disabled_slots as $slot) {
        echo '<tr>';
        echo '<td>' . esc_html($slot->disabled_date) . '</td>';
        echo '<td>' . esc_html($slot->disabled_period) . '</td>';
        echo '<td>';
        echo '<form method="post" style="display:inline">';
        echo '<input type="hidden" name="remove_disabled_slot" value="1">';
        echo '<input type="hidden" name="remove_id" value="' . intval($slot->id) . '">';
        echo '<input type="submit" value="Deblokkeer">';
        echo '</form>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';

    // Partial-day block form
    echo '<h2>Blokkeer dagdeel:</h2>';
    echo '<form method="post">';
    echo '<label for="block_date">Datum:</label> <input type="text" id="block_datepicker" name="block_date" required> ';
    echo '<label for="block_period">Dagdeel:</label> <select name="block_period">';
    echo '<option value="ochtend">Ochtend</option>';
    echo '<option value="avond">Avond</option>';
    echo '<option value="both">Hele dag</option>';
    echo '</select> ';
    echo '<input type="submit" name="add_disabled_slot" value="Blokkeren">';
    echo '</form>';

    ?>
<script>
    jQuery(function($) {
        var reserved = {};
        var disabledSlots = [];
        function initBlockPicker() {
            $("#block_datepicker").datepicker({
                dateFormat: "yy-mm-dd",
                beforeShowDay: function(date) {
                    var d = $.datepicker.formatDate('yy-mm-dd', date);
                    // disable if already reserved or already blocked
                    if (reserved[d] || disabledSlots.indexOf(d) !== -1) {
                        return [false, '', 'Niet beschikbaar'];
                    }
                    return [true, ''];
                }
            });
        }
        // fetch reserved dates
        fetch("<?php echo admin_url('admin-ajax.php?action=get_reserved_dates'); ?>")
            .then(r => r.json()).then(data => {
                reserved = data;
                initBlockPicker();
            });
        // fetch disabled slots
        fetch("<?php echo admin_url('admin-ajax.php?action=get_disabled_dates'); ?>")
            .then(r => r.json()).then(data => {
                disabledSlots = data.map(function(slot) { return slot.disabled_date; });
                initBlockPicker();
            });
    });
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
    // Return both date and disabled_period for each slot
    $results = $wpdb->get_results("SELECT id, disabled_date, disabled_period FROM $table_name", OBJECT);
    wp_send_json($results);
}
?>