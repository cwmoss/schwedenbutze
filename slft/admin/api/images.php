<?php

/**
 * Admin Images List & Delete API Endpoint
 * GET /admin/api/images.php?house={slug}
 * DELETE /admin/api/images.php?house={slug}&file={filename}
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../auth.php';
use slowfoot\admin\auth;

auth::require_login();

$houses_dir = __DIR__ . '/../../content/houses';
$method = $_SERVER['REQUEST_METHOD'];

$house_slug = $_GET['house'] ?? null;
$current_user = auth::user();
if (!$house_slug && $current_user && $current_user['role'] === 'landlord') {
    $house_slug = $current_user['house_id'];
}

if (!$house_slug) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Haus-Kennung (Parameter "house") fehlt.']);
    exit;
}

$safe_house = preg_replace('/[^a-zA-Z0-9_-]/', '', $house_slug);
auth::require_house($safe_house);

$images_dir = $houses_dir . '/' . $safe_house . '/images';
if (!is_dir($images_dir)) {
    mkdir($images_dir, 0755, true);
}

// 1. GET: Bilder auflisten
if ($method === 'GET') {
    $files = glob($images_dir . '/*.*') ?: [];
    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $images = [];

    foreach ($files as $file) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts)) {
            continue;
        }

        $filename = basename($file);
        $dim = @getimagesize($file);

        $images[] = [
            'filename' => $filename,
            'url' => '/houses/' . $safe_house . '/images/' . $filename,
            'size_bytes' => filesize($file),
            'modified_at' => date('c', filemtime($file)),
            'dimensions' => [
                'width' => $dim[0] ?? 0,
                'height' => $dim[1] ?? 0
            ],
            'markdown' => '![' . pathinfo($filename, PATHINFO_FILENAME) . '](' . $filename . ')'
        ];
    }

    // Nach Änderungsdatum sortieren (neueste zuerst)
    usort($images, fn($a, $b) => strcmp($b['modified_at'], $a['modified_at']));

    echo json_encode([
        'success' => true,
        'house' => $safe_house,
        'count' => count($images),
        'images' => $images
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. DELETE: Bild löschen
if ($method === 'DELETE') {
    auth::require_csrf();

    $file_param = $_GET['file'] ?? null;
    if (empty($file_param)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Kein Dateiname zum Löschen übergeben (Parameter "file").']);
        exit;
    }

    // Path traversal verhindern
    $safe_filename = basename($file_param);
    $target_file = $images_dir . '/' . $safe_filename;

    if (!file_exists($target_file) || !is_file($target_file)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => "Bild '$safe_filename' existiert nicht."]);
        exit;
    }

    unlink($target_file);

    echo json_encode([
        'success' => true,
        'message' => "Bild '$safe_filename' wurde erfolgreich gelöscht."
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => "HTTP-Methode $method wird nicht unterstützt."]);
