<?php

/**
 * Build-Trigger API Endpoint
 * Führt den Slowfoot Static Site Build aus
 */

require_once __DIR__ . '/../auth.php';

use slowfoot\admin\auth;

header('Content-Type: application/json; charset=utf-8');

// 1. Authentifizierung erforderlich
auth::require_login();
$user = auth::user();

// 2. Nur POST-Anfragen zulassen
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Only POST is supported.']);
    exit;
}

// 3. CSRF-Token validieren
auth::require_csrf();

$base_dir = realpath(__DIR__ . '/../..');
$slowfoot_bin = $base_dir . '/vendor/bin/slowfoot';

if (!file_exists($slowfoot_bin)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Slowfoot Executable nicht gefunden: ' . $slowfoot_bin
    ]);
    exit;
}

// Build-Befehl ausführen mit -f (frische Datenübernahme) und stderr-Umleitung
$cmd = 'cd ' . escapeshellarg($base_dir) . ' && ' . escapeshellcmd($slowfoot_bin) . ' build -f 2>&1';
$output_lines = [];
$exit_code = 0;

exec($cmd, $output_lines, $exit_code);
$output_str = implode("\n", $output_lines);

if ($exit_code === 0) {
    $house_id = $user['house_id'] ?? null;
    $preview_link = $house_id ? ('/' . $house_id) : '/';

    // Letzte aussagekräftige Zeilen für das UI-Feedback ermitteln
    $summary_lines = array_slice($output_lines, -5);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Website erfolgreich generiert!',
        'built_at' => date('c'),
        'preview_url' => $preview_link,
        'summary' => implode(' | ', array_map('trim', $summary_lines))
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fehler beim Generieren der Website (Exit Code ' . $exit_code . ')',
        'output' => $output_str
    ]);
}
