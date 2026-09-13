# Vermieter-Leitfaden: Google Kalender mit Schwedenbutze synchronisieren

Dieser Leitfaden richtet sich an alle Vermieter von **Schwedenbutze** (*Dångebo*, *Ringshult*, *Oksankas Gård*).

Er erklärt Schritt für Schritt, wie du deinen gewohnten Handy- oder Google-Kalender mit der Schwedenbutze-Webseite verknüpfst. Sobald der Kalender verknüpft ist, werden eingetragene Belegungszeiträume **vollautomatisch im Belegungskalender der Webseite gesperrt** – ganz ohne manuelles Einloggen oder HTML-/Code-Kenntnisse.

---

## 1. Google-Kalender vorbereiten

### Schritt 1: Eigenen Kalender für das Ferienhaus anlegen
> [!TIP]
> **Empfehlung:** Erstelle einen separaten Kalender nur für dein Ferienhaus (nicht deinen privaten Hauptkalender verwenden). So werden nur Belegungen des Hauses übertragen und private Termine bleiben privat.

1. Öffne im Browser am Computer: [calendar.google.com](https://calendar.google.com)
2. Klicke links neben dem Punkt **„Weitere Kalender“** auf das Plus-Symbol **`+`** und wähle **„Neuen Kalender einrichten“**.
3. Gib als Namen z. B. `Belegung Ferienhaus Dångebo` ein und klicke auf **„Kalender erstellen“**.

---

## 2. Privaten iCal-Link abrufen

### Schritt 2: Den geheimen `.ics`-Link kopieren
Damit die Webseite die Termine abrufen darf, benötigt sie die private iCal-Adresse des Kalenders:

1. Fahre in der linken Kalender-Leiste mit der Maus über deinen neu angelegten Ferienhaus-Kalender.
2. Klicke auf die drei vertikalen Punkte **`︙`** und wähle **„Einstellungen und Freigabe“**.
3. Scrolle auf der Einstellungsseite nach ganz unten zum Abschnitt **„Kalender integrieren“**.
4. Suche das Feld **„Privatadresse im iCal-Format“**.
5. Klicke auf das Kopier-Symbol oder kopiere den gesamten Link in die Zwischenablage.

> [!CAUTION]
> **Wichtig:** Bitte die **Privatadresse im iCal-Format** kopieren (endet auf `.ics`). Nicht die Kalender-ID oder die öffentliche Web-URL kopieren.

*Beispiel für einen gültigen Link:*
```text
https://calendar.google.com/calendar/ical/ihre_kalender_id%40group.calendar.google.com/private-1234567890abcdef/basic.ics
```

---

## 3. Link hinterlegen lassen

Sende den kopierten Link an den Webseiten-Administrator oder trage ihn direkt in die Konfigurationsdatei deines Hauses ein:

* **Datei:** `slft/content/houses/{haus-id}.json`
* **Eintrag:**
```json
"calendars": {
  "ical_url": "https://calendar.google.com/calendar/ical/ihre_kalender_id%40group.calendar.google.com/private-xxxx/basic.ics"
}
```

Sobald der Link hinterlegt ist, ist die Einrichtung abgeschlossen!

---

## 4. Buchungen im Alltag eintragen

Du kannst Buchungen nun direkt in der Kalender-App deines Smartphones (iOS / Android) oder im Web eintragen:

### Ganztägige Termine anlegen:
1. Erstelle einen **ganztägigen Termin** im Ferienhaus-Kalender.
2. Titel beliebig wählen (z. B. `Familie Müller` oder `Belegt`).
3. **Zeitraum wählen:**
   - **Start:** Anreisetag (z. B. Samstag, 04. Juli)
   - **Ende:** Abreisetag (z. B. Samstag, 18. Juli)

### Abreisetag & Bettenwechsel:
* In iCal-Kalendern gilt der Abreisetag standardmäßig als Abreisedatum.
* Neue Gäste können ab dem Nachmittag des Abreisetages anreisen. Das Buchungssystem berücksichtigt diese Logik automatisch.

### Stornierungen:
* Wenn du einen Termin im Google Kalender löschst, wird der Zeitraum auf der Webseite automatisch wieder als frei angezeigt.

---

## 5. Häufige Fragen (FAQ)

### Wie schnell sind Änderungen auf der Webseite sichtbar?
* Die Schwedenbutze-Webseite synchronisiert sich **alle 15 Minuten** mit deinem Google Kalender.
* Kürzlich eingetragene Termine sind in der Regel spätestens nach 15 Minuten im Belegungskalender gesperrt.

### Was passiert bei Ausfällen von Google oder Netzwerkproblemen?
* Das System speichert die Termine in einem lokalen Cache. Selbst wenn Google temporär nicht erreichbar sein sollte, bleibt die Webseite schnell und zeigt die zuletzt bekannten Termine an.

### Können Buchungsanfragen für bereits belegte Termine gestellt werden?
* Nein. Der Buchungskalender im Frontend sperrt belegte Tage optisch. Zudem prüft das Buchungsformular vor dem Absenden der E-Mail nochmals serverseitig, ob eine Terminkollision vorliegt.
