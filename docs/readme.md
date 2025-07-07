Dit is de verhuurplugin voor de Tecumseh Wordpress-site.
---
Dit project maak ik voor HPG (Honours Programma Gymnasia)

Huidige functies:

- Een verhuurformulier met dynamische prijsweergave (scouting_rentals_form).
- Shortcodes voor het weergeven van aankomende reserveringen, zowel openbaar als met extra details voor leden.
- Een reserveringscontrolepaneel om alle gegevens eenvoudig aan te passen.
- Een prijscontrolepaneel om prijzen te beheren.
- Automatische generatie van verhuurovereenkomsten en facturen met ingevulde gegevens via dompdf.
- Dagdelenpicker: reeds gereserveerde ochtend/avond-dagdelen worden uitgeschakeld en selectie wordt automatisch aangepast.
- Multi-day boekingen beperken tot de beschikbaarheid vóór of tijdens de beschikbare dagdeel beschikbaar tussen reserveringen.
- Kleurgecodeerde legenda toegevoegd aan datepickers (groen: beschikbaar, rood: vol, oranje: ochtend gereserveerd, paars: avond gereserveerd).
- Stam-interface: blokkeer en deblokkeer individuele dagdelen (ochtend, avond of hele dag) zonder dubbele of reeds gereserveerde blokken.

Functies waar ik aan werk:
- Het verzenden van e-mails bij nieuwe reserveringen.
- Het vertalen van code en functies naar het Nederlands.
- Het toevoegen van extra details aan aankomende reserveringen.

---
Lijst van shortcodes:

- `scouting_rentals_form`: Shortcode voor het verhuurformulier met dynamische prijsweergave.
- `scouting_upcoming_reservations_public`: Shortcode voor het weergeven van aankomende reserveringen.
- `scouting_upcoming_reservations`: Shortcode voor het weergeven van aankomende reserveringen met extra details.

Dit is "work in progress"

---

Het reserveringscontrolepaneel (Je kan alle gegevens makkelijk aanpassen)
![Afbeelding 1](https://verhuur.rohandg.nl/1.png)
---

Het prijscontrolepaneel
![Afbeelding 2](https://verhuur.rohandg.nl/2.png)
---

Het verhuurformulier
![Afbeelding 3](https://verhuur.rohandg.nl/3.png)
---

Het paneel voor aankomende reserveringen voor "stam" leden
![Afbeelding 4](https://verhuur.rohandg.nl/4.png)

Een voorbeeld van een gedeelte van de verhuur overeenkomst (het word automatisch ingevult met alle gegevens) Dit word via dompdf gedaan via html en css
![Afbeelding 5](https://verhuur.rohandg.nl/5.png)
![Afbeelding 6](https://verhuur.rohandg.nl/6.png)
Een voorbeeld van een gegenereerde factuur
![Afbeelding 7](https://verhuur.rohandg.nl/7.png)
