# Task-Plan: iCal / Google-Kalender Synchronisation (TASK-02)

- **Dokument-ID:** TASK-02
- **Branch:** `feature/simplified-booking`
- **Referenz:** [REQ-02: iCal / Google-Kalender Synchronisation](./02-ical-google-calendar-sync.md)

---

## Task-Übersicht

| Task | Titel | Status | Ziel & Test-Kriterium |
| :--- | :--- | :--- | :--- |
| **Task 1** | Standalone iCal Parser (`lib/ICalParser.php`) | ✅ Abgeschlossen | Liest `.ics`-Dateien und extrahiert Start-/Enddaten ganztägiger und zeitbasierter Events. *Test: RFC 5545 Parsing-Test.* |
| **Task 2** | Caching-Layer (`lib/ICalCache.php`) | ✅ Abgeschlossen | Speichert gecachte JSON-Termine mit konfigurierbarem TTL (z. B. 15 Min) und Stale-Fallback bei Offline-Google. *Test: Cache-Hit & TTL-Validierung.* |
| **Task 3** | Integration in `api/availability.php` | ✅ Abgeschlossen | Führt manuelle `blocked_dates` und dynamische `ical_url` Termine zu einer einheitlichen Sperrliste zusammen. *Test: JSON-Response enthält beide Quellen.* |
| **Task 4** | Integration in `api/send-inquiry.php` | ✅ Abgeschlossen | Verhindert Buchungsanfragen für Termine, die im Google-Kalender belegt sind. *Test: 409-Kollision bei iCal-Termin.* |
| **Task 5** | Test-Suite mit echten iCal-Fixtures | ✅ Abgeschlossen | End-to-End Test mit realistischen Google-Kalender `.ics` Datensätzen. *Test: `php slft/tests/test_ical_sync.php`.* |
| **Task 6** | Vermieter-Leitfaden & Konfigurations-Update | ✅ Abgeschlossen | Dokumentation und Hinterlegung von Test-Kalender-Links in den Hausdateien. *Test: Doku-Review.* |

---

## Detaillierte Umsetzungsschritte

### Task 1: Standalone iCal-Parser (`slft/lib/ICalParser.php`)
- [x] Klasse `ICalParser` anlegen (ohne externe Composer-Dependencies).
- [x] Parsen von `BEGIN:VEVENT` bis `END:VEVENT`.
- [x] Erkennung von `DTSTART;VALUE=DATE:YYYYMMDD` vs. `DTSTART:YYYYMMDDTHHMMSSZ`.
- [x] Erkennung von `DTEND` (exklusives Enddatum) vs. `DTSTART + DURATION`.
- [x] Ignorieren von `STATUS:CANCELLED`.
- [x] Normalisierung zu einem sauberen PHP-Array von Belegungen: `[ ['from' => 'YYYY-MM-DD', 'to' => 'YYYY-MM-DD', 'summary' => '...'], ... ]`.

### Task 2: Caching-Layer (`slft/lib/ICalCache.php`)
- [x] Automatisches Anlegen von `slft/var/cache/ical/`.
- [x] Prüfung des Datei-Alters (`filemtime`). Wenn älter als TTL (z. B. 900s), Abruf via `curl` / `file_get_contents` mit kurzem Timeout (max. 3 Sekunden).
- [x] Bei Netzwerk-Fehler oder Google-Ausfall: Fallback auf bestehenden Cache-Stand, kein Seitenausfall.

### Task 3: Integration in `slft/api/availability.php`
- [x] Einlesen von `calendars.ical_url` aus `content/houses/{house}.json`.
- [x] Kombination aus manuellen `blocked_dates` + iCal-Events.
- [x] Entfernen von Duplikaten und Sortierung der `disabled_dates`.

### Task 4: Kollisionsprüfung in `slft/api/send-inquiry.php`
- [x] Vor dem E-Mail-Versand Abgleich der Wunschdaten gegen manuelle und iCal-Belegungen.
- [x] Bei Überschneidung: HTTP 409 Conflict mit verständlicher Fehlermeldung.

### Task 5: Umfassende Tests
- [x] Erstellen einer Fixture-Datei `slft/tests/fixtures/sample_google_calendar.ics`.
- [x] Automatisierter Test `slft/tests/test_ical_sync.php`.

### Task 6: Konfiguration & Dokumentation
- [x] Aktualisierung von `requirements/README.md`.
- [x] Bereitstellung der Vermieter-Anleitung als handliche PDF/Markdown-Referenz ([vermieter-anleitung-google-kalender.md](./vermieter-anleitung-google-kalender.md)).
