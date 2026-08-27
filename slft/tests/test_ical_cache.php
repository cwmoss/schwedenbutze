<?php
/**
 * Test-Skript für den iCal-Caching-Layer (ICalCache.php)
 */

require_once __DIR__ . '/../lib/ICalCache.php';

$test_cache_dir = __DIR__ . '/../var/cache/test_ical';
if (is_dir($test_cache_dir)) {
    array_map('unlink', glob($test_cache_dir . '/*'));
}

$fixture_file = __DIR__ . '/fixtures/sample_google_calendar.ics';
if (!is_dir(dirname($fixture_file))) {
    mkdir(dirname($fixture_file), 0777, true);
}

$sample_ics = <<<'ICS'
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Google Inc//Google Calendar 70.9054//EN
BEGIN:VEVENT
DTSTART;VALUE=DATE:20261010
DTEND;VALUE=DATE:20261020
SUMMARY:Herbstferien Familie Braun
STATUS:CONFIRMED
END:VEVENT
END:VCALENDAR
ICS;
file_put_contents($fixture_file, $sample_ics);

echo "🔍 Starte Tests für ICalCache...\n\n";

$cache = new ICalCache($test_cache_dir, 60, 2);

// Test 1: Erstmaliger Abruf (Cache Miss -> Parse -> Cache Write)
$events = $cache->getEvents($fixture_file);

if (count($events) !== 1 || $events[0]['from'] !== '2026-10-10' || $events[0]['to'] !== '2026-10-20') {
    echo "❌ Test 1 Fehlgeschlagen: Events wurden nicht korrekt initial gecacht!\n";
    print_r($events);
    exit(1);
}
echo "✅ Test 1 (Cache Write): Event 2026-10-10 bis 2026-10-20 erfolgreich geladen und gecacht.\n";

// Prüfen, ob Cache-Datei auf der Festplatte existiert
$cache_files = glob($test_cache_dir . '/*.json');
if (empty($cache_files)) {
    echo "❌ Test 1: Keine Cache-Datei auf der Festplatte gefunden!\n";
    exit(1);
}
echo "✅ Test 1 Cache-File: " . basename($cache_files[0]) . " existiert.\n";

// Test 2: Zweiter Abruf (Cache Hit)
// Wir löschen die Fixture-Datei temporär. Der Cache muss trotzdem liefern!
unlink($fixture_file);

$events_cached = $cache->getEvents($fixture_file);
if (count($events_cached) !== 1 || $events_cached[0]['summary'] !== 'Herbstferien Familie Braun') {
    echo "❌ Test 2 Fehlgeschlagen: Cache Hit lieferte falsche Daten nach Löschen der Quelle!\n";
    exit(1);
}
echo "✅ Test 2 (Cache Hit / Stale Fallback): Bei Ausfall der Quelle liefert der Cache zuverlässig weiter.\n";

// Aufräumen
if (file_exists($fixture_file)) unlink($fixture_file);
array_map('unlink', glob($test_cache_dir . '/*'));
@rmdir($test_cache_dir);

echo "\n🎉 ICalCache Tests erfolgreich bestanden!\n";
