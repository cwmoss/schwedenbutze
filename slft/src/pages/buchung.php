<?php
$is_booking = true;
layout("default");

// Lade alle konfigurierten Häuser
$houses_dir = SLOWFOOT_BASE . '/content/houses';
$house_files = glob($houses_dir . '/*.json');
$houses = [];
foreach ($house_files as $hf) {
    $hdata = json_decode(file_get_contents($hf), true);
    if ($hdata && !empty($hdata['active'])) {
        $houses[] = $hdata;
    }
}

// Prüfe, ob ein bestimmtes Haus vorausgewählt wurde (z.B. /buchung?house=dangebo)
$selected_house = $_GET['house'] ?? ($houses[0]['id'] ?? 'dangebo');
?>

<article id="main">
  <header>
    <h2>Buchungsanfrage</h2>
    <p>Frage unverbindlich deinen Wunschzeitraum für unsere Schwedenbutzen an.</p>
  </header>

  <section class="wrapper style5">
    <div class="inner">

      <div id="form-alert-success" class="form-alert success" style="display: none;"></div>
      <div id="form-alert-error" class="form-alert error" style="display: none;"></div>

      <form id="booking-inquiry-form" method="post" action="/api/send-inquiry.php">
        <div class="row gtr-uniform">

          <!-- 1. Haus-Auswahl -->
          <div class="col-12">
            <label for="house"><strong>Ferienhaus auswählen:</strong></label>
            <select name="house" id="house" required>
              <?php foreach ($houses as $h): ?>
                <option value="<?= htmlspecialchars($h['id']) ?>" <?= $h['id'] === $selected_house ? 'selected' : '' ?>>
                  <?= htmlspecialchars($h['title']) ?> (ab <?= $h['details']['price_per_night_eur'] ?? '-' ?> €/Nacht)
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Haus-Info Box -->
          <div class="col-12">
            <div id="house_info_box"></div>
          </div>

          <!-- 2. Zeitraum (Flatpickr) -->
          <div class="col-12">
            <label for="booking_dates"><strong>Reisezeitraum (Anreise & Abreise):</strong></label>
            <input type="text" id="booking_dates" placeholder="Verfügbarkeit wird geladen..." readonly required>
            <input type="hidden" name="checkin" id="checkin">
            <input type="hidden" name="checkout" id="checkout">
            <div id="nights_display" style="margin-top: 0.5em; font-weight: bold; color: #2ecc71;"></div>
          </div>

          <!-- 3. Kontaktdaten -->
          <div class="col-6 col-12-xsmall">
            <label for="name"><strong>Dein Name:</strong> *</label>
            <input type="text" name="name" id="name" placeholder="Vor- und Nachname" required>
          </div>

          <div class="col-6 col-12-xsmall">
            <label for="email"><strong>Deine E-Mail-Adresse:</strong> *</label>
            <input type="email" name="email" id="email" placeholder="name@beispiel.de" required>
          </div>

          <div class="col-6 col-12-xsmall">
            <label for="phone"><strong>Telefonnummer (optional):</strong></label>
            <input type="tel" name="phone" id="phone" placeholder="+49 ...">
          </div>

          <div class="col-6 col-12-xsmall">
            <label for="guests"><strong>Anzahl Personen:</strong> *</label>
            <select name="guests" id="guests" required>
              <option value="1">1 Person</option>
              <option value="2" selected>2 Personen</option>
              <option value="3">3 Personen</option>
              <option value="4">4 Personen</option>
              <option value="5">5 Personen</option>
              <option value="6">6 Personen</option>
              <option value="7">7 Personen</option>
              <option value="8">8 Personen</option>
            </select>
          </div>

          <!-- 4. Nachricht -->
          <div class="col-12">
            <label for="message"><strong>Nachricht / Fragen an den Vermieter (optional):</strong></label>
            <textarea name="message" id="message" rows="4" placeholder="Gibt es besondere Wünsche oder Fragen zum Aufenthalt?"></textarea>
          </div>

          <!-- Spam Honeypot (unsichtbar für Menschen) -->
          <div class="col-12 hp-field">
            <label for="website_url">Bitte dieses Feld freilassen:</label>
            <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
          </div>

          <!-- Datenschutz-Hinweis -->
          <div class="col-12">
            <p style="font-size: 0.85em; opacity: 0.8;">
              ℹ️ Ihre Anfrage wird direkt an die zuständigen Vermieter des Hauses übermittelt.
              Die Vermieter melden sich zeitnah per E-Mail bei Ihnen.
            </p>
          </div>

          <!-- Submit Button -->
          <div class="col-12">
            <ul class="actions">
              <li>
                <button type="submit" id="submit-btn" class="button primary fit">
                  Unverbindliche Buchungsanfrage abschicken
                </button>
              </li>
            </ul>
          </div>

        </div>
      </form>

    </div>
  </section>
</article>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // 1. Initialisiere Buchungskalender mit Flatpickr
  const calendar = new BookingCalendar({
    calendarInput: '#booking_dates',
    houseSelect: '#house',
    checkinInput: '#checkin',
    checkoutInput: '#checkout',
    nightsDisplay: '#nights_display',
    infoBox: '#house_info_box',
    apiEndpoint: '/api/availability.php',
    defaultHouse: '<?= htmlspecialchars($selected_house) ?>'
  });

  // 2. AJAX Formular-Absendung mit Feedback
  const form = document.getElementById('booking-inquiry-form');
  const alertSuccess = document.getElementById('form-alert-success');
  const alertError = document.getElementById('form-alert-error');
  const submitBtn = document.getElementById('submit-btn');

  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    // Validierung
    const checkin = document.getElementById('checkin').value;
    const checkout = document.getElementById('checkout').value;

    if (!checkin || !checkout) {
      alertError.textContent = 'Bitte wählen Sie Ihren Anreise- und Abreisezeitraum im Kalender aus.';
      alertError.style.display = 'block';
      alertSuccess.style.display = 'none';
      return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Wird gesendet...';
    alertError.style.display = 'none';
    alertSuccess.style.display = 'none';

    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    try {
      const res = await fetch('/api/send-inquiry.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify(data)
      });

      const json = await res.json();

      if (json.success) {
        alertSuccess.innerHTML = '<strong>Vielen Dank!</strong> ' + json.message;
        alertSuccess.style.display = 'block';
        form.reset();
        if (calendar.fpInstance) calendar.fpInstance.clear();
        document.getElementById('nights_display').textContent = '';
      } else {
        alertError.innerHTML = '<strong>Fehler:</strong> ' + (json.error || 'Die Anfrage konnte nicht gesendet werden.');
        alertError.style.display = 'block';
      }
    } catch (err) {
      alertError.innerHTML = '<strong>Verbindungsfehler:</strong> Bitte prüfen Sie Ihre Internetverbindung oder versuchen Sie es später erneut.';
      alertError.style.display = 'block';
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Unverbindliche Buchungsanfrage abschicken';
    }
  });
});
</script>