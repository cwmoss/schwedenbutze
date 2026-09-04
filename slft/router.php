<?php
/**
 * Lokaler Development Router für Schwedenbutze
 * Starten mit: php -S localhost:8000 slft/router.php
 * Oder über: make dev
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$base_dir = __DIR__;
$dist_dir = $base_dir . '/dist';

// 1. API-Aufrufe direkt an das PHP-Skript in /api/ leiten
if (strpos($uri, '/api/') === 0) {
    $script = $base_dir . $uri;
    if (file_exists($script) && is_file($script)) {
        require $script;
        return;
    }
}

// 2. Statische Dateien aus dist/ direkt ausliefern (Bilder, CSS, JS, Fonts)
$file_in_dist = $dist_dir . $uri;
if ($uri !== '/' && file_exists($file_in_dist) && is_file($file_in_dist)) {
    // Korrekten MIME-Type setzen
    $ext = pathinfo($file_in_dist, PATHINFO_EXTENSION);
    $mimes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf'
    ];
    if (isset($mimes[$ext])) {
        header("Content-Type: " . $mimes[$ext]);
    }
    readfile($file_in_dist);
    return;
}

// 3. Clean URLs auflösen (z. B. /buchung -> /dist/buchung/index.html)
if (file_exists($file_in_dist . '/index.html')) {
    header("Content-Type: text/html; charset=utf-8");
    readfile($file_in_dist . '/index.html');
    return;
}

// 4. Startseite (/)
if ($uri === '/' && file_exists($dist_dir . '/index.html')) {
    header("Content-Type: text/html; charset=utf-8");
    readfile($dist_dir . '/index.html');
    return;
}

// 404 Fallback
http_response_code(404);
echo "404 Not Found: " . htmlspecialchars($uri);
