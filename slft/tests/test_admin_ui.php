<?php

/**
 * End-to-End Test für Admin-Weboberfläche (index.php)
 */

echo "🔍 Starte Tests für Admin-Weboberfläche (index.php)...\n\n";

function render_admin_page(?array $session_user = null): string {
    $code = '<?php
    require_once ' . var_export(__DIR__ . '/../admin/auth.php', true) . ';
    slowfoot\admin\auth::start_session();
    ' . ($session_user ? '$_SESSION["admin_user"] = ' . var_export($session_user, true) . ';' : 'unset($_SESSION["admin_user"]);') . '
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_GET = [];
    ob_start();
    require ' . var_export(__DIR__ . '/../admin/index.php', true) . ';
    echo ob_get_clean();
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

    return $out;
}

// 1. Test: Nicht authentifizierter Besucher sieht Login-Formular
$guest_html = render_admin_page(null);
if (strpos($guest_html, 'id="login-form"') === false || strpos($guest_html, 'csrf_token') === false) {
    echo "❌ Test 1: Login-Formular oder CSRF-Token nicht in unauthentifizierter Ansicht vorhanden!\n";
    exit(1);
}
if (strpos($guest_html, 'class="tabs-nav"') !== false) {
    echo "❌ Test 1: Geschützte Admin-Tabs sind für unauthentifizierte Besucher sichtbar!\n";
    exit(1);
}
echo "✅ Test 1: Unauthentifizierte Besucher sehen ausschließlich das geschützte Login-Formular mit CSRF.\n";

// 2. Test: Vermieter Dångebo sieht Dashboard, 5 Tabs und kein House-Switcher für fremde Häuser
$dangebo_user = [
    'username' => 'dangebo',
    'name' => 'Vermieter Dångebo',
    'role' => 'landlord',
    'house_id' => 'dangebo'
];
$landlord_html = render_admin_page($dangebo_user);

if (strpos($landlord_html, 'class="tabs-nav"') === false) {
    echo "❌ Test 2: Tab-Navigation im Vermieter-Dashboard fehlt!\n";
    exit(1);
}
if (strpos($landlord_html, 'data-tab="tab-details"') === false ||
    strpos($landlord_html, 'data-tab="tab-calendar"') === false ||
    strpos($landlord_html, 'data-tab="tab-hero"') === false ||
    strpos($landlord_html, 'data-tab="tab-subpages"') === false ||
    strpos($landlord_html, 'data-tab="tab-gallery"') === false) {
    echo "❌ Test 2: Nicht alle 5 Tabs im Vermieter-Dashboard vorhanden!\n";
    exit(1);
}
if (strpos($landlord_html, '<select id="house-selector">') !== false) {
    echo "❌ Test 2: Vermieter hat unzulässigen Haus-Umschalter für fremde Häuser!\n";
    exit(1);
}
echo "✅ Test 2: Vermieter sieht alle 5 Tabs (Stammdaten, Kalender, Hauptseite, Unterseiten, Galerie) fest fokussiert auf sein Haus.\n";

// 3. Test: Super-Admin sieht den interaktiven Haus-Umschalter für alle 3 Häuser
$admin_user = [
    'username' => 'admin',
    'name' => 'Super Administrator',
    'role' => 'admin',
    'house_id' => null
];
$admin_html = render_admin_page($admin_user);

if (strpos($admin_html, '<select id="house-selector">') === false ||
    strpos($admin_html, 'value="dangebo"') === false ||
    strpos($admin_html, 'value="ringshult"') === false ||
    strpos($admin_html, 'value="oksankas-gard"') === false) {
    echo "❌ Test 3: Super-Admin Haus-Umschalter fehlt oder ist unvollständig!\n";
    exit(1);
}
echo "✅ Test 3: Super-Admin hat vollen Haus-Umschalter (Dångebo, Ringshult, Oksankas Gård).\n";

echo "\n🎉 Alle Tests für Admin-Weboberfläche (index.php) erfolgreich bestanden!\n";
