<?php
/**
 * Verfügbarkeits-API für Schwedenbutze
 * Liefert die belegten Zeiträume und Einzeltage für ein bestimmtes Haus oder alle Häuser.
 *
 * Aufruf:
 *   GET /api/availability.php?house=dangebo
 *   GET /api/availability.php?house=all
 */

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$houses_dir = __DIR__ . '/../content/houses';

if (!function_exists('expand_date_range')) {
    /**
     * Wandelt einen Zeitraum (from bis to) in ein Array einzelner YYYY-MM-DD Strings um.
     */
    function expand_date_range(string $from, string $to): array {
        $dates = [];
        try {
            $current = new DateTime($from);
            $end = new DateTime($to);
            
            while ($current <= $end) {
                $dates[] = $current->format('Y-m-d');
                $current->modify('+1 day');
            }
        } catch (Exception $e) {
            // Bei fehlerhaftem Datumsformat ignorieren
        }
        return $dates;
    }
}

if (!function_exists('get_house_availability')) {
    /**
     * Lädt und formatiert die Verfügbarkeit eines einzelnen Hauses.
     */
    function get_house_availability(string $file): ?array {
        if (!file_exists($file)) {
            return null;
        }
        
        $raw = file_get_contents($file);
        $data = json_decode($raw, true);
        if (!$data) {
            return null;
        }

        $ranges = $data['blocked_dates'] ?? [];
        $disabled_dates = [];

        foreach ($ranges as $range) {
            if (!empty($range['from']) && !empty($range['to'])) {
                $expanded = expand_date_range($range['from'], $range['to']);
                $disabled_dates = array_merge($disabled_dates, $expanded);
            }
        }

        // Duplikate entfernen und sortieren
        $disabled_dates = array_values(array_unique($disabled_dates));
        sort($disabled_dates);

        return [
            'id' => $data['id'] ?? basename($file, '.json'),
            'title' => $data['title'] ?? '',
            'slug' => $data['slug'] ?? '',
            'details' => $data['details'] ?? [],
            'blocked_ranges' => $ranges,
            'disabled_dates' => $disabled_dates
        ];
    }
}

$house_param = isset($_GET['house']) ? trim($_GET['house']) : '';

// 1. Wenn house=all oder leer: Liste aller Häuser zurückgeben
if ($house_param === 'all' || $house_param === '') {
    $result = [];
    $files = glob($houses_dir . '/*.json');
    foreach ($files as $file) {
        $house_data = get_house_availability($file);
        if ($house_data) {
            $result[$house_data['id']] = $house_data;
        }
    }
    echo json_encode([
        'success' => true,
        'houses' => $result
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. Spezifisches Haus anfragen
// Sanitize input to prevent path traversal
$safe_house = preg_replace('/[^a-zA-Z0-9_-]/', '', $house_param);
$target_file = $houses_dir . '/' . $safe_house . '.json';

$house_data = get_house_availability($target_file);

if (!$house_data) {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'error' => "Haus '$safe_house' wurde nicht gefunden."
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'house' => $house_data
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
