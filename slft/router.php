<?php
/**
 * Lokaler Development Router für Schwedenbutze
 * Starten mit: php -S localhost:8000 slft/router.php
 * Oder über: make dev
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$base_dir = __DIR__;
$dist_dir = $base_dir . '/dist';

// 1. API-Aufrufe und Admin-Bereich direkt an das jeweilige PHP-Skript leiten
if (strpos($uri, '/api/') === 0 || strpos($uri, '/admin') === 0) {
    $clean_uri = ($uri === '/admin') ? '/admin/index.php' : $uri;
    $script = $base_dir . $clean_uri;
    if (is_dir($script)) {
        $script = rtrim($script, '/') . '/index.php';
    }
    if (file_exists($script) && is_file($script)) {
        require $script;
        return;
    }
}

// 1b. Haus-Bilder aus content/houses/ direkt ausliefern
if (strpos($uri, '/content/houses/') === 0 || strpos($uri, '/houses/') === 0) {
    $rel_path = (strpos($uri, '/houses/') === 0) ? '/content' . $uri : $uri;
    $content_file = $base_dir . $rel_path;
    if (file_exists($content_file) && is_file($content_file)) {
        $ext = strtolower(pathinfo($content_file, PATHINFO_EXTENSION));
        $img_mimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml'
        ];
        if (isset($img_mimes[$ext])) {
            header("Content-Type: " . $img_mimes[$ext]);
            readfile($content_file);
            return;
        }
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
