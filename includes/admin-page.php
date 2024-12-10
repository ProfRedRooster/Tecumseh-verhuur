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
        $rental = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}scouting_rentals WHERE id = $id");
        $dompdf = new Dompdf\Dompdf();
        $html = '<h1>Factuur</h1>';
        $dompdf->loadHtml($html);
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'landscape');
        // Render the HTML as PDF
        $dompdf->render();
        // Output the generated PDF to Browser
        $dompdf->stream('factuur.pdf');
    }
    if (isset($_GET['contract'])) {
        $id = intval($_GET['contract']);
        $borg = get_option('borg', 201);
        $rental = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}scouting_rentals WHERE id = $id");
        $dompdf = new Dompdf\Dompdf();
        
        $image_path_handtekening = 'https://tecumseh-wp.rohandg.nl/wp-content/uploads/2024/12/rohanhandtekening-e1733859959740.png';
        $image_data_handtekening = file_get_contents($image_path_handtekening);
        $base64_image_handtekening = base64_encode($image_data_handtekening);

        $image_path_logo = 'https://tecumseh-wp.rohandg.nl/wp-content/uploads/2024/12/logo-2-3.png';
        $image_data_logo = file_get_contents($image_path_logo);
        $base64_image_logo = base64_encode($image_data_logo);
  
        $html = '
        <!DOCTYPE html>
        <html lang="nl">
        <head>
            <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px;
        }
        h1 {
            color: #004080;
            text-align: center;
            margin-bottom: 20px;
        }
        h2 {
            color: #004080;
            border-bottom: 2px solid #004080;
            padding-bottom: 5px;
            margin-top: 20px;
        }
        p {
            margin: 10px 0;
        }
        ul {
            margin-left: 20px;
        }
        .footer {
            text-align: center;
            font-size: 0.9em;
            color: #777;
        }
    </style>
        </head>
        <body>
        
        <div class="header">
            <img src="data:image/png;base64,' . $base64_image_logo . '" alt="Signature">
        </div>
        
        <h1>Huurovereenkomst voor het gebouw en terrein van de vereniging Scouting Tecumseh in Haren (GN)</h1>
        
        <p>De ondergetekenden:</p>
        <p>1. Vereniging Scouting Tecumseh, gevestigd te Haren (Gn.) Kamer Van Koophandel nr. 54395356 p/a Berkenlaan 30 9751 GR Haren (Gn.), hierna te noemen "Tecumseh".</p>
        <p>2. <strong>' . esc_html($rental->name) . '</strong> met de e-mail: <strong>' . esc_html($rental->email) . '</strong></p>
        
        <h2>Verklaren:</h2>
        <p>Tecumseh verhuurt aan de onder 2 genoemde organisatie, hierna te noemen "de huurder", haar hoofdgebouw (aangeduid als "groepsgebouw") op perceel Rollematen 2 te Haren gelegen;<p>
        <ul>
            <li>het groepsgebouw en de omliggende terreinen;</li>
            <li>het groepsgebouw, de omliggende terreinen, en de materialen en goederen op de inventarislijst die Tecumseh en de huurder ondertekend hebben;</li>
            <li>de omliggende terreinen en de toiletgelegenheid in het gebouw;</li>
            <li>de omliggende terreinen, de toiletgelegenheid in het gebouw en de materialen en goederen op de inventarislijst die Tecumseh en de huurder ondertekend hebben.</li>
        </ul>
        <p>Deze overeenkomst van huur en verhuur wordt aangegaan onder de volgende bepalingen en bedingen:</p>
        <h2>Artikel 1: de huurprijs</h2>
        <p>€ <strong>' . esc_html($rental->total_price + $borg) . '</strong> ,-</p>
        <p>De prijs is inclusief BTW en het borgbedrag</p>
        <p>De huurprijs moet voor de huurperiode op de rekening van Tecumseh gestort worden op rekening: NL71 ABNA 0571 1612 78 ter name van: Vereniging Scouting Tecumseh te Haren onder vermelding van: het factuurnummer</p>
        
        <h2>Artikel 2: aantal personen</h2>
        <p> De huurder moet opgeven met hoeveel personen hij maximaal komt. Dit maximale aantal mag zonder overleg met Tecumseh niet overschreden worden. Aantal personen door huurder opgegeven: <strong>' . esc_html($rental->number_of_people) . '</strong>  Bij een evenement met meer dan 100 personen dient formeel een evenementen vergunning te worden aangevraagd bij de gemeente Groningen (meerschap) (doorlooptijd ca 8 weken).</p>
        
        <h2>Artikel 3: waarborgsom</h2>
        <p> De huurder moet voor de aanvang van de huurperiode een borgsom storten van € 200 ,- Dit bedrag wordt binnen 14 dagen teruggeboekt als er geen schade is en de huurder aan alle verplichtingen die voorvloeien uit deze overeenkomst heeft voldaan.</p>
        
        <h2>Artikel 4: huurperiode</h2>
        <p>De overeengekomen huurperiode bestaat uit de volgende tijdstippen en datum(s):<br>
        Tijdstip aankomst: in de <strong>' . esc_html($rental->start_period) . '</strong> van <strong>' . esc_html($rental->start_date) . '</strong>, tot de <strong>' . esc_html($rental->end_period) . '</strong> van <strong>' . esc_html($rental->end_date) . '</strong>.</p>
        
        <h2>Artikel 5: zorgplicht en de aansprakelijkheid voor schade</h2>
        <p>De huurder ontvangt het gehuurde in nette staat en moet het na de huurperiode in dezelfde staat weer opleveren. Het gebouw en de omliggende terreinen moeten schoongemaakt en opgeruimd worden. Als dit niet is gebeurd dan kan Tecumseh € 50 ,- in mindering brengen op de terug te betalen waarborgsom. De huurder moet aan het einde van de huurperiode de sleutels aan een door Tecumseh aangewezen persoon overhandigen. Deze zal samen met de huurder het gehuurde inspecteren en de huurder zo nodig aansprakelijk stellen voor schade aan het gebouw en/of de inventaris. Als er goederen of materialen ontbreken die bij het begin van de huurperiode aanwezig waren, dan moeten die op kosten van de huurder worden vervangen. Als er schade is ontstaan door toedoen van de huurder, dan moet deze de schade op zijn kosten laten herstellen. Deze kosten worden van de waarborgsom afgetrokken. Als deze kosten hoger zijn dan de waarborgsom, dan is de huurder aansprakelijk voor het resterende bedrag.</p>
        
        <h2>Artikel 6: annuleringsclausule</h2>
        <p>De huurder kan tot uiterlijk twee maanden voor de huurperiode de overeenkomst zonder kosten opzeggen. Wanneer de huurder na twee maanden, maar voor een maand voor de huurperiode de overeenkomst opzegt, dan moet hij een bedrag van € 50 ,- betalen. Wanneer Tecumseh een andere huurder vindt voor de verhuurperiode, dan wordt dit bedrag teruggestort. Als de huurder binnen een maand voor de verhuurperiode opzegt dan moet hij € 75 ,- betalen. Wanneer Tecumseh voor de verhuurperiode alsnog een huurder vindt dan zal dit bedrag worden teruggestort.</p>
        
        <h2>Artikel 7: beperking gebruik gehuurde</h2>
        <p>Het is de huurder verboden het gehuurde geheel of gedeeltelijk in onderhuur af te staan.</p>
        
        <h2>Artikel 8: overnachten</h2>
        <p>In het groepsgebouw mag alleen in de lokalen met branduitgang worden overnacht.</p>
        
        <h2>Artikel 9: rechten</h2>
        <p>verhuurder Tecumseh mag het gebouw en de omliggende terreinen altijd betreden tijdens de huurperiode.</p>
        
        <h2>Artikel 10: vrijwaring voor schade</h2>
        <p>De huurder vrijwaart Tecumseh voor elke aanspraak op vergoeding voor materiële schade...</p>
        
        <h2>Artikel 11: eerste hulp</h2>
        <p>De huurder is zelf verantwoordelijk voor het verlenen van eerste medische hulp.</p>
        
        <h2>Artikel 12: brandveiligheid</h2>
        <p>De huurder vrijwaart Tecumseh voor elke aanspraak op vergoeding voor materiële schade, en letsel van personen die voortvloeien uit het gebruik van het gehuurde (inclusief spelmaterialen en speeltoestellen).</p>
        
        <h2>Artikel 13: roken</h2>
        <p>De huurder is zelf verantwoordelijk voor het verlenen van eerste medische hulp.</p>
        
        <h2>Artikel 14: brandschade</h2>
        <p>Als er tijdens de huurperiode door toedoen van een persoon of personen waarvoor de huurder aansprakelijk gesteld kan worden brandschade ontstaat, dan is de huurder voor die schade aansprakelijk.</p>
        
        <h2>Artikel 15: geluidsapparatuur</h2>
        <p>Het gebruik van geluidsapparatuur in het gebouw en op het terrein is alleen toegestaan als er geen overlast voor omwonenden wordt veroorzaakt. Bij evenementen waarbij het veroorzaken van geluidshinder in de lijn de verwachting ligt, adviseren wij de huurder om de omwonenden daarvan schriftelijk op de hoogte te stellen.</p>
        
        <h2>Artikel 16: kampvuren</h2>
        <p>Er mag alleen een kampvuur in de speciale kampvuurplaats gemaakt worden en nadat Tecumseh daarvoor uitdrukkelijk toestemming heeft gegeven. Het vuur mag niet hoger opgestookt worden dan een meter. Als de gemeente beperkingen oplegt, zoals een stookverbod, dan moeten die strikt nageleefd worden.</p>
        
        <h2>Artikel 17: afval</h2>
        <p>Tecumseh maakt met de huurder bij aankomst afspraken over de afvoer van het afval. Afval mag `s nachts nooit open op het terrein achterblijven omdat dit ongedierte aantrekt.</p>
        
        <h2>Artikel 18: overlast algemeen</h2>
        <p>De huurder moet overlast of hinder aan de andere gebruikers voorkomen...</p>
        
        <h2>Artikel 19: toegang tot terreinen van de andere scoutinggroepen</h2>
        <p>De huurder moet als een "goede huisvader" met het gehuurde om gaan en overlast of hinder aan de andere gebruikers van het scoutingeiland en de omwonenden voorkomen.</p>
        
        <h2>Artikel 20: ontbinding</h2>
        <p>Als de huurder niet aan de verplichtingen van deze overeenkomst voldoet dan is Tecumseh bevoegd om de overeenkomst met onmiddellijke ingang te ontbinden. Restitutie van de in artikel 2 genoemde waarborgsom evenals van de eventuele restant huursom zal in dat geval niet plaatsvinden. In geval van faillissement of van surseance van betaling verleend aan de huurder zijn de curator van de huurder dan wel haar vereffenaars aansprakelijk voor de voldoening van alle voor de huurder uit deze overeenkomst voortvloeiende verplichtingen.</p>
        
        <h2>Artikel 21: opzegging van de huurovereenkomst</h2>
        <p>Opzegging van deze overeenkomst moet per aangetekende brief worden gedaan.</p>
        
<div class="signature-section">
    <p>Overeengekomen te Haren, Groningen</p>
    <p><strong>' . date("d-m-Y") . '</strong></p>
    <table style="width: 100%; margin-top: 50px;">
        <tr>
            <td style="width: 50%; text-align: left; vertical-align: top">
                <p><strong>' . esc_html($rental->name) . '</strong><br>Namens de huurder</p>
            </td>
            <td style="width: 50%; text-align: right; vertical-align: top">
                <p><strong>R.S.W. de Graaf</strong><br>Namens de verhuurder</p>
                <img src="data:image/png;base64,' . $base64_image_handtekening . '" alt="Signature" style="max-width: 250px; height: auto;">
            </td>
        </tr>
    </table>
</div>
        
        <div class="footer">
            <p>&copy; Vereniging Scouting Tecumseh, Haren (GN) All rights reserved</p>
        </div>
        
        </body>
        </html>';
        
        $dompdf->loadHtml($html);
        
        // (Optional) Setup the paper size and orientation
        $dompdf->setPaper('A4', 'portrait');
        
        // Render the HTML as PDF
        $dompdf->render();
        
        // Output the generated PDF to Browser
        $dompdf->stream('verhuur-overeenkomst-' . esc_html($rental->name) . '.pdf');
    }
}