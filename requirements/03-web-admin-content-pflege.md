# REQ-03: Web-Admin & Content-Pflege für Vermieter

- **Dokument-ID:** REQ-03
- **Status:** Spezifiziert / Bereit zur Implementierung ([Tasks](./03-tasks-web-admin.md))
- **Zielversion:** Phase 3
- **Bezug:** Baut auf [REQ-01 (Multi-Vermieter-Architektur)](./01-architektur-buchung-vermieter.md) und [REQ-02 (iCal-Synchronisation)](./02-ical-google-calendar-sync.md) auf.

---

## 1. Motivation & Zielsetzung

Bislang erfolgte die Pflege der Webseite über zwei getrennte, teilweise komplexe Wege:
1. **Buchungs- & Vermieterdaten (REQ-01 / REQ-02):** Stammdaten, Preise, Konditionen und Kalendersperren liegen als Flat-Files in `slft/content/houses/{slug}.json` und mussten technisch (per Editor/Git) bearbeitet werden.
2. **Texte, Unterseiten & Bilder:** Wurden historisch über das cloud-basierte **Sanity Studio** gepflegt. Dort erstellten Redakteure Dokumente für die Haus-Hauptseite (`page`) sowie verknüpfte Unterseiten (`post`, z. B. `/dangebo-sauna`, `/ringshult-geschichte`).

**Ziel von REQ-03:**
Ablösung von Sanity Studio für alle Ferienhaus-Inhalte durch ein **leichtgewichtiges, browserbasiertes Web-Admin-Panel** (`/admin`), das speziell auf die Bedürfnisse von Vermieterinnen und Vermietern zugeschnitten ist:
* **Keine Cloud-Abhängigkeiten, keine Drittanbieter-Accounts:** Vermieter benötigen weder ein Sanity- noch ein GitHub-Konto.
* **Alles an einem Ort:** Verwaltung von Buchungsdaten, Preisen und Belegungen gemeinsam mit Texten, Ausstattungsmerkmalen und Fotos.
* **Vollwertiges Unterseiten-Management:** Vermieter können für ihr Haus neue Unterseiten anlegen, formatieren (Überschriften, Listen, Fettung), bebildern und die Darstellung auf der Haus-Startseite steuern.
* **Local-First & Flat-File:** Alle Inhalte werden als strukturierte JSON- und Markdown-Dateien im Dateisystem abgelegt und von Slowfoot statisch generiert.

---

## 2. Rollen- und Authentifizierungskonzept

Das Admin-Panel unterscheidet zwischen zwei Nutzerrollen:

```
                         ┌─────────────────────────────────┐
                         │      Login (/admin/login)       │
                         └────────────────┬────────────────┘
                                          │
                 ┌────────────────────────┴────────────────────────┐
                 ▼                                                 ▼
     [ Haus-Vermieter ]                                     [ Super-Admin ]
  (z. B. Passwort für 'dangebo')                        (Zentrales Admin-Passwort)
                 │                                                 │
                 ▼                                                 ▼
- Sieht NUR das eigene Haus                      - Kann zwischen allen Häusern wechseln
- Bearbeitet eigene Texte & Unterseiten          - Kann neue Häuser anlegen
- Pflegt eigene Preise, iCal & Sperren           - Verwaltet globale Seiteneinstellungen
- Verwaltet eigene Bildergalerie                 - Direkter Re-Build aller Seiten
```

### Sicherheitsmechanismen:
* **Passwort-Speicherung:** Gehashte Passwörter via `password_hash()` (Argon2id bzw. Bcrypt) in einer geschützten Konfigurationsdatei außerhalb des Web-Roots (`slft/var/admin_auth.json`).
* **Session-Handling:** Sichere PHP-Sessions mit `SameSite=Strict`, `HttpOnly` und `Secure`-Flags.
* **CSRF-Schutz:** Jedes Formular und jeder API-Request erfordert ein gültiges CSRF-Token.
* **Brute-Force-Schutz:** Rate-Limiting für Fehlversuche beim Login (IP- & Account-basiert).

---

## 3. Daten- und Dateistruktur (Flat-File & Local-First)

