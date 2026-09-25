<?php

/**
 * Admin Image Upload Handler
 * POST /admin/api/upload.php?house={slug}
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../auth.php';
use slowfoot\admin\auth;

auth::require_login();
auth::require_csrf();

$houses_dir = __DIR__ . '/../../content/houses';

$house_slug = $_GET['house'] ?? $_POST['house'] ?? null;
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

$file_info = $_FILES['image'] ?? $_FILES['file'] ?? null;
if (!$file_info || $file_info['error'] !== UPLOAD_ERR_OK) {
    $error_msg = 'Keine Datei hochgeladen oder Upload-Fehler aufgetreten.';
    if ($file_info && $file_info['error'] === UPLOAD_ERR_INI_SIZE) {
        $error_msg = 'Die Datei überschreitet die maximale Upload-Größe des Servers.';
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $error_msg]);
    exit;
}

// 1. Maximale Dateigröße: 15 MB
$max_size = 15 * 1024 * 1024;
if ($file_info['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Die Datei ist zu groß (maximal 15 MB erlaubt).']);
    exit;
}

// 2. MIME-Type per finfo prüfen (Whitelist: JPEG, PNG, WebP)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($file_info['tmp_name']);

$allowed_mimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

if (!isset($allowed_mimes[$mime_type])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => "Nicht unterstützter Dateityp ($mime_type). Nur JPEG, PNG und WebP sind erlaubt."]);
    exit;
}

// 3. Zielverzeichnis sicherstellen
$images_dir = $houses_dir . '/' . $safe_house . '/images';
if (!is_dir($images_dir)) {
    mkdir($images_dir, 0755, true);
}

// 4. Dateinamen bereinigen
$orig_name = pathinfo($file_info['name'], PATHINFO_FILENAME);
$orig_ext = pathinfo($file_info['name'], PATHINFO_EXTENSION);
if (empty($orig_ext)) {
    $orig_ext = $allowed_mimes[$mime_type];
}

$clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $orig_name);
$clean_name = trim(preg_replace('/_+/', '_', $clean_name), '_');
if (empty($clean_name)) {
    $clean_name = 'image_' . date('Ymd_His');
}

$target_filename = $clean_name . '.' . strtolower($orig_ext);
$target_path = $images_dir . '/' . $target_filename;

// Kollisionsvermeidung: Dateinamen mit Zähler erweitern
$counter = 1;
while (file_exists($target_path)) {
    $target_filename = $clean_name . '_' . $counter . '.' . strtolower($orig_ext);
    $target_path = $images_dir . '/' . $target_filename;
    $counter++;
}

// 5. Datei verschieben
if (!move_uploaded_file($file_info['tmp_name'], $target_path)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Fehler beim Speichern der Bilddatei auf dem Server.']);
    exit;
}

// 6. Abmessungen ermitteln und bei Übergröße optimieren (max. 2000px Breite/Höhe)
$image_dimensions = @getimagesize($target_path);
$width = $image_dimensions[0] ?? 0;
$height = $image_dimensions[1] ?? 0;

if ($width > 2000 || $height > 2000) {
    if (extension_loaded('gd')) {
        $ratio = min(2000 / $width, 2000 / $height);
        $new_width = (int)round($width * $ratio);
        $new_height = (int)round($height * $ratio);

        $src_img = null;
        switch ($mime_type) {
            case 'image/jpeg':
                $src_img = @imagecreatefromjpeg($target_path);
                break;
            case 'image/png':
                $src_img = @imagecreatefrompng($target_path);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $src_img = @imagecreatefromwebp($target_path);
                }
                break;
        }

        if ($src_img) {
            $dst_img = imagecreatetruecolor($new_width, $new_height);
            if ($mime_type === 'image/png' || $mime_type === 'image/webp') {
                imagealphablending($dst_img, false);
                imagesavealpha($dst_img, true);
            }
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

            switch ($mime_type) {
                case 'image/jpeg':
                    imagejpeg($dst_img, $target_path, 88);
                    break;
                case 'image/png':
                    imagepng($dst_img, $target_path, 8);
                    break;
                case 'image/webp':
                    if (function_exists('imagewebp')) {
                        imagewebp($dst_img, $target_path, 88);
                    }
                    break;
            }

            unset($src_img, $dst_img);

            $width = $new_width;
            $height = $new_height;
        }
    }
}

$file_size = filesize($target_path);
$markdown_snippet = "![" . htmlspecialchars($clean_name) . "](" . $target_filename . ")";

echo json_encode([
    'success' => true,
    'message' => 'Bild erfolgreich hochgeladen.',
    'filename' => $target_filename,
    'url' => '/houses/' . $safe_house . '/images/' . $target_filename,
    'markdown' => $markdown_snippet,
    'size_bytes' => $file_size,
    'dimensions' => [
        'width' => $width,
        'height' => $height
    ]
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
