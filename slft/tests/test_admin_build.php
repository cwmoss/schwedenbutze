<?php

/**
 * Integration Test für Slowfoot-Integration & Build-Trigger (Task 6)
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Starte Tests für Slowfoot-Integration & Build-Trigger (Task 6)...\n\n";

function run_build_request(string $method, array $headers = [], ?array $session_user = null, ?string $csrf = null): array {
    $code = '<?php
    require_once ' . var_export(__DIR__ . '/../admin/auth.php', true) . ';
    slowfoot\admin\auth::start_session();
    ' . ($session_user ? '$_SESSION["admin_user"] = ' . var_export($session_user, true) . ';' : 'unset($_SESSION["admin_user"]);') . '
    ' . ($csrf ? '$_SESSION["csrf_token"] = ' . var_export($csrf, true) . ';' : '') . '
    $_SERVER["REQUEST_METHOD"] = ' . var_export($method, true) . ';
    ';

    foreach ($headers as $k => $v) {
        $header_key = 'HTTP_' . strtoupper(str_replace('-', '_', $k));
        $code .= '$_SERVER[' . var_export($header_key, true) . '] = ' . var_export($v, true) . ';' . "\n";
    }

    $code .= '
    ob_start();
    register_shutdown_function(function() {
        $output = ob_get_clean();
        $code = http_response_code();
        $code = ($code === false || $code === 0) ? 200 : $code;
        echo "STATUS_CODE:" . $code . "\n";
        echo "BODY_START\n" . $output;
    });

    require ' . var_export(__DIR__ . '/../admin/api/build.php', true) . ';
    ';

    $proc = proc_open('php', [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ], $pipes);

    fwrite($pipes[0], $code);
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);

    $status = 0;
    $body = '';
    if (preg_match('/STATUS_CODE:(\d+)\s+BODY_START\s*(.*)$/s', $out, $matches)) {
        $status = (int)$matches[1];
        $body = $matches[2];
    }

    $json = json_decode($body, true);
    return ['status' => $status, 'body' => $body, 'json' => $json];
}

// 1. Test: Nicht authentifizierter Zugriff verweigert (401)
$res = run_build_request('POST', []);
if ($res['status'] !== 401) {
    echo "❌ Test 1: Unauthentifizierter Zugriff wurde nicht mit 401 abgelehnt! (Status: {$res['status']})\n";
    exit(1);
}
echo "✅ Test 1: Unauthentifizierter Build-Trigger wird sicher mit 401 abgelehnt.\n";

$dangebo_user = [
    'username' => 'dangebo',
    'name' => 'Vermieter Dångebo',
    'role' => 'landlord',
    'house_id' => 'dangebo'
];

$valid_csrf = '11223344556677889900aabbccddeeff11223344556677889900aabbccddeeff';

// 2. Test: Fehlender oder falscher CSRF-Token (403)
$res = run_build_request('POST', ['X-CSRF-Token' => 'invalid-token'], $dangebo_user, $valid_csrf);
if ($res['status'] !== 403) {
    echo "❌ Test 2: Ungültiger CSRF-Token wurde nicht mit 403 abgewiesen! (Status: {$res['status']})\n";
    exit(1);
}
echo "✅ Test 2: Ungültiger CSRF-Token wird mit 403 abgewiesen.\n";

// 3. Test: GET-Methode nicht erlaubt (405)
$res = run_build_request('GET', ['X-CSRF-Token' => $valid_csrf], $dangebo_user, $valid_csrf);
if ($res['status'] !== 405) {
    echo "❌ Test 3: GET-Methode wurde nicht mit 405 abgewiesen! (Status: {$res['status']})\n";
    exit(1);
}
echo "✅ Test 3: GET-Methode wird korrekt mit 405 Method Not Allowed abgewiesen.\n";

// 4. Test: Erfolgreicher Build-Trigger via POST mit CSRF
$res = run_build_request('POST', ['X-CSRF-Token' => $valid_csrf], $dangebo_user, $valid_csrf);
if ($res['status'] !== 200 || empty($res['json']['success'])) {
    echo "❌ Test 4: Build-Trigger fehlgeschlagen! Status: {$res['status']}, Fehler: " . ($res['json']['error'] ?? $res['body']) . "\n";
    exit(1);
}
if (!isset($res['json']['preview_url']) || $res['json']['preview_url'] !== '/dangebo') {
    echo "❌ Test 4: preview_url im Build-Response fehlt oder ist fehlerhaft!\n";
    exit(1);
}
echo "✅ Test 4: Build-Trigger führt slowfoot build erfolgreich aus und liefert 200 OK mit Preview-URL.\n";

// 5. Test: Flat-File Loader Generator prüft alle Häuser und Unterseiten
require_once __DIR__ . '/../src/lib/flatfile_loader.php';
$conf = \slowfoot\configuration::load(__DIR__ . '/..');
$dummy_db = $conf->get_store();
$docs = iterator_to_array(\slowfoot\loader\houses::load($conf, $dummy_db));

$house_pages = array_filter($docs, fn($d) => ($d['_type'] ?? '') === 'page');
$subpages = array_filter($docs, fn($d) => ($d['_type'] ?? '') === 'post');
$infoboxes = array_filter($docs, fn($d) => ($d['_type'] ?? '') === 'infobox');

if (count($house_pages) < 3) {
    echo "❌ Test 5: Flat-File Loader hat nicht alle 3 Hausseiten gefunden! (Gefunden: " . count($house_pages) . ")\n";
    exit(1);
}
if (count($subpages) < 4) {
    echo "❌ Test 5: Flat-File Loader hat zu wenige Unterseiten geladen! (Gefunden: " . count($subpages) . ")\n";
    exit(1);
}
if (count($infoboxes) < 6) {
    echo "❌ Test 5: Flat-File Loader hat zu wenige Infoboxen geladen! (Gefunden: " . count($infoboxes) . ")\n";
    exit(1);
}

echo "✅ Test 5: Flat-File Loader yieldet erfolgreich alle Hausseiten (" . count($house_pages) . "), Unterseiten (" . count($subpages) . ") und Infoboxen (" . count($infoboxes) . ").\n";

echo "\n🎉 Alle Tests für Slowfoot-Integration & Build-Trigger (Task 6) erfolgreich bestanden!\n";
