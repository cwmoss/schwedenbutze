# Test-Anleitung: Schwedenbutze Admin-Oberfläche & CMS

Diese Anleitung beschreibt Schritt für Schritt, wie das webbasierte Administrations-Panel von **Schwedenbutze** lokal gestartet, manuell im Browser bedient und über automatisierte Test-Suites geprüft werden kann.

---

## Inhaltsverzeichnis
1. [Voraussetzungen & Vorbereitung](#1-voraussetzungen--vorbereitung)
2. [Lokalen Entwicklungsserver starten](#2-lokalen-entwicklungsserver-starten)
3. [Aufruf im Browser & Test-Accounts](#3-aufruf-im-browser--test-accounts)
4. [Manuelle Testfälle pro Funktionsbereich](#4-manuelle-testfälle-pro-funktionsbereich)
   - [4.1 Login & Sicherheit (RBAC & Brute-Force)](#41-login--sicherheit-rbac--brute-force)
   - [4.2 Tab 1: Stammdaten & Preise](#42-tab-1-stammdaten--preise)
   - [4.3 Tab 2: Belegungskalender & manuelle Sperren](#43-tab-2-belegungskalender--manuelle-sperren)
   - [4.4 Tab 3: Hauptseite & Hero-Bereich](#44-tab-3-hauptseite--hero-bereich)
   - [4.5 Tab 4: Unterseiten-CMS (Markdown & Vorschau)](#45-tab-4-unterseiten-cms-markdown--vorschau)
   - [4.6 Tab 5: Bildergalerie & Drag & Drop Upload](#46-tab-5-bildergalerie--drag--drop-upload)
   - [4.7 Site-Build & Veröffentlichung („🚀 Veröffentlichen“)](#47-site-build--veröffentlichung--veröffentlichen)
5. [Automatisierte Tests ausführen](#5-automatisierte-tests-ausführen)
6. [Fehleranalyse & Troubleshooting](#6-fehleranalyse--troubleshooting)

---

## 1. Voraussetzungen & Vorbereitung

- **PHP:** Version 8.2 oder neuer mit aktivierten Modulen `pdo_sqlite` und `gd` (für Bildverarbeitung).
  ```bash
  php -v
  ```
- **Projektverzeichnis:** Alle Befehle werden aus dem Repository-Root `/Users/uo/dev/schwedenbutze` ausgeführt.

---

## 2. Lokalen Entwicklungsserver starten

Starte den lokalen PHP-Server inklusive Routing für statische Assets, API-Endpunkte und den Admin-Bereich:

```bash
make dev
```

*Alternativer Direktaufruf ohne Make:*
```bash
php -S localhost:8000 slft/router.php
```

Sobald die Meldung erscheint, ist der Server betriebsbereit:
```
Starte lokalen Server auf http://localhost:8000 ...
[Sun Sep 27 10:00:00 2026] PHP 8.4.x Development Server (http://localhost:8000) started
```

---

## 3. Aufruf im Browser & Test-Accounts

Öffne im Browser Deiner Wahl:
👉 **[http://localhost:8000/admin/](http://localhost:8000/admin/)**

Folgende Testkonten sind in [`slft/var/admin_auth.json`](file:///Users/uo/dev/schwedenbutze/slft/var/admin_auth.json) vorkonfiguriert:

| Benutzername | Passwort | Rolle | Berechtigung / Scope |
| :--- | :--- | :--- | :--- |
| **`dangebo`** | `Dangebo2026!` | Vermieter | Ausschließlich Haus **Dångebo** |
| **`ringshult`** | `Ringshult2026!` | Vermieter | Ausschließlich Haus **Ringshult** |
| **`oksankas-gard`** | `Oksankas2026!` | Vermieter | Ausschließlich Haus **Oksankas Gård** |
| **`admin`** | `AdminSchweden2026!` | Super-Admin | Voller Zugriff mit **Haus-Umschalter** im Header |

---

## 4. Manuelle Testfälle pro Funktionsbereich

### 4.1 Login & Sicherheit (RBAC & Brute-Force)

1. **Erfolgreicher Login:**
   - Melde Dich mit `dangebo` und `Dangebo2026!` an.
   - *Erwartetes Ergebnis:* Das Dashboard öffnet sich sofort, oben links steht `[LANDLORD] 🏡 Dångebo`.
2. **Mandantentrennung prüfen:**
   - Als `dangebo` eingeloggt: Im Header existiert kein Haus-Auswahlmenü, alle angezeigten Texte, Unterseiten und Bilder gehören zu Dångebo.
   - Melde Dich ab und logge Dich als `admin` (`AdminSchweden2026!`) ein: Im Header erscheint ein Dropdown-Menü, mit dem Du zwischen Dångebo, Ringshult und Oksankas Gård wechseln kannst.
3. **Brute-Force-Schutz:**
   - Gib 5-mal ein falsches Passwort für einen Benutzer ein.
   - *Erwartetes Ergebnis:* Nach dem 5. Fehlversuch wird die rote Warnung *„Zu viele fehlgeschlagene Versuche. Konto für 15 Minuten gesperrt.“* angezeigt.

---

### 4.2 Tab 1: Stammdaten & Preise

1. Wechsle in den Reiter **„Stammdaten & Preise“**.
2. Ändere z. B. den Preis pro Nacht von `95` auf `99` oder passe den Mindestaufenthalt an.
3. Klicke auf den Button **„Stammdaten speichern“**.
4. *Erwartetes Ergebnis:* Oben rechts erscheint ein grüner Toast-Hinweis *„Stammdaten erfolgreich gespeichert!“*. Beim Neuladen der Seite bleibt der neue Wert erhalten.

---

### 4.3 Tab 2: Belegungskalender & manuelle Sperren

1. Wechsle in den Reiter **„Belegungskalender“**.
2. **Neue Sperre anlegen:**
   - Wähle im Formular *„Neuen Zeitraum sperren“* ein Startdatum (z. B. `2026-11-01`), ein Enddatum (z. B. `2026-11-07`) und als Notiz `Test Eigenbedarf`.
   - Klicke auf **„Zeitraum sperren“**.
   - *Erwartetes Ergebnis:* Grüner Toast *„Sperrzeitraum eingetragen“*. Der Zeitraum erscheint sofort in der darunterliegenden Tabelle.
3. **Sperre löschen:**
   - Klicke in der Tabelle neben der neu angelegten Sperre auf **„🗑️ Löschen“**.
   - Bestätige die Sicherheitsabfrage.
   - *Erwartetes Ergebnis:* Die Sperre wird entfernt und die Tabelle aktualisiert sich sofort.

---

### 4.4 Tab 3: Hauptseite & Hero-Bereich

1. Wechsle in den Reiter **„Hauptseite & Hero“**.
2. Wähle im Dropdown **Hero-Bild** ein anderes Bild aus.
3. Passe den **Teaser-Text** an.
4. Ändere das Icon oder den Text einer der 6 **Infobox-Kacheln**.
5. Klicke auf **„Hauptseite & Hero speichern“**.
6. *Erwartetes Ergebnis:* Bestätigungs-Toast *„Hauptseite erfolgreich gespeichert!“*.

---

### 4.5 Tab 4: Unterseiten-CMS (Markdown & Vorschau)

1. Wechsle in den Reiter **„Unterseiten“**.
2. **Unterseite bearbeiten:**
   - Klicke bei einer bestehenden Seite (z. B. *„Die Sauna“*) auf **„✏️ Bearbeiten“**.
   - Der Editor öffnet sich. Tippe Text im Markdown-Feld ein oder nutze die Toolbar-Buttons (**B**, *I*, **H2**, **H3**, **List**, **Link**).
   - *Erwartetes Ergebnis:* Die **Live-Vorschau** rechts daneben rendert Überschriften, Listen und Formatierungen synchron in Echtzeit.
   - Klicke auf **„Unterseite speichern“** -> Erfolgsmeldung.
3. **Neue Unterseite anlegen:**
   - Klicke oben auf **„+ Neue Unterseite anlegen“**.
   - Gib als Titel `Kaminofen & Holz` ein (Slug `kaminofen-holz` wird automatisch erzeugt).
   - Klicke auf **„Unterseite erstellen“**.
   - *Erwartetes Ergebnis:* Die Seite öffnet sich direkt im Editor und ist als Markdown-Datei unter `slft/content/houses/{slug}/subpages/kaminofen-holz.md` angelegt.
4. **Unterseite löschen:**
   - Klicke in der Liste auf **„🗑️ Löschen“** und bestätige. Die Datei wird sicher vom Dateisystem entfernt.

---

### 4.6 Tab 5: Bildergalerie & Drag & Drop Upload

1. Wechsle in den Reiter **„Bildergalerie“**.
2. **Bilder hochladen:**
   - Ziehe eine beliebige Bilddatei (JPEG, PNG oder WebP) per **Drag & Drop** in die gestrichelte Upload-Zone (oder klicke hinein, um eine Datei auszuwählen).
   - *Erwartetes Ergebnis:* Grüner Toast *„Bild erfolgreich hochgeladen!“*. Das Bild erscheint sofort in der Galerie-Rasteransicht.
3. **Markdown-Kopierfunktion:**
   - Klicke bei einem Bild auf den blauen Button **„📋 MD kopieren“**.
   - *Erwartetes Ergebnis:* Toast *„Markdown-Code in Zwischenablage kopiert!“*.
   - Wechsle in den Tab **Unterseiten** und füge mit `Strg + V` (bzw. `Cmd + V`) ein: Es erscheint der korrekte Code, z. B. `![Dateiname](/houses/dangebo/images/mein-bild.jpeg)`.
4. **Bild löschen:**
   - Klicke auf **„🗑️ Löschen“** und bestätige -> Bild verschwindet aus der Galerie.

---

### 4.7 Site-Build & Veröffentlichung („🚀 Veröffentlichen“)

1. Klicke oben rechts in der Kopfleiste auf den grünen Button **„🚀 Veröffentlichen“**.
2. Bestätige das Hinweisfenster mit **„OK“**.
3. Der Button wechselt auf **„⏳ Wird generiert...“**.
4. Nach ca. 1 bis 1,5 Sekunden erscheint der grüne Toast:
   ```
   🚀 Website erfolgreich generiert!
   ```
5. Klicke auf den Button **„🌐 Seite öffnen“**:
   - Die öffentliche Ansicht Ihres Hauses öffnet sich in einem neuen Tab (z. B. `http://localhost:8000/dangebo`).
   - Alle zuvor vorgenommenen Text- und Preisänderungen sind nun live im statischen HTML sichtbar!

---

## 5. Automatisierte Tests ausführen

Alle Backend-Logiken, Validierungen und APIs können ohne manuelles Klicken über die CLI-Testsuite geprüft werden:

### Einzelne Test-Suites ausführen

```bash
# 1. UI-Rendering & Login-View Tests
php slft/tests/test_admin_ui.php

# 2. Authentifizierung, Brute-Force & Session-Sicherheit
php slft/tests/test_admin_auth.php

# 3. Haus-Stammdaten & Unterseiten CRUD-APIs
php slft/tests/test_admin_apis.php

# 4. Bild-Upload, MIME-Prüfung & Galerie
php slft/tests/test_admin_upload.php

# 5. Slowfoot-Integration & Build-Trigger
php slft/tests/test_admin_build.php
```

### Vollständige Gesamtsuite (alle 8 Suiten)

```bash
php slft/tests/test_availability.php && \
php slft/tests/test_ical_sync.php && \
php slft/tests/test_send_inquiry.php && \
php slft/tests/test_admin_auth.php && \
php slft/tests/test_admin_apis.php && \
php slft/tests/test_admin_upload.php && \
php slft/tests/test_admin_ui.php && \
php slft/tests/test_admin_build.php
```

*Erwartetes Ergebnis:* Alle Tests enden mit `🎉 ... erfolgreich bestanden!` und Exit-Code `0`.

---

## 6. Fehleranalyse & Troubleshooting

- **Port 8000 bereits belegt?**
  Prüfe mit `lsof -i :8000`. Falls ein alter Prozess läuft, beende ihn mit `kill <PID>` oder wähle einen anderen Port (z. B. `php -S localhost:8080 slft/router.php`).
- **Passwort vergessen oder Testdaten zurücksetzen?**
  Die Zugangsdaten und Sperrzähler liegen in:
  - Konten: `slft/var/admin_auth.json`
  - Fehlversuche: `slft/var/login_attempts.json`
  Löschen von `login_attempts.json` hebt bestehende Kontosperren sofort auf.
- **Bilder werden im Frontend nicht angezeigt?**
  Stelle sicher, dass der lokale Development-Router [`slft/router.php`](file:///Users/uo/dev/schwedenbutze/slft/router.php) verwendet wird, da dieser URLs der Form `/houses/{slug}/images/*` transparent auf `slft/content/houses/{slug}/images/*` mappt.
