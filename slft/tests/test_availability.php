<?php
/**
 * Test-Skript für den Verfügbarkeits-API-Endpunkt (availability.php)
 */

$_SERVER['REQUEST_METHOD'] = 'GET';

// 1. Test Dångebo
$_GET['house'] = 'dangebo';
ob_start();
require __DIR__ . '/../api/availability.php';
$output = ob_get_clean();

$data = json_decode($output, true);

if (!$data || empty($data['success']) || empty($data['house'])) {
    echo "❌ FEHLER bei Anfrage house=dangebo:\n$output\n";
    exit(1);
}

$disabled = $data['house']['disabled_dates'] ?? [];
if (!in_array('2026-07-04', $disabled) || !in_array('2026-07-18', $disabled)) {
    echo "❌ FEHLER: Erwartete gesperrte Daten nicht in Dångebo-Response gefunden!\n";
    exit(1);
}

echo "✅ Test 1 (Dångebo): " . count($disabled) . " gesperrte Tage erfolgreich ermittelt.\n";

// 2. Test All Houses
$_GET['house'] = 'all';
ob_start();
require __DIR__ . '/../api/availability.php';
$output_all = ob_get_clean();

$data_all = json_decode($output_all, true);
if (!$data_all || empty($data_all['success']) || count($data_all['houses']) < 3) {
    echo "❌ FEHLER bei Anfrage house=all:\n$output_all\n";
    exit(1);
}

echo "✅ Test 2 (Alle Häuser): " . count($data_all['houses']) . " Häuser erfolgreich abgerufen.\n";

echo "\n🎉 Verfügbarkeits-API funktioniert einwandfrei!\n";
