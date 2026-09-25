<?php

/**
 * Build-Trigger API Endpoint
 * Führt den Slowfoot Static Site Build aus
 */

require_once __DIR__ . '/../auth.php';

use slowfoot\admin\auth;

header('Content-Type: application/json; charset=utf-8');

// 1. Authentifizierung erforderlich
$user = auth::require_login();

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

// Build-Befehl ausführen
$cmd = escapeshellcmd($slowfoot_bin) . ' build';
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$proc = proc_open($cmd, $descriptors, $pipes, $base_dir);

if (!is_resource($proc)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Prozess konnte nicht gestartet werden.'
    ]);
    exit;
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);

$exit_code = proc_close($proc);

if ($exit_code === 0) {
    $house_id = $user['house_id'] ?? null;
    $preview_link = $house_id ? ('/' . $house_id) : '/';

    echo json_encode([
        'success' => true,
        'message' => 'Website erfolgreich generiert!',
        'built_at' => date('c'),
        'preview_url' => $preview_link,
        'output' => trim($stdout)
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Fehler beim Generieren der Website (Exit Code ' . $exit_code . ')',
        'output' => trim($stdout . "\n" . $stderr)
    ]);
}
