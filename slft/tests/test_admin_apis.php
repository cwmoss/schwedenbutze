<?php

/**
 * End-to-End Test für Admin Backend-APIs (House & Subpages)
 */

echo "🔍 Starte Integrationstests für Backend-APIs (House & Subpages)...\n\n";

function run_api_request(string $script_path, string $method = 'GET', array $query = [], array $body = [], ?array $session_user = null, ?string $csrf = null): array {
    $code = '<?php
    require_once ' . var_export(__DIR__ . '/../admin/auth.php', true) . ';
    slowfoot\admin\auth::start_session();
    ' . ($session_user ? '$_SESSION["admin_user"] = ' . var_export($session_user, true) . ';' : 'unset($_SESSION["admin_user"]);') . '
    ' . ($csrf ? '$_SESSION["csrf_token"] = ' . var_export($csrf, true) . '; $_SERVER["HTTP_X_CSRF_TOKEN"] = ' . var_export($csrf, true) . ';' : '') . '
    $_SERVER["REQUEST_METHOD"] = ' . var_export($method, true) . ';
    $_GET = ' . var_export($query, true) . ';
    $_POST = ' . var_export($body, true) . ';
    register_shutdown_function(function() {
        $c = http_response_code();
        echo "---API_RESULT---" . json_encode([
            "code" => ($c === false || $c === 0) ? 200 : $c
        ]);
    });
    require ' . var_export($script_path, true) . ';
    ';

    $proc = proc_open('php', [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ], $pipes);

    fwrite($pipes[0], $code);
    fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]);
    $err = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);

    $parts = explode("---API_RESULT---", $out);
    $script_output = $parts[0] ?? '';
    $res_json = $parts[1] ?? '{}';
    $decoded = json_decode($res_json, true);

    return [
        'status' => $decoded['code'] ?? 500,
        'body' => json_decode($script_output, true),
        'raw' => $script_output,
        'stderr' => $err
    ];
}

$house_api = __DIR__ . '/../admin/api/house.php';
$subpages_api = __DIR__ . '/../admin/api/subpages.php';
$dangebo_user = [
    'username' => 'dangebo',
    'role' => 'landlord',
    'house_id' => 'dangebo'
];
$ringshult_user = [
    'username' => 'ringshult',
    'role' => 'landlord',
    'house_id' => 'ringshult'
];
$csrf_token = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

// 1. Test: Nicht authentifizierter Zugriff auf house.php
$res1 = run_api_request($house_api, 'GET', ['house' => 'dangebo'], [], null);
if ($res1['status'] !== 401) {
    echo "❌ Test 1: Unauthorisierter Zugriff hat keinen 401-Code zurückgegeben (Status: {$res1['status']})!\n{$res1['raw']}\n";
    exit(1);
}
echo "✅ Test 1: Unauthorisierter Zugriff wird sicher mit 401 abgewiesen.\n";

// 2. Test: Mandantentrennung (Vermieter ringshult darf dangebo nicht aufrufen)
$res2 = run_api_request($house_api, 'GET', ['house' => 'dangebo'], [], $ringshult_user);
if ($res2['status'] !== 403) {
    echo "❌ Test 2: Zugriff auf fremdes Haus wurde nicht mit 403 verweigert (Status: {$res2['status']})!\n{$res2['raw']}\n";
    exit(1);
}
echo "✅ Test 2: Mandantentrennung blockiert fremden Hauszugriff mit 403.\n";

// 3. Test: Berechtigter Zugriff (dangebo auf dangebo)
$res3 = run_api_request($house_api, 'GET', ['house' => 'dangebo'], [], $dangebo_user);
if ($res3['status'] !== 200 || empty($res3['body']['success']) || empty($res3['body']['house'])) {
    echo "❌ Test 3: Berechtigter GET house.php fehlgeschlagen: {$res3['raw']}\n";
    exit(1);
}
$house = $res3['body']['house'];
if ($house['id'] !== 'dangebo' || empty($house['details']['price_per_night_eur'])) {
    echo "❌ Test 3: Unvollständige Hausdaten in house.php Antwort!\n";
    exit(1);
}
echo "✅ Test 3: Hausdaten für Dångebo erfolgreich über GET abgerufen.\n";

