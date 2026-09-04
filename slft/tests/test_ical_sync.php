<?php
/**
 * End-to-End Test für Google-Kalender / iCal Synchronisation
 */

require_once __DIR__ . '/../lib/ICalParser.php';
require_once __DIR__ . '/../lib/ICalCache.php';

echo "🔍 Starte End-to-End Integrationstests für Google-Kalender / iCal Sync...\n\n";

$fixture_path = realpath(__DIR__ . '/fixtures/sample_google_calendar.ics');
$houses_dir = __DIR__ . '/../content/houses';
$dangebo_file = $houses_dir . '/dangebo.json';
$original_dangebo_json = file_get_contents($dangebo_file);

// 1. Dångebo temporär mit dem iCal Test-Feed ausstatten
$dangebo_data = json_decode($original_dangebo_json, true);
$dangebo_data['calendars'] = [
    'ical_url' => $fixture_path,
    'cache_ttl_seconds' => 10
];
file_put_contents($dangebo_file, json_encode($dangebo_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

try {
    // 2. Verfügbarkeits-API für Dångebo abfragen
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['house'] = 'dangebo';

    ob_start();
    require __DIR__ . '/../api/availability.php';
    $output = ob_get_clean();

    $res = json_decode($output, true);
    if (!$res || empty($res['success']) || empty($res['house'])) {
        echo "❌ Test 1 Fehlgeschlagen: Konnte Verfügbarkeit nicht abrufen!\n$output\n";
        exit(1);
    }

    $disabled = $res['house']['disabled_dates'] ?? [];

    // Prüfe statische Daten (04.07.2026)
    if (!in_array('2026-07-04', $disabled)) {
        echo "❌ Test 1: Statische Sperre 2026-07-04 fehlt in Verfügbarkeit!\n";
        exit(1);
    }

    // Prüfe iCal-Daten aus Google Calendar Feed (10.10.2026 bis 20.10.2026)
    if (!in_array('2026-10-10', $disabled) || !in_array('2026-10-20', $disabled)) {
        echo "❌ Test 1: Google-Kalender Sperren (10.10. - 20.10.) fehlen in Verfügbarkeit!\n";
        exit(1);
    }

    // Prüfe iCal-Daten (20.12.2026)
    if (!in_array('2026-12-20', $disabled) || !in_array('2026-12-27', $disabled)) {
        echo "❌ Test 1: Google-Kalender Sperren (20.12. - 27.12.) fehlen in Verfügbarkeit!\n";
        exit(1);
    }

    // Prüfe, dass stornierte Termine (01.11.2026) NICHT gesperrt sind
    if (in_array('2026-11-02', $disabled)) {
        echo "❌ Test 1: Stornierter Google-Kalender Termin wurde fälschlicherweise gesperrt!\n";
        exit(1);
    }

    echo "✅ Test 1 (Verfügbarkeits-API): Manuelle Sperren & Google-Kalender Termine erfolgreich zusammengeführt (" . count($disabled) . " Tage gesperrt).\n";

    // 3. Buchungsanfrage-Kollisionsprüfung gegen Google-Kalender-Termin
    $script = __DIR__ . '/../api/send-inquiry.php';
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    // Versuch: Zeitraum buchen, der im Google-Kalender belegt ist (12.10. - 18.10.)
    $collision_payload = json_encode([
        'house' => 'dangebo',
        'checkin' => '2026-10-12',
        'checkout' => '2026-10-18',
        'name' => 'Tester Google Collision',
        'email' => 'collision@example.com'
    ]);

    $proc = proc_open("php $script", $descriptors, $pipes, null, ['REQUEST_METHOD' => 'POST']);
    fwrite($pipes[0], $collision_payload);
    fclose($pipes[0]);
    $collision_out = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);

    $collision_res = json_decode($collision_out, true);
    if (!empty($collision_res['success'])) {
        echo "❌ Test 2 Fehlgeschlagen: Buchungsanfrage trotz Google-Kalender Belegung akzeptiert!\n";
        exit(1);
    }
    echo "✅ Test 2 (Buchungs-Kollision): Anfrage für Google-Kalender-Belegung (12.10.-18.10.) wurde abgewiesen.\n";

    // Versuch: Freien Zeitraum buchen (02.11. - 07.11.)
    $free_payload = json_encode([
        'house' => 'dangebo',
        'checkin' => '2026-11-02',
        'checkout' => '2026-11-07',
        'name' => 'Erfolgreicher Gast',
        'email' => 'gast@example.com'
    ]);

    $proc2 = proc_open("php $script", $descriptors, $pipes2, null, ['REQUEST_METHOD' => 'POST']);
    fwrite($pipes2[0], $free_payload);
    fclose($pipes2[0]);
    $free_out = stream_get_contents($pipes2[1]);
    fclose($pipes2[1]);
    fclose($pipes2[2]);
    proc_close($proc2);

    $free_res = json_decode($free_out, true);
    if (empty($free_res['success'])) {
        echo "❌ Test 3 Fehlgeschlagen: Buchungsanfrage für freien Zeitraum wurde abgelehnt: $free_out\n";
        exit(1);
    }
    echo "✅ Test 3 (Erfolgreiche Buchung): Freier Zeitraum (02.11.-07.11.) erfolgreich angefragt.\n";

} finally {
    // Originalzustand von Dångebo wiederherstellen
    file_put_contents($dangebo_file, $original_dangebo_json);
}

echo "\n🎉 Alle Google-Kalender / iCal Synchronisations-Tests erfolgreich bestanden!\n";
