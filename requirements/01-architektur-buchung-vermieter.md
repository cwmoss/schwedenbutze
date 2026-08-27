# REQ-01: Multi-Vermieter-Konfiguration, Belegungskalender & Buchungsanfrage

- **Dokument-ID:** REQ-01
- **Status:** Spezifiziert / Bereit zur Implementierung
- **Zielversion:** Phase 1

---

## 1. Kontext & Ausgangslage

Die Plattform **Schwedenbutze** ([schwedenbutze.de](https://schwedenbutze.de)) präsentiert mehrere Ferienhäuser in Småland, Schweden (aktuell: *Dångebo*, *Ringshult*, *Oksankas Gård*).

### Bisherige Problemstellungen:
1. **Überdimensionierte Architektur:** Die Nutzung von Sanity Cloud + React Studio + Webhooks + Gridsome-Altlasten ist für eine statische Seite mit wenigen Häusern zu komplex und wartungsintensiv.
2. **Fehlender Buchungs-/Kalenderprozess:** Derzeit existiert kein funktionsfähiger Belegungskalender und kein Formular-Backend zur Übermittlung von Buchungsanfragen.
3. **Direktvermietung mit unterschiedlichen Eigentümern:** Die Häuser sind nicht auf Drittportalen (Airbnb/FeWo) inseriert. Jedes Haus hat eigenständige Vermieter, die Buchungsanfragen direkt erhalten und ihre Verfügbarkeiten eigenständig pflegen müssen.

---

## 2. Kernanforderungen

1. **Komplexitätsreduktion (Flat-File / Markdown-Konfiguration):**
   - Vollständiger Verzicht auf komplexe Cloud-CMS-Strukturen für die Basisfunktionen.
   - Jedes Haus wird über eine strukturierte Konfigurationsdatei (YAML / Markdown mit Frontmatter) definiert.
2. **Multi-Vermieter-Routing:**
   - Jedes Haus besitzt eigene Vermieter-Kontaktdaten.
   - Buchungsanfragen werden automatisch an die hinterlegte E-Mail-Adresse des jeweiligen Vermieters geroutet.
   - Gast erhält eine strukturierte Eingangsbestätigung mit den Kontaktdaten des zuständigen Vermieters.
   - Optional: Zentrale Kopie (BCC/CC) an die Betreiber der Plattform.
3. **Belegungskalender:**
   - Visualisierung freier und belegter Zeiträume im Frontend (z. B. via [Flatpickr](https://flatpickr.js.org)).
   - Belegte Tage sind optisch markiert und nicht auswählbar.
   - Pflege der belegten Zeiträume direkt über die Haus-Konfiguration (ohne Zwang zu externen Portalen).
4. **Buchungsanfrage-Formular:**
   - Schlankes Anfrageformular (Hausauswahl/Detailseite, Anreise, Abreise, Personenanzahl, Kontaktdaten, Freitext).
   - Spam-Schutz ohne nervige Captchas (Honeypot-Feld + Validierung).
   - Zuverlässiges PHP-Mail-Backend.

---

## 3. Datenmodell & Konfigurationsschema

Jedes Haus wird als eigenständige Datei (z. B. `content/houses/{slug}.yaml` oder `.md`) angelegt.

### Beispiel-Schema:

```yaml
id: "dangebo"
title: "Ferienhaus Dångebo"
slug: "dangebo"
active: true

# 1. Vermieter-Stammdaten & Routing
landlord:
  name: "Anna & Lars Svensson"
  email: "anna.svensson@example.com"      # Primärer Empfänger für Buchungsanfragen
  phone: "+46 123 456 789"               # Optional für Rückfragen / Bestätigungsmail
  notify_cc: "buchung@schwedenbutze.de"   # Zentrale Archiv-Kopie (optional)

# 2. Belegte Zeiträume (Datumsbereich YYYY-MM-DD)
blocked_dates:
  - from: "2026-07-04"
    to: "2026-07-18"
    note: "Buchung Fam. Schmidt"
  - from: "2026-08-01"
    to: "2026-08-15"
    note: "Eigenbelegung"

# 3. Haus-Metadaten & Konditionen
details:
  max_guests: 6
  bedrooms: 3
  bathrooms: 1
  min_stay_nights: 3
  price_per_night_eur: 95
  cleaning_fee_eur: 80

# 4. Inhalte & Medien
teaser: "Idyllisches Ferienhaus im Herzen von Småland..."
hero_image: "/assets/images/houses/dangebo/hero.jpg"
gallery:
  - "/assets/images/houses/dangebo/1.jpg"
  - "/assets/images/houses/dangebo/2.jpg"
```

---

## 4. Funktionale Spezifikationen

### 4.1 Frontend-Kalender & Datepicker
* **Bibliothek:** Flatpickr (schlank, responsive, deutsche Lokalisierung, kein schweres JS-Framework erforderlich).
* **Verhalten:**
  * Initialisierung pro Haus: Liest die Liste `blocked_dates` des jeweiligen Hauses aus.
  * Modus: `range` (Auswahl von Anreise- und Abreisedatum).
  * Gesperrte Daten: Automatische Deaktivierung aller Daten innerhalb von `blocked_dates` (Check-in/Check-out an Belegungsgrenzen konfigurierbar).

### 4.2 Buchungsanfrage-Formular
* **Felder:**
  * Haus (automatisch vorausgewählt auf Haus-Detailseiten oder als Dropdown auf zentraler Buchungsseite)
  * Zeitraum: Anreise & Abreise (verbunden mit Flatpickr)
  * Name des Gastes (Pflicht)
  * E-Mail-Adresse des Gastes (Pflicht, syntaktische Prüfung)
  * Telefonnummer (Optional)
  * Anzahl Erwachsene & Kinder (Pflicht)
  * Bemerkungen / Wünsche (Optional)
  * Spam-Schutz: Unsichtbares Honeypot-Feld (`<input type="text" name="website_url" class="hidden">`)
* **Feedback:** Klare Erfolgsmeldung („Vielen Dank für Ihre Anfrage...“) und Fehlervalidierung ohne Seiten-Reload (AJAX/Fetch) oder sauberes Redirect mit Flash-Message.

### 4.3 E-Mail-Verarbeitung & Dispatcher (PHP)
* **Endpunkt:** `api/send-inquiry.php` (oder modulares PHP-Script).
* **Ablauf:**
  1. Prüfung der HTTP-Methode (`POST`) und Spam-Prüfung (Honeypot gefüllt -> Request lautlos abbrechen).
  2. Validierung aller Pflichtfelder (Datum, Name, E-Mail).
  3. Laden der Hauskonfiguration anhand der übergebenen `house_id`.
  4. **E-Mail 1 (an den Vermieter):**
     * Empfänger: `landlord.email`
     * Reply-To: E-Mail-Adresse des Gastes (ermöglicht direktes Antworten per Mail-Client)
     * Betreff: `[Schwedenbutze] Neue Buchungsanfrage für {Haus-Titel} ({Zeitraum})`
     * Inhalt: Alle Angaben des Gastes, berechnete Nächte, Kontaktdaten.
  5. **E-Mail 2 (Bestätigung an den Gast):**
     * Empfänger: E-Mail-Adresse des Gastes
     * Reply-To: `landlord.email`
     * Betreff: `Ihre Buchungsanfrage für {Haus-Titel} – Schwedenbutze`
     * Inhalt: Zusammenfassung der Anfrage, Hinweis auf direkte Rückmeldung durch den Vermieter.

---

## 5. Nicht-funktionale Anforderungen

* **Datenschutz & DSGVO:** Keine Speicherung sensibler Daten in externen Drittanbieter-Clouds; direkte Mail-Weiterleitung; Checkbox zur Datenschutzerklärung im Formular.
* **Performance & Robustheit:** Keine Datenbank-Abhängigkeiten (kein SQL, Flat-File basiert); Ladezeit des Kalenders < 100ms.
* **Wartbarkeit:** Neues Haus hinzufügen erfordert lediglich das Anlegen einer neuen Konfigurationsdatei.

---

## 6. Phasenplan & Nächste Schritte

1. **Phase 1 (dieses Dokument):** Konfigurationsstruktur anlegen, Kalender-Integration mit Flatpickr und PHP-Mail-Dispatcher implementieren.
2. **Phase 2 ([REQ-02](./02-web-admin-content-pflege.md)):** Browserbasiertes Admin-Panel (z. B. Sveltia CMS oder schlankes PHP-Web-Admin) für Vermieter zur Pflege von Texten, Bildern und Belegungszeiten ohne lokalen Rechner.
3. **Phase 3 ([REQ-03](./03-legacy-cleanup.md)):** Vollständige Bereinigung der Altlasten (Sanity Studio, alte Gridsome-Dateien, veraltete Abhängigkeiten).
