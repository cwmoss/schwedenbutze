# Requirements: iCal / Google-Kalender Synchronisation (REQ-02)

- **Dokument-ID:** REQ-02
- **Status:** Entwurf & Bereit zur Umsetzung
- **Bezug:** Ergänzung zu [REQ-01: Buchungssystem & Multi-Vermieter-Architektur](./01-architektur-buchung-vermieter.md)

---

## 1. Motivation & Zielsetzung

Die Vermieter der Häuser (*Dångebo*, *Ringshult*, *Oksankas Gård*) pflegen ihre Termine im Alltag am liebsten auf dem Smartphone in ihrer gewohnten Kalender-App (z. B. Google Kalender, Apple Kalender oder Outlook).

**Ziel:**
* Die Webseite soll belegte Zeiträume automatisch aus privaten oder öffentlichen **iCal / Google-Kalender-Feeds (`.ics`)** auslesen.
* Wenn ein Vermieter in seinem Handy-Kalender einen Termin einträgt (z. B. *„Belegt: Familie Schmidt“* oder *„Eigennutzung“*), wird dieser Zeitraum **vollautomatisch im Buchungskalender auf der Webseite gesperrt**.
* Der Vermieter muss sich **weder auf einer Webseite einloggen noch Code anfassen**.
* **Dual-Source-Prinzip:** Statische manuelle Sperren (`blocked_dates` in JSON) und dynamische Live-Feeds (`ical_url`) werden nahtlos kombiniert.

---

## 2. Architektur & Datenfluss

```
[ Vermieter Handy / Google Kalender ]
                │
                ▼ (tritt Termin ein)
[ Google Calendar Server (https://.../basic.ics) ]
                │
                ▼ (HTTP GET alle 15 Min)
[ PHP iCal Cache & Parser (slft/lib/ICalParser.php) ]
                │
                ▼ (extrahiert VEVENT -> Start/Enddatum)
[ Availability API (slft/api/availability.php) ]
                │
                ▼ (JSON: disabled_dates)
[ Frontend Belegungskalender (Flatpickr) & Buchungsformular ]
```

---

## 3. Datenmodell-Erweiterung (`houses/*.json`)

Jede Haus-Konfiguration wird um optionale iCal-Quellen erweitert:

```json
{
  "id": "dangebo",
  "title": "Dångebo",
  "slug": "dangebo",
  "active": true,
  "landlord": {
    "name": "Vermieter Dångebo",
    "email": "dangebo@schwedenbutze.de",
    "phone": "+46 123 456789"
  },
  "calendars": {
    "ical_url": "https://calendar.google.com/calendar/ical/example%40gmail.com/private-xxxx/basic.ics",
    "cache_ttl_seconds": 900
  },
  "blocked_dates": [
    {
      "from": "2026-11-01",
      "to": "2026-11-15",
      "note": "Manuelle Winterwartung"
    }
  ]
}
```

> [!NOTE]
> Es können sowohl eine einzelne `ical_url` als auch eine Liste mehrerer Feeds (z. B. Google Kalender + Airbnb iCal) hinterlegt werden.

---

## 4. Technische Spezifikation des iCal-Parsers

### 4.1. iCal RFC 5545 Parsing
Der Parser benötigt keine externen Composer-Abhängigkeiten und unterstützt:
1. **Ganztägige Ereignisse (Standard bei Ferienhaus-Buchungen):**
   * `DTSTART;VALUE=DATE:20260704`
   * `DTEND;VALUE=DATE:20260718`
2. **Uhrzeit-basierte Ereignisse (inkl. UTC / ISO-Timestamps):**
   * `DTSTART:20260704T140000Z`
   * `DTEND:20260718T100000Z`
3. **Abreisetag-Logik:**
   * In iCal ist `DTEND` bei ganztägigen Events exklusiv (der Tag der Abreise).
   * Der Abreisetag eines Gastes kann der Anreisetag des nächsten Gastes sein (Bettenwechsel).
4. **Ausfiltern irrelevanter Events:**
   * Abgesagte Termine (`STATUS:CANCELLED`) werden ignoriert.
   * Vergangene Termine (älter als 30 Tage) werden für die Performance verworfen.

### 4.2. Caching-Strategie & Ausfallsicherheit
* **Cache-Verzeichnis:** `slft/var/cache/ical/{house_id}.json`
* **Cache-Dauer (TTL):** Standardmäßig 15 Minuten (900 Sekunden).
* **Ausfallschutz (Stale-While-Revalidate):** Sollte Google oder der Kalenderserver temporär nicht erreichbar sein oder ein Timeout liefern, liefert das System automatisch den letzten bekannten Cache-Stand aus. Die Webseite bleibt jederzeit erreichbar und schnell.

### 4.3. Kollisionsprüfung bei Buchungsanfragen
Beim Absenden einer Buchungsanfrage (`api/send-inquiry.php`) prüft das Backend serverseitig nicht nur die manuellen `blocked_dates`, sondern auch die per iCal geladenen Belegungen.

---

## 5. Anleitung für Vermieter: Google-Kalender-Link abrufen

Hier ist die einfache Schritt-für-Schritt-Anleitung, wie ein Vermieter den Link aus seinem Google-Konto erhält:

### Schritt 1: Eigenen Kalender für das Ferienhaus erstellen (Empfohlen)
1. Im Browser Google Kalender öffnen: [calendar.google.com](https://calendar.google.com)
2. Links neben **„Weitere Kalender“** auf das **`+`** klicken -> **„Neuen Kalender einrichten“**.
3. Name eingeben (z. B. *„Belegung Ferienhaus Dångebo“*) und auf **„Kalender erstellen“** klicken.

### Schritt 2: Private iCal-Adresse kopieren
1. In der linken Leiste mit der Maus über den neu erstellten Kalender fahren und auf die **drei Punkte `︙`** -> **„Einstellungen und Freigabe“** klicken.
2. Nach ganz unten scrollen zum Abschnitt **„Kalender integrieren“**.
3. Den Link im Feld **„Privatadresse im iCal-Format“** kopieren.
   * *Beispiel:* `https://calendar.google.com/calendar/ical/dein_kalender_id%40group.calendar.google.com/private-abcdef123456/basic.ics`

### Schritt 3: In die Haus-Konfiguration einfügen
* Diese URL wird einmalig in der jeweiligen Konfigurationsdatei (z. B. `slft/content/houses/dangebo.json`) unter `calendars.ical_url` eingetragen.
* **Fertig!** Ab sofort synchronisiert die Schwedenbutze-Webseite alle dort eingetragenen Termine.

> [!TIP]
> **Praxis-Tipp:** Der Vermieter kann diesen Kalender auch auf seinem iPhone / Android-Smartphone in der Kalender-App einblenden und neue Buchungen einfach per Fingertipp eintragen.
