<?php

/**
 * Admin Subpages API Endpoint
 * 
 * GET /admin/api/subpages.php?house={slug}                     -> Liste aller Unterseiten
 * GET /admin/api/subpages.php?house={slug}&page={short_slug}    -> Einzelne Unterseite laden
 * POST /admin/api/subpages.php?house={slug}                    -> Neue Unterseite anlegen
 * PUT /admin/api/subpages.php?house={slug}&page={short_slug}   -> Unterseite bearbeiten
 * DELETE /admin/api/subpages.php?house={slug}&page={short_slug}-> Unterseite löschen
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../auth.php';

use slowfoot\admin\auth;
use Symfony\Component\Yaml\Yaml;

auth::require_login();

$houses_dir = __DIR__ . '/../../content/houses';
$method = $_SERVER['REQUEST_METHOD'];

// Helper zum Parsen von Markdown mit YAML Frontmatter
function parse_page_file(string $filepath): ?array {
    if (!file_exists($filepath)) {
        return null;
    }

    $raw = file_get_contents($filepath);
    $pattern = '/^---\s*\n(.*?)\n---\s*\n?(.*)$/s';
    
    $frontmatter = [];
    $body = '';

    if (preg_match($pattern, $raw, $matches)) {
        try {
            $frontmatter = Yaml::parse($matches[1]) ?: [];
        } catch (\Exception $e) {
            $frontmatter = [];
        }
        $body = $matches[2] ?? '';
    } else {
        $body = $raw;
    }

    return [
        'frontmatter' => $frontmatter,
        'body' => $body
    ];
}

// Helper zum Serialisieren von Markdown mit YAML Frontmatter
function render_page_file(array $frontmatter, string $body): string {
    $yaml = Yaml::dump($frontmatter, 2, 2);
    return "---\n" . trim($yaml) . "\n---\n\n" . ltrim($body);
}

// Slug ermitteln
$house_slug = $_GET['house'] ?? null;
if (!$house_slug && ($method === 'POST' || $method === 'PUT')) {
    $raw_input = file_get_contents('php://input');
    $input_data = json_decode($raw_input, true) ?: $_POST;
    $house_slug = $input_data['house'] ?? null;
}

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

$subpages_dir = $houses_dir . '/' . $safe_house . '/subpages';
if (!is_dir($subpages_dir)) {
    mkdir($subpages_dir, 0755, true);
}

$page_param = $_GET['page'] ?? null;

// ==========================================
// 1. GET: Liste oder Einzelne Unterseite
// ==========================================
if ($method === 'GET') {
    if (!empty($page_param)) {
        $safe_page = preg_replace('/[^a-zA-Z0-9_-]/', '', $page_param);
        $file = $subpages_dir . '/' . $safe_page . '.md';

        $parsed = parse_page_file($file);
        if (!$parsed) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => "Unterseite '$safe_page' nicht gefunden."]);
            exit;
        }

        $fm = $parsed['frontmatter'];
        echo json_encode([
            'success' => true,
            'subpage' => [
                'short_slug' => $fm['short_slug'] ?? $safe_page,
                'slug' => $fm['slug'] ?? ($safe_house . '-' . $safe_page),
                'title' => $fm['title'] ?? '',
                'main_image' => $fm['main_image'] ?? '',
                'excerpt' => $fm['excerpt'] ?? '',
                'show_on_frontpage' => (bool)($fm['show_on_frontpage'] ?? true),
                'published_at' => $fm['published_at'] ?? '',
                'body' => $parsed['body']
            ]
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Liste aller Unterseiten
    $files = glob($subpages_dir . '/*.md') ?: [];
    $subpages = [];
    foreach ($files as $f) {
        $short = basename($f, '.md');
        $parsed = parse_page_file($f);
        if ($parsed) {
            $fm = $parsed['frontmatter'];
            $subpages[] = [
                'short_slug' => $fm['short_slug'] ?? $short,
                'slug' => $fm['slug'] ?? ($safe_house . '-' . $short),
                'title' => $fm['title'] ?? $short,
                'main_image' => $fm['main_image'] ?? '',
                'excerpt' => $fm['excerpt'] ?? '',
                'show_on_frontpage' => (bool)($fm['show_on_frontpage'] ?? true),
                'published_at' => $fm['published_at'] ?? ''
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'house' => $safe_house,
        'count' => count($subpages),
        'subpages' => $subpages
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ==========================================
// 2. POST: Neue Unterseite anlegen
// ==========================================
if ($method === 'POST') {
    auth::require_csrf();

    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true) ?: $_POST;

    $title = trim($data['title'] ?? '');
    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Der Titel der Unterseite darf nicht leer sein.']);
        exit;
    }

    $short_slug = trim($data['short_slug'] ?? '');
    if (empty($short_slug)) {
        // Automatisch aus Titel ableiten
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $title);
        $clean = preg_replace('/[^a-zA-Z0-9]/', '-', strtolower($clean));
        $short_slug = trim(preg_replace('/-+/', '-', $clean), '-');
    }
    $safe_short = preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($short_slug));
    if (empty($safe_short)) {
        $safe_short = 'seite-' . substr(md5(uniqid()), 0, 6);
    }

    $target_file = $subpages_dir . '/' . $safe_short . '.md';
    if (file_exists($target_file)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => "Eine Unterseite mit dem Kürzel '$safe_short' existiert bereits."]);
        exit;
    }

    // Globaler Slug (z. B. dangebo-sauna)
    $global_slug = (strpos($safe_short, $safe_house . '-') === 0) ? $safe_short : ($safe_house . '-' . $safe_short);

    $frontmatter = [
        'title' => $title,
        'slug' => $global_slug,
        'short_slug' => $safe_short,
        'main_image' => trim($data['main_image'] ?? ''),
        'excerpt' => trim($data['excerpt'] ?? ''),
        'show_on_frontpage' => isset($data['show_on_frontpage']) ? (bool)$data['show_on_frontpage'] : true,
        'published_at' => $data['published_at'] ?? date('c')
    ];

    $body = $data['body'] ?? '';
    $file_content = render_page_file($frontmatter, $body);

    file_put_contents($target_file, $file_content);

    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Unterseite erfolgreich erstellt.',
        'subpage' => array_merge($frontmatter, ['body' => $body])
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ==========================================
// 3. PUT: Unterseite bearbeiten
// ==========================================
if ($method === 'PUT') {
    auth::require_csrf();

    $raw_input = file_get_contents('php://input');
    $data = json_decode($raw_input, true) ?: $_POST;

    $current_short = $page_param ?: ($data['short_slug'] ?? null);
    if (empty($current_short)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Keine Unterseite zur Bearbeitung angegeben (Parameter "page").']);
        exit;
    }

    $safe_current = preg_replace('/[^a-zA-Z0-9_-]/', '', $current_short);
    $current_file = $subpages_dir . '/' . $safe_current . '.md';

    $parsed = parse_page_file($current_file);
    if (!$parsed) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => "Unterseite '$safe_current' nicht gefunden."]);
        exit;
    }

    $existing_fm = $parsed['frontmatter'];
    $title = trim($data['title'] ?? $existing_fm['title'] ?? '');
    if (empty($title)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Der Titel darf nicht leer sein.']);
        exit;
    }

    // Prüfen ob Slug umbenannt werden soll
    $new_short = !empty($data['new_short_slug']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', strtolower($data['new_short_slug'])) : $safe_current;
    $target_file = $subpages_dir . '/' . $new_short . '.md';

    if ($new_short !== $safe_current && file_exists($target_file)) {
        http_response_code(409);
        echo json_encode(['success' => false, 'error' => "Kürzel '$new_short' existiert bereits."]);
        exit;
    }

    $global_slug = (strpos($new_short, $safe_house . '-') === 0) ? $new_short : ($safe_house . '-' . $new_short);

    $frontmatter = [
        'title' => $title,
        'slug' => $global_slug,
        'short_slug' => $new_short,
        'main_image' => trim($data['main_image'] ?? $existing_fm['main_image'] ?? ''),
        'excerpt' => trim($data['excerpt'] ?? $existing_fm['excerpt'] ?? ''),
        'show_on_frontpage' => isset($data['show_on_frontpage']) ? (bool)$data['show_on_frontpage'] : (bool)($existing_fm['show_on_frontpage'] ?? true),
        'published_at' => $existing_fm['published_at'] ?? date('c')
    ];

    $body = isset($data['body']) ? $data['body'] : $parsed['body'];
    $file_content = render_page_file($frontmatter, $body);

    file_put_contents($target_file, $file_content);

    // Falls umbenannt, alte Datei löschen
    if ($new_short !== $safe_current && file_exists($current_file)) {
        unlink($current_file);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Unterseite erfolgreich aktualisiert.',
        'subpage' => array_merge($frontmatter, ['body' => $body])
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ==========================================
// 4. DELETE: Unterseite löschen
// ==========================================
if ($method === 'DELETE') {
    auth::require_csrf();

    if (empty($page_param)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Keine Unterseite zum Löschen angegeben (Parameter "page").']);
        exit;
    }

    $safe_page = preg_replace('/[^a-zA-Z0-9_-]/', '', $page_param);
    $target_file = $subpages_dir . '/' . $safe_page . '.md';

    if (!file_exists($target_file)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => "Unterseite '$safe_page' existiert nicht."]);
        exit;
    }

    unlink($target_file);

    echo json_encode([
        'success' => true,
        'message' => "Unterseite '$safe_page' wurde erfolgreich gelöscht."
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => "HTTP-Methode $method wird nicht unterstützt."]);
