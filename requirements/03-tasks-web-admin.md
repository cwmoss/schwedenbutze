# Tasks: Web-Admin & Content-Pflege für Vermieter (TASK-03)

- **Dokument-ID:** TASK-03
- **Bezug:** Umsetzung zu [REQ-03: Web-Admin & Content-Pflege](./03-web-admin-content-pflege.md)
- **Status:** Bereit zur Umsetzung

---

## Übersicht der Arbeitspakete

| Task | Titel | Status | Beschreibung |
| :--- | :--- | :--- | :--- |
| **Task 1** | Flat-File Struktur & Migration | ✅ Erledigt | Anlegen von `content/houses/{slug}/` (JSON, Markdown-Unterseiten, Bilder) & Migration aus Sanity-DB; Rückwärtskompatibilität in APIs |
| **Task 2** | Authentifizierung & Rollensystem | ⏳ Bereit | Login, Session-Management, Argon2id/Bcrypt Passwort-Hashing, CSRF-Schutz & Rollen (Vermieter vs. Super-Admin) |
| **Task 3** | Backend-APIs (Haus & Unterseiten) | ⏳ Bereit | JSON/REST-Endpunkte für Stammdaten, Preise, iCal, Sperren und Unterseiten-CRUD |
| **Task 4** | Medien- & Bildupload-Handler | ⏳ Bereit | Drag & Drop Upload, MIME-Type-Prüfung, Web-Optimierung & lokale Galerie |
| **Task 5** | Admin-Weboberfläche (Dashboard & Editor) | ⏳ Bereit | Responsive Single-Page-UI mit Tabs für Stammdaten, Kalender, Hauptseite, Unterseiten-Editor (Markdown + Vorschau) & Galerie |
| **Task 6** | Slowfoot-Integration & Build-Trigger | ⏳ Bereit | Flat-File-Loader in `slowfoot-config.php`, Re-Build-Endpunkt `/admin/api/build.php` |
| **Task 7** | Tests & Vermieter-Handbuch | ⏳ Bereit | Test-Suite für Auth/APIs, Validierung aller 3 Häuser und Erstellung der Vermieter-Anleitung |

---

## Detaillierte Umsetzungsschritte

