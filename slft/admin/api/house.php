<?php

/**
 * Admin House API Endpoint
 * GET /admin/api/house.php?house={slug}
 * POST / PUT /admin/api/house.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../auth.php';
use slowfoot\admin\auth;

auth::require_login();

$houses_dir = __DIR__ . '/../../content/houses';
$method = $_SERVER['REQUEST_METHOD'];

// Slug aus Query oder JSON / POST ermitteln
$house_slug = $_GET['house'] ?? null;

if (!$house_slug && ($method === 'POST' || $method === 'PUT')) {
    $raw_input = file_get_contents('php://input');
    $input_data = json_decode($raw_input, true) ?: $_POST;
    $house_slug = $input_data['id'] ?? $input_data['slug'] ?? null;
}

// Fallback für Vermieter auf ihr eigenes Haus
$current_user = auth::user();
if (!$house_slug && $current_user && $current_user['role'] === 'landlord') {
    $house_slug = $current_user['house_id'];
}

if (!$house_slug) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Haus-Kennung (Parameter "house") fehlt.']);
    exit;
}

$safe_slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $house_slug);
auth::require_house($safe_slug);

$house_file = $houses_dir . '/' . $safe_slug . '/house.json';
$legacy_file = $houses_dir . '/' . $safe_slug . '.json';

if (!file_exists($house_file) && file_exists($legacy_file)) {
    $house_file = $legacy_file;
}

if (!file_exists($house_file)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => "Haus '$safe_slug' existiert nicht."]);
    exit;
}

// GET: Hausdaten auslesen
if ($method === 'GET') {
    $data = json_decode(file_get_contents($house_file), true);
    echo json_encode([
        'success' => true,
        'house' => $data
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// POST oder PUT: Hausdaten speichern
if ($method === 'POST' || $method === 'PUT') {
    auth::require_csrf();

    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true);
    if (!$data) {
        $data = $_POST;
    }

    $existing = json_decode(file_get_contents($house_file), true) ?: [];

    // Validierung
    $title = trim($data['title'] ?? $existing['title'] ?? '');
    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Der Haus-Titel darf nicht leer sein.']);
        exit;
    }

    $landlord = $data['landlord'] ?? $existing['landlord'] ?? [];
    $landlord_email = trim($landlord['email'] ?? '');
    if (empty($landlord_email) || !filter_var($landlord_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Bitte geben Sie eine gültige Vermieter-E-Mail-Adresse an.']);
        exit;
    }

    $notify_cc = trim($landlord['notify_cc'] ?? '');
    if (!empty($notify_cc) && !filter_var($notify_cc, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Die CC-Benachrichtigungsadresse ist ungültig.']);
        exit;
    }

    // Details & Preise
    $details = $data['details'] ?? $existing['details'] ?? [];
    $validated_details = [
        'max_guests' => max(1, (int)($details['max_guests'] ?? 1)),
        'bedrooms' => max(0, (int)($details['bedrooms'] ?? 1)),
        'bathrooms' => max(0, (int)($details['bathrooms'] ?? 1)),
        'min_stay_nights' => max(1, (int)($details['min_stay_nights'] ?? 1)),
        'price_per_night_eur' => max(0, (float)($details['price_per_night_eur'] ?? 0)),
        'cleaning_fee_eur' => max(0, (float)($details['cleaning_fee_eur'] ?? 0))
    ];

    // Kalender / iCal
    $calendars = $data['calendars'] ?? $existing['calendars'] ?? [];
    $ical_url = trim($calendars['ical_url'] ?? '');
    if (!empty($ical_url) && !filter_var($ical_url, FILTER_VALIDATE_URL) && !file_exists($ical_url)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Die angegebene iCal-URL ist ungültig.']);
        exit;
    }

    // Gesperrte Zeiträume validieren
    $blocked_dates = [];
    $raw_blocked = $data['blocked_dates'] ?? $existing['blocked_dates'] ?? [];
    if (is_array($raw_blocked)) {
        foreach ($raw_blocked as $range) {
            $from = trim($range['from'] ?? '');
            $to = trim($range['to'] ?? '');
            $note = trim($range['note'] ?? '');
            if (!empty($from) && !empty($to)) {
                $blocked_dates[] = [
                    'from' => $from,
                    'to' => $to,
                    'note' => $note
                ];
            }
        }
    }

    // Gesamtes Datenobjekt zusammenbauen
    $updated_house = [
        'id' => $safe_slug,
        'title' => $title,
        'slug' => $safe_slug,
        'active' => isset($data['active']) ? (bool)$data['active'] : ($existing['active'] ?? true),
        'landlord' => [
            'name' => trim($landlord['name'] ?? ''),
            'email' => $landlord_email,
            'phone' => trim($landlord['phone'] ?? ''),
            'notify_cc' => $notify_cc
        ],
        'calendars' => [
            'ical_url' => $ical_url,
            'cache_ttl_seconds' => (int)($calendars['cache_ttl_seconds'] ?? 300)
        ],
        'blocked_dates' => $blocked_dates,
        'details' => $validated_details,
        'teaser' => trim($data['teaser'] ?? $existing['teaser'] ?? ''),
        'hero' => [
            'image' => trim($data['hero']['image'] ?? $existing['hero']['image'] ?? ''),
            'teaser' => trim($data['hero']['teaser'] ?? $existing['hero']['teaser'] ?? ''),
            'body_markdown' => trim($data['hero']['body_markdown'] ?? $existing['hero']['body_markdown'] ?? '')
        ],
        'infotitle' => trim($data['infotitle'] ?? $existing['infotitle'] ?? ''),
        'infoheadline' => trim($data['infoheadline'] ?? $existing['infoheadline'] ?? ''),
        'infoboxes' => is_array($data['infoboxes'] ?? null) ? $data['infoboxes'] : ($existing['infoboxes'] ?? [])
    ];

    // In modulare Struktur schreiben
    $modular_file = $houses_dir . '/' . $safe_slug . '/house.json';
    $dir = dirname($modular_file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($modular_file, json_encode($updated_house, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    // Für Rückwärtskompatibilität auch Legacy-File aktualisieren, falls vorhanden
    if (file_exists($legacy_file)) {
        file_put_contents($legacy_file, json_encode($updated_house, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Hausdaten erfolgreich gespeichert.',
        'house' => $updated_house
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => "HTTP-Methode $method wird nicht unterstützt."]);