Um Vermietern maximale Flexibilität zu bieten und gleichzeitig 100 % unabhängig von Sanity Cloud zu werden, wird der Content pro Haus modular gegliedert:

```text
slft/content/houses/
├── dangebo/
│   ├── house.json              # Stammdaten, Preise, iCal, Infoboxen, Sections-Reihenfolge
│   ├── subpages/               # Alle redaktionellen Unterseiten des Hauses
│   │   ├── haus.md             # 'Das Haus' (/dangebo-haus)
│   │   ├── sauna.md            # 'Die Sauna' (/dangebo-sauna)
│   │   ├── hausgebrauch.md     # 'Hausgebrauch' (/dangebo-hausgebrauch)
│   │   └── umgebung.md         # 'Umgebung' (/umgebung)
│   └── images/                 # Originale und hochgeladene Bilder dieses Hauses
├── ringshult/
│   ├── house.json
│   ├── subpages/
│   │   ├── nybygget.md
│   │   ├── hausgebrauch.md
│   │   ├── geschichte.md
│   │   └── warum.md
│   └── images/
└── oksankas-gard/
    ├── house.json
    ├── subpages/
    │   ├── strawberries.md
    │   └── our-sustainable-farm-in-smaland.md
    └── images/
```

### Schema `house.json` (Stammdaten, Hauptseite & Layout):
```json
{
  "id": "dangebo",
  "title": "Dångebo",
  "slug": "dangebo",
  "landlord": {
    "name": "Zorro Brunath",
    "email": "dangebo@schwedenbutze.de",
    "phone": "+49 170 1234567"
  },
  "details": {
    "max_guests": 6,
    "bedrooms": 3,
    "bathrooms": 1,
    "min_stay_nights": 3,
    "price_per_night_eur": 95,
    "cleaning_fee_eur": 80
  },
  "hero": {
    "image": "hero.jpg",
    "teaser": "Willkommen in unserem idyllischen Ferienhaus in Småland!",
    "body_markdown": "### Ihr Traumurlaub am See...\n\nUnser größeres Ferienhaus befindet sich..."
  },
  "sections_order": [
    "haus",
    "sauna",
    "hausgebrauch",
    "umgebung"
  ],
  "infoboxes": [
    {
      "icon": "fa-sun",
      "title": "Idyllische Sonnenuntergänge",
      "text": "Genießen Sie atemberaubende Sonnenuntergänge am See Sandsjön."
    },
    {
      "icon": "fa-hot-tub",
      "title": "Holzsauna",
      "text": "Entspannen Sie sich in unserer traditionellen Holzsauna."
    }
  ],
  "calendars": {
    "ical_url": "https://calendar.google.com/calendar/ical/.../basic.ics",
    "cache_ttl_seconds": 900
  },
  "blocked_ranges": [
    {
      "from": "2026-07-04",
      "to": "2026-07-18",
      "note": "Sommerurlaub Fam. Schmidt"
    }
  ]
}
```

### Schema einer Unterseite (`subpages/{slug}.md`):
```markdown
---
title: "Die Sauna"
slug: "dangebo-sauna"
main_image: "sauna-aussen.jpg"
excerpt: "Neu und schon so heiß!"
show_on_frontpage: true
published_at: "2026-01-15T10:00:00Z"
---

Hier kommt ein ausführlicher Text über die Sauna rein.

## Ausstattung
* Traditioneller Holzofen
* Vorraum zum Umziehen
* Blick in den Birkenwald

![Blick aus der Sauna](images/sauna-innen.jpg)
```

---

## 4. Funktionsumfang des Web-Admin (`slft/admin/`)

Das Admin-Interface wird als aufgeräumtes, responsives Dashboard (Single-Page-UI ohne Framework-Overhead) realisiert:

### 1. Stammdaten & Buchungskonditionen
* **Preise & Konditionen:** Nachtpreis, Endreinigung, Mindestaufenthalt, max. Gästeanzahl, Zimmer.
* **Routing & Benachrichtigungen:** Vermieter-Name, Anfrage-E-Mail, Telefonnummer.

### 2. Kalender & Belegungszeiten
* **Google-Kalender:** Hinterlegen und Testen der privaten `.ics`-URL.
* **Manuelle Sperrzeiten:** Belegungen anlegen, editieren, löschen (mit Notiz/Gastname).
* **Übersicht:** Direkte grafische Kalendervorschau aller belegten Tage (iCal + manuell).