// 4. Test: Subpages-Liste für Dångebo
$res4 = run_api_request($subpages_api, 'GET', ['house' => 'dangebo'], [], $dangebo_user);
if ($res4['status'] !== 200 || empty($res4['body']['subpages'])) {
    echo "❌ Test 4: Abruf der Unterseiten fehlgeschlagen: {$res4['raw']}\n";
    exit(1);
}
$subpages = $res4['body']['subpages'];
$subpage_slugs = array_column($subpages, 'short_slug');
if (!in_array('sauna', $subpage_slugs) || !in_array('haus', $subpage_slugs)) {
    echo "❌ Test 4: Erwartete Unterseiten (sauna, haus) nicht in Liste!\n";
    exit(1);
}
echo "✅ Test 4: Unterseiten-Liste für Dångebo erfolgreich abgerufen (" . count($subpages) . " Seiten).\n";

// 5. Test: Einzelne Subpage laden (sauna)
$res5 = run_api_request($subpages_api, 'GET', ['house' => 'dangebo', 'page' => 'sauna'], [], $dangebo_user);
if ($res5['status'] !== 200 || empty($res5['body']['subpage'])) {
    echo "❌ Test 5: Abruf der Unterseite 'sauna' fehlgeschlagen: {$res5['raw']}\n";
    exit(1);
}
$subpage = $res5['body']['subpage'];
if ($subpage['title'] !== 'Die Sauna' || empty($subpage['body'])) {
    echo "❌ Test 5: Inhalt der Unterseite 'sauna' fehlerhaft!\n";
    exit(1);
}
echo "✅ Test 5: Einzelne Unterseite 'sauna' mit Metadaten und Markdown erfolgreich geladen.\n";

// 6. Test: Neue Subpage erstellen (CRUD - Create)
$create_data = [
    'title' => 'Test Boot',
    'short_slug' => 'test-boot',
    'main_image' => 'boot.jpg',
    'excerpt' => 'Ein tolles Ruderboot am See.',
    'body' => "## Unser Ruderboot\n\nLiegt am Steg bereit."
];
$res6 = run_api_request($subpages_api, 'POST', ['house' => 'dangebo'], $create_data, $dangebo_user, $csrf_token);
if ($res6['status'] !== 201 || empty($res6['body']['success'])) {
    echo "❌ Test 6: Anlegen einer neuen Unterseite fehlgeschlagen: {$res6['raw']}\n";
    exit(1);
}
$created_file = __DIR__ . '/../content/houses/dangebo/subpages/test-boot.md';
if (!file_exists($created_file)) {
    echo "❌ Test 6: Datei $created_file wurde auf dem Dateisystem nicht erzeugt!\n";
    exit(1);
}
echo "✅ Test 6: Neue Unterseite 'test-boot' erfolgreich via API angelegt.\n";

// 7. Test: Subpage aktualisieren (CRUD - Update)
$update_data = [
    'title' => 'Test Boot Deluxe',
    'body' => "## Unser Ruderboot Deluxe\n\nInklusive Schwimmwesten und Angeln."
];
$res7 = run_api_request($subpages_api, 'PUT', ['house' => 'dangebo', 'page' => 'test-boot'], $update_data, $dangebo_user, $csrf_token);
if ($res7['status'] !== 200 || empty($res7['body']['success'])) {
    echo "❌ Test 7: Aktualisieren der Unterseite fehlgeschlagen: {$res7['raw']}\n";
    exit(1);
}
$updated_content = file_get_contents($created_file);
if (strpos($updated_content, 'Test Boot Deluxe') === false || strpos($updated_content, 'Schwimmwesten') === false) {
    echo "❌ Test 7: Änderungen wurden nicht in die Markdown-Datei geschrieben!\n";
    exit(1);
}
echo "✅ Test 7: Unterseite erfolgreich aktualisiert und Markdown synchronisiert.\n";

// 8. Test: Subpage löschen (CRUD - Delete)
$res8 = run_api_request($subpages_api, 'DELETE', ['house' => 'dangebo', 'page' => 'test-boot'], [], $dangebo_user, $csrf_token);
if ($res8['status'] !== 200 || empty($res8['body']['success'])) {
    echo "❌ Test 8: Löschen der Unterseite fehlgeschlagen: {$res8['raw']}\n";
    exit(1);
}
if (file_exists($created_file)) {
    echo "❌ Test 8: Datei $created_file existiert immer noch nach dem Löschen!\n";
    exit(1);
}
echo "✅ Test 8: Unterseite erfolgreich gelöscht und Datei vom Dateisystem entfernt.\n";

echo "\n🎉 Alle Integrationstests für Backend-APIs (House & Subpages) erfolgreich bestanden!\n";
