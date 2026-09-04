/**
 * Schwedenbutze - Buchungskalender & Verfügbarkeits-Integration
 * Nutzt Flatpickr zur Visualisierung und Sperrung belegter Zeiträume.
 */

class BookingCalendar {
  constructor(config = {}) {
    this.calendarInput = document.querySelector(config.calendarInput || '#booking_dates');
    this.houseSelect = document.querySelector(config.houseSelect || '#house');
    this.checkinInput = document.querySelector(config.checkinInput || '#checkin');
    this.checkoutInput = document.querySelector(config.checkoutInput || '#checkout');
    this.nightsDisplay = document.querySelector(config.nightsDisplay || '#nights_display');
    this.infoBox = document.querySelector(config.infoBox || '#house_info_box');
    this.apiEndpoint = config.apiEndpoint || '/api/availability.php';
    this.defaultHouse = config.defaultHouse || (this.houseSelect ? this.houseSelect.value : 'dangebo');
    
    this.currentHouseData = null;
    this.fpInstance = null;

    if (this.calendarInput) {
      this.init();
    }
  }

  async init() {
    this.initFlatpickr();

    // URL-Parameter auslesen (z. B. ?house=ringshult)
    const urlParams = new URLSearchParams(window.location.search);
    const houseFromUrl = urlParams.get('house');

    if (this.houseSelect) {
      if (houseFromUrl) {
        const optionExists = Array.from(this.houseSelect.options).some(opt => opt.value === houseFromUrl);
        if (optionExists) {
          this.houseSelect.value = houseFromUrl;
        }
      }

      this.houseSelect.addEventListener('change', (e) => {
        this.loadAvailability(e.target.value);
      });
      await this.loadAvailability(this.houseSelect.value || this.defaultHouse);
    } else if (this.defaultHouse) {
      await this.loadAvailability(houseFromUrl || this.defaultHouse);
    }
  }

  initFlatpickr() {
    if (typeof flatpickr === 'undefined') {
      console.error('Flatpickr library is not loaded!');
      return;
    }

    const self = this;
    const localeDe = (flatpickr.l10ns && flatpickr.l10ns.de) ? flatpickr.l10ns.de : 'de';

    this.fpInstance = flatpickr(this.calendarInput, {
      mode: 'range',
      minDate: 'today',
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd.m.Y',
      locale: localeDe,
      showMonths: window.innerWidth > 768 ? 2 : 1,
      disable: [],
      onChange: function (selectedDates, dateStr, instance) {
        self.handleDateSelection(selectedDates);
      }
    });
  }

  async loadAvailability(houseId) {
    if (!houseId) return;

    try {
      if (this.calendarInput) {
        this.calendarInput.setAttribute('placeholder', 'Verfügbarkeit wird geladen...');
      }

      const response = await fetch(`${this.apiEndpoint}?house=${encodeURIComponent(houseId)}`);
      const json = await response.json();

      if (!json.success || !json.house) {
        console.warn('Konnte Verfügbarkeit nicht laden:', json);
        return;
      }

      this.currentHouseData = json.house;
      const disabledDates = json.house.disabled_dates || [];

      if (this.fpInstance) {
        this.fpInstance.clear();
        this.fpInstance.set('disable', disabledDates);
        this.calendarInput.setAttribute('placeholder', 'Anreise - Abreise auswählen');
      }

      this.updateHouseInfo(json.house);
    } catch (err) {
      console.error('Fehler beim Laden der Verfügbarkeiten:', err);
      if (this.calendarInput) {
        this.calendarInput.setAttribute('placeholder', 'Datum auswählen');
      }
    }
  }

  handleDateSelection(selectedDates) {
    if (selectedDates.length === 2) {
      const checkin = this.formatDate(selectedDates[0]);
      const checkout = this.formatDate(selectedDates[1]);

      if (this.checkinInput) this.checkinInput.value = checkin;
      if (this.checkoutInput) this.checkoutInput.value = checkout;

      const diffTime = Math.abs(selectedDates[1] - selectedDates[0]);
      const diffNights = Math.round(diffTime / (1000 * 60 * 60 * 24));

      if (this.nightsDisplay) {
        const minStay = this.currentHouseData?.details?.min_stay_nights || 1;
        let text = `${diffNights} ${diffNights === 1 ? 'Nacht' : 'Nächte'}`;
        if (diffNights < minStay) {
          text += ` (⚠️ Mindestaufenthalt: ${minStay} Nächte)`;
        }
        this.nightsDisplay.textContent = text;
      }
    } else {
      if (this.checkinInput) this.checkinInput.value = '';
      if (this.checkoutInput) this.checkoutInput.value = '';
      if (this.nightsDisplay) this.nightsDisplay.textContent = '';
    }
  }

  updateHouseInfo(house) {
    if (!this.infoBox || !house) return;
    const details = house.details || {};
    let html = `<div class="house-quick-info">`;
    html += `<strong>${house.title}</strong> &bull; `;
    if (details.max_guests) html += `Max. ${details.max_guests} Gäste &bull; `;
    if (details.price_per_night_eur) html += `ab ${details.price_per_night_eur} € / Nacht &bull; `;
    if (details.min_stay_nights) html += `Mindestaufenthalt: ${details.min_stay_nights} Nächte`;
    html += `</div>`;
    this.infoBox.innerHTML = html;
  }

  formatDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
  }
}

// Global verfügbar machen
window.BookingCalendar = BookingCalendar;