### 3. Haus-Hauptseite bearbeiten
* Hero-Banner Bild auswählen/hochladen.
* Teaser-Text und Hauptvorstellung formatieren.
* Feature-Kacheln (`infoboxes`) mit Icon-Auswahl (FontAwesome) pflegen.

### 4. Unterseiten-Verwaltung (Articles / Posts)
* **Übersichtsliste:** Alle Unterseiten des Hauses mit Titel, URL-Slug, Status und Teaser-Bild.
* **Button „+ Neue Unterseite anlegen“**:
  * Titel eingeben (Slug wird automatisch vorgeschlagen).
  * Haupt- / Teaser-Bild hochladen oder aus Galerie wählen.
  * Teaser-Text für den Spotlight-Kasten auf der Hausseite verfassen.
  * **Markdown-Editor:** Formatierter Fließtext mit intuitiver Toolbar (Überschriften, Fett, Kursiv, Listen, Links, Bilder) und Sofortvorschau.
  * Schalter: *„Auf Haus-Startseite anzeigen“* + Reihenfolge festlegen.
* **Aktionen:** Bestehende Unterseiten editieren, duplizieren oder löschen.

### 5. Medien- & Bilderverwaltung
* Drag & Drop Bildupload (JPEG, PNG, WebP).
* Automatische Generierung responsiver Web-Formate und Größen (z. B. `1200x600`, `600x300`).
* Medien-Raster mit Kopier-Button für Bild-Tags in Markdown.

### 6. Speichern, Veröffentlichen & Build-Trigger
* **Auto-Save / Entwurfsmodus:** Schnelles Zwischenspeichern ohne Build-Verzögerung.
* **Button „Änderungen veröffentlichen“:**
  * Schreibt alle JSON- und Markdown-Dateien.
  * Triggert im Hintergrund den `slowfoot build`-Lauf für die betroffenen Seiten.
  * Zeigt dem Nutzer Erfolgsmeldung und direkten Link zur Live-Vorschau.

---

## 5. Technische Architektur & Schnittstellen

```
[ Browser Vermieter ]
         │ HTTPS
         ▼
[ /admin/index.php ] ─── Authentifizierung (Session & Cookie)
         │
         ├── REST API: /admin/api/house.php        (CRUD house.json)
         ├── REST API: /admin/api/subpages.php     (CRUD subpages/*.md)
         ├── REST API: /admin/api/upload.php       (Media-Upload & Resize)
         └── REST API: /admin/api/build.php        (Trigger Slowfoot Generator)
                                 │
                                 ▼
                     [ Filesystem: content/houses/ ]
                                 │
                                 ▼
                     [ Slowfoot Static Site Build ] ──> [ dist/{slug}/index.html ]
```

### Abwärtskompatibilität:
* Die bestehenden Verfügbarkeits- und Buchungs-APIs ([`slft/api/availability.php`](file:///Users/uo/dev/schwedenbutze/slft/api/availability.php) und [`slft/api/send-inquiry.php`](file:///Users/uo/dev/schwedenbutze/slft/api/send-inquiry.php)) werden so aktualisiert, dass sie transparent sowohl `content/houses/{slug}/house.json` als auch die bisherigen Einzelfiles `content/houses/{slug}.json` unterstützen.

---

## 6. Phasenplan & Abgrenzung zu REQ-04

* **REQ-03 (dieses Dokument):**
  * Vollwertiges Web-Admin für alle 3 Ferienhäuser (Dångebo, Ringshult, Oksankas Gård).
  * Pflege von Hausdaten, Preisen, Kalendern und allen Unterseiten/Bildern.
  * Slowfoot-Loader für die lokalen Flat-Files.
* **REQ-04 (Legacy-Cleanup & globale Seiten):**
  * Migration der restlichen globalen Seiten (Startseite `index`, `kontakt`, `ueber`, Navigation, Footer) aus Sanity in statische Flat-Files.
  * Vollständige Entfernung des Sanity-Plugins, des Studio-Ordners und alter Gridsome-Relikte.
