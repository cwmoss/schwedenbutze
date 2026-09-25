# Vermieter-Handbuch: Schwedenbutze Web-Admin & CMS

Willkommen im neuen Verwaltungsbereich von **Schwedenbutze**! Mit dieser Web-Oberfläche können Sie alle Daten, Preise, Belegungskalender, Texte, Unterseiten und Bilder für Ihr Ferienhaus eigenständig und ohne technische Vorkenntnisse pflegen.

---

## Inhaltsverzeichnis
1. [Anmeldung & Sicherheit](#1-anmeldung--sicherheit)
2. [Die Benutzeroberfläche im Überblick](#2-die-benutzeroberfläche-im-überblick)
3. [Tab 1: Stammdaten, Preise & Vermieter-Kontakt](#3-tab-1-stammdaten-preise--vermieter-kontakt)
4. [Tab 2: Belegungskalender & manuelle Sperren](#4-tab-2-belegungskalender--manuelle-sperren)
5. [Tab 3: Haus-Hauptseite, Hero & Infoboxen](#5-tab-3-haus-hauptseite-hero--infoboxen)
6. [Tab 4: Unterseiten-CMS (Neue Unterseiten & Markdown)](#6-tab-4-unterseiten-cms-neue-unterseiten--markdown)
7. [Tab 5: Bildergalerie & Drag & Drop Upload](#7-tab-5-bildergalerie--drag--drop-upload)
8. [Änderungen veröffentlichen (Der "🚀 Veröffentlichen"-Button)](#8-änderungen-veröffentlichen-der--veröffentlichen-button)

---

## 1. Anmeldung & Sicherheit

### Aufruf des Admin-Bereichs
Rufen Sie im Browser die folgende Adresse auf:
- **Produktiv:** `https://schwedenbutze.de/admin/`
- **Lokale Entwicklung / Test:** `http://localhost:8000/admin/`

### Zugangsdaten
Geben Sie Ihren zugewiesenen Benutzernamen und Ihr Passwort ein:

| Benutzername | Rolle | Zuständigkeit |
| :--- | :--- | :--- |
| `dangebo` | Vermieter | Haus Dångebo |
| `ringshult` | Vermieter | Haus Ringshult |
| `oksankas-gard` | Vermieter | Haus Oksankas Gård |
| `admin` | Administrator | Alle 3 Häuser |

> [!NOTE]
> **Datensicherheit & Datenschutz:**
> Vermieter-Zugänge sind strikt mandantentrennend geschützt. Sie sehen und bearbeiten ausschließlich Daten, Bilder und Unterseiten Ihres eigenen Ferienhauses. Der Zugriff auf fremde Häuser wird serverseitig abgewiesen.
> Nach 5 fehlerhaften Login-Versuchen wird die Anmeldung für 15 Minuten vorübergehend gesperrt (Brute-Force-Schutz).

---

## 2. Die Benutzeroberfläche im Überblick

Nach erfolgreichem Login sehen Sie den oberen Header (Topbar) und darunter die 5 Arbeitsbereiche:

```
+-----------------------------------------------------------------------------------------------+
| 🇸🇪 Schwedenbutze Admin [LANDLORD]   🏡 Dångebo   👤 Vermieter   [🚀 Veröffentlichen] [🌐 Seite] [Abmelden] |
+-----------------------------------------------------------------------------------------------+
|  [📋 Stammdaten & Preise]  [📅 Kalender]  [🏡 Hauptseite]  [📄 Unterseiten]  [🖼️ Bildergalerie]       |
+-----------------------------------------------------------------------------------------------+
```

- **Haus-Anzeige / Umschalter:** Zeigt Ihr Ferienhaus an. (Super-Admins können hier zwischen allen Häusern wechseln).
- **🚀 Veröffentlichen:** Aktualisiert die statische Webseite für Besucher (siehe [Kapitel 8](#8-änderungen-veröffentlichen-der--veröffentlichen-button)).
- **🌐 Seite öffnen:** Öffnet die öffentliche Ansicht Ihres Hauses in einem neuen Browser-Tab zur Kontrolle.
- **Abmelden:** Beendet die aktuelle Sitzung sicher.

---

## 3. Tab 1: Stammdaten, Preise & Vermieter-Kontakt

In diesem Reiter hinterlegen Sie alle Basisdaten, die für das Buchungsformular und die Preiskalkulation verwendet werden:

### Felder & Einstellungen
1. **Titel des Hauses:** z. B. `Dångebo` oder `Oksankas Gård`.
2. **Kurzbeschreibung (Teaser):** Kurzer Willkommenssatz, der unter der Überschrift erscheint.
3. **Vermieter-Kontakt:**
   - **Name:** z. B. Ihr Vor- und Nachname.
   - **E-Mail-Adresse:** An diese E-Mail-Adresse werden alle Buchungsanfragen der Gäste für Ihr Haus automatisch zugestellt.
4. **Ausstattung & Kapazitäten:**
   - **Maximale Gästeanzahl:** Höchstgrenze für Buchungsanfragen.
   - **Schlafzimmer & Badezimmer:** Werden in den Hausdetails aufgeführt.
5. **Konditionen & Preise:**
   - **Preis pro Nacht (€):** Grundpreis für die Übernachtung.
   - **Endreinigung (€):** Einmalige Pauschale pro Buchung.
   - **Mindestaufenthalt (Nächte):** Buchungsanfragen mit kürzerer Reisedauer werden automatisch abgewiesen.

Klicken Sie nach Änderungen auf **„Stammdaten speichern“**. Ein grüner Hinweis bestätigt das erfolgreiche Speichern.

---

## 4. Tab 2: Belegungskalender & manuelle Sperren

Das Buchungssystem synchronisiert sich automatisch mit Ihrem bestehenden Kalender und erlaubt zusätzlich manuelle Sperren:

### A. iCal-Kalender-Synchronisation (Google / Airbnb / FeWo)
- Tragen Sie im Feld **iCal-Kalender-URL** den geheimen iCal-Link Ihres Google Kalenders oder Ihrer Plattform ein (Format endet meist auf `.ics`).
- Alle Termine aus diesem Kalender werden automatisch stündlich synchronisiert und sperren die entsprechenden Tage im Buchungsformular für Neuanfragen.
- Details zur Einrichtung des Google-Kalenders finden Sie in der separaten Anleitung [`vermieter-anleitung-google-kalender.md`](./vermieter-anleitung-google-kalender.md).

### B. Manuelle Sperrzeiten einpflegen
Möchten Sie Tage für Eigenbedarf, Renovierung oder Freunde reservieren:
1. Wählen Sie im Bereich **„Neuen Zeitraum sperren“** das Startdatum (**Von**) und das Enddatum (**Bis**) über den Kalender-Picker.
2. Geben Sie optional eine Notiz ein (z. B. *„Familienurlaub“* oder *„Dachreparatur“*).
3. Klicken Sie auf **„Zeitraum sperren“**.
4. Der Zeitraum erscheint sofort in der Tabelle und ist ab sofort im Buchungskalender als belegt markiert.

### C. Sperrzeiten aufheben
Klicken Sie in der Tabelle neben dem entsprechenden Zeitraum einfach auf den roten Button **„🗑️ Löschen“**.

---

## 5. Tab 3: Haus-Hauptseite, Hero & Infoboxen

Hier gestalten Sie den Auftritt der Hauptseite Ihres Ferienhauses:

### Hero-Banner & Begrüßung
- **Hero-Bild:** Wählen Sie aus dem Dropdown ein beliebiges Bild aus Ihrer Hausgalerie als großes Titelbild.
- **Teaser:** Die prägnante Botschaft im oberen Kopfbereich.
- **Ausführliche Vorstellung (Fließtext):** Beschreiben Sie das Haus, die Lage am See und die Vorzüge für Urlauber. Hier können Sie Absätze und Formatierungen nutzen.

### Die 6 Highlight-Kacheln (Infoboxen)
Jedes Haus verfügt auf der Startseite über genau 6 Feature-Kacheln. Für jede Kachel können Sie festlegen:
- **Icon:** Auswahl aus beliebten Icons (z. B. 🌲 Baum/Wald, ☀️ Sonne/Sommer, 🏊 See/Wasser, 🐟 Angeln, 🛁 Sauna/Bad, 🥾 Wandern, 🏡 Haus).
- **Überschrift:** z. B. *„Idyllische Sonnenuntergänge“* oder *„Holzsauna am Haus“*.
- **Beschreibungstext:** 1-2 prägnante Sätze zu diesem Highlight.

Klicken Sie auf **„Hauptseite & Hero speichern“**, um die Angaben zu sichern.

---

## 6. Tab 4: Unterseiten-CMS (Neue Unterseiten & Markdown)

Für Ihr Haus können Sie beliebig viele Unterseiten erstellen (z. B. für *„Die Sauna“*, *„Hausgebrauch & Mülltrennung“*, *„Bootsverleih & Angeln“*, *„Geschichte des Hofes“*):

### Bestehende Unterseiten verwalten
In der Tabelle sehen Sie alle existierenden Unterseiten mit Titel, Dateiname, Status und Aktionen:
- **✏️ Bearbeiten:** Öffnet die Seite im visuellen Markdown-Editor.
- **🗑️ Löschen:** Entfernt die Unterseite nach Sicherheitsbestätigung.

### Eine neue Unterseite anlegen
1. Klicken Sie auf den Button **„+ Neue Unterseite anlegen“**.
2. Geben Sie den **Titel** ein (z. B. *„Kaminofen Anleitung“*).
3. Der **Kurz-Slug (Dateiname)** wird automatisch passend vorgeschlagen (z. B. `kaminofen-anleitung`).
4. Klicken Sie auf **„Unterseite erstellen“**.
5. Die Seite öffnet sich direkt zur Bearbeitung.

### Der Markdown-Editor & Toolbar
Im Editor können Sie den Text komfortabel formatieren. Die Toolbar bietet Schnellzugriffe:

| Button | Funktion | Tastatur-Kürzel / Markdown |
| :--- | :--- | :--- |
| **B** | **Fett formatieren** | `**Fetter Text**` |
| *I* | *Kursiv formatieren* | `*Kursiver Text*` |
| **H2** | Große Zwischenüberschrift | `## Überschrift 2` |
| **H3** | Kleine Zwischenüberschrift | `### Überschrift 3` |
| **List** | Aufzählungsliste | `- Erster Punkt` |
| **Link** | Web-Verweis einfügen | `[Link-Text](https://...)` |
| **Img** | Bild einbetten | `![Beschreibung](/houses/.../images/...)` |

### Sofortige Live-Vorschau
Rechts neben dem Textfeld sehen Sie die **Live-Vorschau**. Jede getippte Änderung wird in Echtzeit formatiert dargestellt, sodass Sie das Ergebnis sofort sehen.

### Startseiten-Verknüpfung
Mit dem Schalter **„Auf Haus-Startseite anzeigen (Spotlight-Kachel)“** steuern Sie, ob diese Unterseite als große Feature-Sektion mit Bild und „Mehr erfahren“-Button direkt auf der Startseite Ihres Hauses erscheinen soll.

---

## 7. Tab 5: Bildergalerie & Drag & Drop Upload

Verwalten Sie alle Fotos für Ihr Ferienhaus an einem zentralen Ort:

### Bilder hochladen
1. Öffnen Sie den Tab **„Bildergalerie“**.
2. Ziehen Sie Bilddateien von Ihrem Computer per **Drag & Drop** direkt in die gestrichelte Upload-Zone oder klicken Sie hinein, um Dateien auszuwählen.
3. Erlaubte Formate: **JPEG, PNG, WebP** (bis zu 15 MB Dateigröße).
4. Das System optimiert und skaliert die Bilder automatisch für eine optimale Ladegeschwindigkeit auf Handys und Desktop-Bildschirmen.

### Bilder im Text verwenden (1-Klick Markdown-Kopierbutton)
1. Jedes Bild in der Galerie besitzt den Button **„📋 MD kopieren“**.
2. Ein Klick kopiert den fertigen Markdown-Bildcode direkt in Ihre Zwischenablage (z. B. `![Mein Bild](/houses/dangebo/images/IMG_8208.jpeg)`).
3. Wechseln Sie in den Tab **Unterseiten** und fügen Sie den Code mit `Strg + V` (oder `Cmd + V` auf Mac) an der gewünschten Stelle in Ihren Text ein.

---

## 8. Änderungen veröffentlichen (Der „🚀 Veröffentlichen“-Button)

Schwedenbutze ist eine ultraschnelle, ausfallsichere statische Website. Wenn Sie im Admin-Bereich Änderungen vornehmen, werden diese sofort in Ihren Dateien gespeichert.

Damit die Änderungen auch für **Besucher der öffentlichen Website** sichtbar werden:

1. Klicken Sie oben rechts in der Kopfleiste auf den grünen Button **„🚀 Veröffentlichen“**.
2. Bestätigen Sie den kurzen Dialog mit **„OK“**.
3. Der Button wechselt auf *„⏳ Wird generiert...“*. Das System baut im Hintergrund in weniger als einer Sekunde alle Webseiten neu auf.
4. Ein grüner Hinweis **„🚀 Website erfolgreich generiert!“** signalisiert den erfolgreichen Abschluss.
5. Klicken Sie auf **„🌐 Seite öffnen“**, um Ihr Ferienhaus direkt im Browser anzusehen.

---

*Haben Sie Fragen oder benötigen Sie Unterstützung? Wenden Sie sich jederzeit an den technischen Administrator (`admin`).*
