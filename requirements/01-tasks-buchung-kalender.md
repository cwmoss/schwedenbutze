# Task-Plan: Multi-Vermieter-Konfiguration, Belegungskalender & Buchungsanfrage

- **Dokument-ID:** TASK-01
- **Branch:** `feature/simplified-booking`
- **Referenz:** [REQ-01: Spezifikation](./01-architektur-buchung-vermieter.md)

---

## Task-Übersicht

| Task | Titel | Status | Ziel & Test-Kriterium |
| :--- | :--- | :--- | :--- |
| **Task 1** | Konfigurationsdateien & Datenmodell | ✅ Abgeschlossen | Strukturierte Daten für Dångebo, Ringshult, Oksankas Gård angelegt. *Test: PHP-Parser-Check.* |
| **Task 2** | Verfügbarkeits-Endpunkt (`api/availability.php`) | ✅ Abgeschlossen | REST/JSON Endpunkt für belegte Tage pro Haus. *Test: curl/Browser-Abfrage liefert JSON-Tage.* |
| **Task 3** | Belegungskalender-Frontend (Flatpickr) | ✅ Abgeschlossen | Responsive Kalenderanzeige mit gesperrten Tagen. *Test: Belegte Tage sind optisch gesperrt.* |
| **Task 4** | Buchungsanfrage-Formular & Spam-Schutz | ✅ Abgeschlossen | Formular UI mit Validierung & Honeypot. *Test: Validierungsprüfung bei Pflichtfeldern & Spam-Trap.* |
| **Task 5** | E-Mail-Routing & Dispatcher (`api/send-inquiry.php`) | ✅ Abgeschlossen | PHP-Mailer versendet Anfrage an Haus-Vermieter mit Gast-Reply-To. *Test: Testversand an Zieladresse.* |
| **Task 6** | End-to-End Integration & Templates | ✅ Abgeschlossen | Integration in Buchungsseite und Haus-Detailseiten. *Test: Vollständiger Buchungs-Flow.* |

---

## Details zu den einzelnen Tasks

### Task 1: Konfigurationsdateien & Datenmodell anlegen
- [ ] Verzeichnis `slft/content/houses/` erstellen.
- [ ] `dangebo.json` anlegen (Vermieterdaten, Belegungszeiten, Details).
- [ ] `ringshult.json` anlegen.
- [ ] `oksankas-gard.json` anlegen.
- [ ] Test-Skript zur Validierung der JSON/YAML-Dateien erstellen und ausführen.

### Task 2: Verfügbarkeits-Endpunkt
- [ ] `slft/api/availability.php` erstellen (Standalone, ohne Sanity-Abhängigkeit).
- [ ] Logik zur Auflösung von Datumsbereichen (`from` -> `to`) in Einzeltage (`YYYY-MM-DD`).
- [ ] CORS-Header & JSON-Response.
- [ ] Test mit `curl` und verschiedenen Haus-IDs.

### Task 3: Belegungskalender-Frontend (Flatpickr)
- [ ] Flatpickr CSS & JS in `slft/src/assets/` einbinden.
- [ ] JavaScript-Modul erstellen: Lädt belegte Tage via API und initialisiert Flatpickr im `range`-Modus.
- [ ] Visuelle Tests im Browser (Styling passend zum Spectral-Theme).

### Task 4: Buchungsanfrage-Formular & UI
- [ ] Formular-Markup mit Hausauswahl, Zeitraum, Name, E-Mail, Telefon, Gäste, Nachricht.
- [ ] Unsichtbares Honeypot-Feld (`website_url`) zur Spamerkennung.
- [ ] Clientseitige Validierung.

### Task 5: E-Mail-Routing & Dispatcher
- [ ] `slft/api/send-inquiry.php` erstellen.
- [ ] Honeypot-Prüfung & Server-Validierung.
- [ ] E-Mail an jeweiligen Vermieter generieren (inkl. `Reply-To: $guest_email`).
- [ ] Eingangsbestätigung an Gast generieren.
- [ ] Testdurchlauf mit Mock- bzw. Test-E-Mails.

### Task 6: End-to-End Integration
- [ ] Einbindung in `slft/src/pages/buchung.php` bzw. Haus-Templates.
- [ ] Test des Gesamtablaufs im Browser.
