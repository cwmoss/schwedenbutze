# Requirements & Spezifikationen – Schwedenbutze

Dieses Verzeichnis enthält die strukturierten Anforderungsdokumente und Spezifikationen für die Weiterentwicklung und Vereinfachung der Plattform **Schwedenbutze** ([schwedenbutze.de](https://schwedenbutze.de)).

## Übersicht der Entwicklungsstufen & Dokumente

| ID | Dokument | Status | Beschreibung |
| :--- | :--- | :--- | :--- |
| **REQ-01** | [01-architektur-buchung-vermieter.md](./01-architektur-buchung-vermieter.md) | ✅ Umgesetzt | Komplexitätsreduktion, Multi-Vermieter-Konfiguration, Belegungskalender & Buchungsanfrage-Routing ([Tasks](./01-tasks-buchung-kalender.md)) |
| **REQ-02** | [02-ical-google-calendar-sync.md](./02-ical-google-calendar-sync.md) | ✅ Umgesetzt | Automatische Synchronisation mit Google Kalender & iCal Feeds der Vermieter ([Tasks](./02-tasks-ical-sync.md)) |
| **REQ-03** | *Folgt* (Web-Admin & Content-Pflege) | ⏳ Geplant | Spezifikation des browserbasierten Admin-Panels (Sveltia/Flat-File) für Vermieter |
| **REQ-04** | *Folgt* (Legacy-Cleanup & Deployment) | ⏳ Geplant | Sanity-Ablösung, Gridsome-Entfernung, vereinfachter CI/CD- und Server-Workflow |
