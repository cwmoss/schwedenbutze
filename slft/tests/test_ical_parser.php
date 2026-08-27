<?php
/**
 * Test-Skript für den Standalone iCal-Parser (ICalParser.php)
 */

require_once __DIR__ . '/../lib/ICalParser.php';

echo "🔍 Starte Tests für ICalParser...\n\n";

$sample_ics = <<<'ICS'
BEGIN:VCALENDAR
PRODID:-//Google Inc//Google Calendar 70.9054//EN
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:PUBLISH
X-WR-CALNAME:Belegung Dångebo
BEGIN:VEVENT
DTSTART;VALUE=DATE:20260601
DTEND;VALUE=DATE:20260608
DTSTAMP:20260827T170000Z
UID:event-1@google.com
STATUS:CONFIRMED
SUMMARY:Buchung Familie Meier
END:VEVENT
BEGIN:VEVENT
DTSTART:20260715T140000Z
DTEND:20260722T100000Z
UID:event-2@google.com
STATUS:CONFIRMED
SUMMARY:Sommerurlaub
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20260801
DTEND;VALUE=DATE:20260805
UID:event-cancelled@google.com
STATUS:CANCELLED
SUMMARY:Stornierte Buchung
END:VEVENT
END:VCALENDAR
ICS;

$events = ICalParser::parse($sample_ics);

if (count($events) !== 2) {
    echo "❌ FEHLER: Erwartet wurden 2 aktive Events, erhalten: " . count($events) . "\n";
    print_r($events);
    exit(1);
}

// Event 1 Check
if ($events[0]['from'] !== '2026-06-01' || $events[0]['to'] !== '2026-06-08' || $events[0]['summary'] !== 'Buchung Familie Meier') {
    echo "❌ FEHLER in Event 1:\n";
    print_r($events[0]);
    exit(1);
}
echo "✅ Test 1 (Ganztägiges Event): 2026-06-01 bis 2026-06-08 ('Buchung Familie Meier') korrekt geparst.\n";

// Event 2 Check (Timestamp Event)
if ($events[1]['from'] !== '2026-07-15' || $events[1]['to'] !== '2026-07-22') {
    echo "❌ FEHLER in Event 2:\n";
    print_r($events[1]);
    exit(1);
}
echo "✅ Test 2 (Timestamp Event): 2026-07-15 bis 2026-07-22 korrekt geparst.\n";

// Test 3 Check (Storniertes Event ignoriert)
foreach ($events as $ev) {
    if ($ev['summary'] === 'Stornierte Buchung') {
        echo "❌ FEHLER: Storniertes Event wurde nicht ignoriert!\n";
        exit(1);
    }
}
echo "✅ Test 3 (Storniertes Event): STATUS:CANCELLED wurde wie gewünscht ignoriert.\n";

echo "\n🎉 ICalParser Tests erfolgreich bestanden!\n";
