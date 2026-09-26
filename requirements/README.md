# Requirements & Spezifikationen – Schwedenbutze

Dieses Verzeichnis enthält die strukturierten Anforderungsdokumente und Spezifikationen für die Weiterentwicklung und Vereinfachung der Plattform **Schwedenbutze** ([schwedenbutze.de](https://schwedenbutze.de)).

## Übersicht der Entwicklungsstufen & Dokumente

| ID | Dokument | Status | Beschreibung |
| :--- | :--- | :--- | :--- |
| **REQ-01** | [01-architektur-buchung-vermieter.md](./01-architektur-buchung-vermieter.md) | ✅ Umgesetzt | Komplexitätsreduktion, Multi-Vermieter-Konfiguration, Belegungskalender & Buchungsanfrage-Routing ([Tasks](./01-tasks-buchung-kalender.md)) |
| **REQ-02** | [02-ical-google-calendar-sync.md](./02-ical-google-calendar-sync.md) | ✅ Umgesetzt | Automatische Synchronisation mit Google Kalender & iCal Feeds der Vermieter ([Tasks](./02-tasks-ical-sync.md) · [Vermieter-Leitfaden](./vermieter-anleitung-google-kalender.md)) |
| **REQ-03** | [03-web-admin-content-pflege.md](./03-web-admin-content-pflege.md) | ✅ Umgesetzt | Browserbasiertes Web-Admin-Panel für Vermieter (Stammdaten, Preise, Belegungskalender, Unterseiten-Manager & Medienpflege) ([Tasks](./03-tasks-web-admin.md) · [Vermieter-Handbuch](./vermieter-anleitung-web-admin.md) · [Test-Anleitung](./test-anleitung-web-admin.md)) |
| **REQ-04** | *Folgt* (Legacy-Cleanup & Deployment) | ⏳ Geplant | Sanity-Ablösung, Gridsome-Entfernung, vereinfachter CI/CD- und Server-Workflow |