### Task 1: Flat-File Struktur & Datenmigration
- [x] Modulare Verzeichnisstruktur `slft/content/houses/{slug}/` anlegen (`house.json`, `subpages/`, `images/`).
- [x] Migrationsskript erstellen, das bestehende Unterseiten (`post`) und Haus-Hauptseiten (`page`) aus `slft/var/slowfoot.db` und den bisherigen JSONs in saubere Markdown-Dateien und `house.json` überführt.
- [x] Bestehende APIs ([`slft/api/availability.php`](file:///Users/uo/dev/schwedenbutze/slft/api/availability.php) & [`slft/api/send-inquiry.php`](file:///Users/uo/dev/schwedenbutze/slft/api/send-inquiry.php)) so aktualisieren, dass sie transparent sowohl `content/houses/{slug}/house.json` als auch `content/houses/{slug}.json` unterstützen.
- [x] Ausführung und Verifikation aller bestehenden Tests (`slft/tests/*.php`).

### Task 2: Authentifizierung & Rollensystem (`slft/admin/auth.php`)
- [ ] Erstellen von `slft/var/admin_auth.json` (außerhalb des Web-Roots) mit sicheren Passwort-Hashes (`password_hash()`) für die 3 Vermieter und den Super-Admin.
- [ ] Session-Manager mit `HttpOnly`-, `SameSite=Strict`- und `Secure`-Cookies.
- [ ] Login- und Logout-Controller mit Brute-Force-Schutz (Rate-Limiting).
- [ ] CSRF-Token Generierung und Middleware für alle POST/PUT/DELETE Anfragen.
- [ ] Berechtigungsprüfung: Vermieter hat ausschließlich Schreib- und Leserechte auf sein eigenes Hausverzeichnis (`content/houses/{slug}/`).

### Task 3: Backend-APIs (`slft/admin/api/`)
- [ ] `slft/admin/api/house.php`:
  - `GET`: Liefert Stammdaten, Preise, Kontaktdaten, Hauptseiten-Texte und Infoboxen des Hauses.
  - `POST`: Validiert und speichert Änderungen in `house.json`.
- [ ] `slft/admin/api/subpages.php`:
  - `GET`: Liste aller Unterseiten des Hauses (inkl. Metadaten aus dem Frontmatter).
  - `POST`: Anlegen einer neuen Unterseite oder Aktualisieren einer bestehenden (`subpages/{slug}.md`).
  - `DELETE`: Sicheres Löschen einer Unterseite.
- [ ] `slft/admin/api/calendar.php`:
  - Hinzufügen, Bearbeiten und Löschen von manuellen Sperrzeiträumen (`blocked_ranges`).
  - Speichern und Validieren der iCal-URL.

### Task 4: Medien- & Bildupload-Handler (`slft/admin/api/upload.php`)
- [ ] Upload-Endpunkt mit Größenbeschränkung (z. B. max. 15 MB) und Whitelist erlaubter MIME-Types (JPEG, PNG, WebP).
- [ ] Speicherung der Originale im lokalen Hausordner `content/houses/{slug}/images/`.
- [ ] Automatische Skalierung für responsive Web-Profile (`1200x600`, `600x300`).
- [ ] Rückgabe von Bildpfad und Markdown-Snippet für die direkte Übernahme in den Editor.

### Task 5: Admin-Weboberfläche (`slft/admin/index.php`)
- [ ] Klares, responsives HTML5/CSS-Layout (ohne externe Framework-Abhängigkeiten, voll mobilfähig).
- [ ] Login-Formular mit CSRF-Token.
- [ ] Navigation zwischen den Bereichen:
  - **Konditionen & Routing:** Formularfelder für Übernachtungspreis, Mindestaufenthalt, Endreinigung, Gästelimit, Kontaktdaten.
  - **Belegungskalender:** Übersicht belegter Zeiträume, Monatsvorschau, Formular für neue manuelle Sperren, Google-Kalender Link.
  - **Haus-Hauptseite:** Hero-Bild-Auswahl, Willkommenstext, Ausführliche Vorstellung, Verwaltung der Infobox-Kacheln mit Icon-Vorschau.
  - **Unterseiten-Manager:**
    - Tabelle/Kacheln aller Unterseiten mit Status und Schnellaktionen.
    - Modal / Editor-Ansicht: Titel, Slug, Hero-Bild, Teaser, Schalter *„Auf Haus-Startseite anzeigen“*.
    - Markdown-Editor mit Toolbar (Überschrift, Fett, Kursiv, Liste, Link, Bild einfügen) und Sofortvorschau.
  - **Medien:** Bildübersicht mit Drag & Drop Upload und Löschfunktion.

### Task 6: Slowfoot-Integration & Build-Trigger
- [ ] Anlegen eines lokalen Flat-File-Loaders in `slft/slowfoot-config.php`, der Häuser und Unterseiten direkt aus `content/houses/` lädt.
- [ ] Endpunkt `slft/admin/api/build.php`:
  - Führt bei Klick auf *„Änderungen veröffentlichen“* den Build-Prozess für die betroffenen Seiten aus.
  - Gibt Statusmeldung (Erfolg / Fehler) und Link zur erzeugten Seite zurück.

### Task 7: Tests & Vermieter-Handbuch
- [ ] Automatisierte Tests für Auth, House-API, Subpages-API und Uploads (`slft/tests/test_admin_api.php`).
- [ ] Bereitstellung eines bebilderten Handbuchs [`requirements/vermieter-anleitung-web-admin.md`](./vermieter-anleitung-web-admin.md) für Vermieter.
