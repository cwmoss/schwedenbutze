<?php
/**
 * Standalone RFC 5545 iCal (.ics) Parser für Schwedenbutze
 * Extrahiert Belegungszeiträume (VEVENT) aus Google Kalender, Apple Kalender, Airbnb etc.
 * Ohne externe Composer-Abhängigkeiten.
 */

class ICalParser {
    /**
     * Parst einen iCal (.ics) String in ein Array von Belegungszeiträumen.
     *
     * @param string $ics_content Der rohe .ics Dateiinhalt
     * @return array Liste von ['from' => 'YYYY-MM-DD', 'to' => 'YYYY-MM-DD', 'summary' => string]
     */
    public static function parse(string $ics_content): array {
        $events = [];
        if (empty(trim($ics_content))) {
            return $events;
        }

        // 1. Line Unfolding gemäß RFC 5545 (Zeilenumbrüche gefolgt von Leerzeichen/Tab zusammenführen)
        $ics_content = preg_replace('/\r\n[ \t]|\r[ \t]|\n[ \t]/', '', $ics_content);
        $lines = preg_split('/\r\n|\r|\n/', $ics_content);

        $in_event = false;
        $current_event = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if ($line === 'BEGIN:VEVENT') {
                $in_event = true;
                $current_event = [];
                continue;
            }

            if ($line === 'END:VEVENT') {
                $in_event = false;
                $parsed = self::normalizeEvent($current_event);
                if ($parsed !== null) {
                    $events[] = $parsed;
                }
                continue;
            }

            if ($in_event) {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) {
                    $key = strtoupper(trim($parts[0]));
                    $value = trim($parts[1]);
                    $current_event[$key] = $value;
                }
            }
        }

        return $events;
    }

    /**
     * Normalisiert die rohen Event-Felder zu einem konsistenten Zeitraum.
     */
    private static function normalizeEvent(array $raw): ?array {
        // Ignoriere abgesagte Events
        if (isset($raw['STATUS']) && strtoupper($raw['STATUS']) === 'CANCELLED') {
            return null;
        }

        $summary = $raw['SUMMARY'] ?? 'Belegt';
        $dtstart_raw = null;
        $dtend_raw = null;

        foreach ($raw as $k => $v) {
            if (strpos($k, 'DTSTART') === 0) {
                $dtstart_raw = $v;
            } elseif (strpos($k, 'DTEND') === 0) {
                $dtend_raw = $v;
            }
        }

        if (!$dtstart_raw) {
            return null;
        }

        $from = self::parseIcsDate($dtstart_raw);
        if (!$from) {
            return null;
        }

        $to = null;
        if ($dtend_raw) {
            $to = self::parseIcsDate($dtend_raw);
        }

        // Falls kein DTEND vorhanden ist, auf mindestens 1 Tag setzen
        if (!$to) {
            $to = $from;
        }

        // Wenn DTEND vor oder gleich DTSTART liegt (ungültig), anpassen
        if ($to < $from) {
            $to = $from;
        }

        return [
            'from' => $from,
            'to' => $to,
            'summary' => $summary
        ];
    }

    /**
     * Wandelt iCal-Datumsformate in YYYY-MM-DD um.
     * Unterstützt:
     * - 20260704 (Ganztägig)
     * - 20260704T140000Z (UTC Zeit)
     * - 20260704T140000 (Lokale Zeit)
     */
    public static function parseIcsDate(string $val): ?string {
        $val = trim($val);
        // Format YYYYMMDD...
        if (preg_match('/^(\d{4})(\d{2})(\d{2})/', $val, $m)) {
            $year = $m[1];
            $month = $m[2];
            $day = $m[3];

            if (checkdate((int)$month, (int)$day, (int)$year)) {
                return "$year-$month-$day";
            }
        }
        return null;
    }
}
