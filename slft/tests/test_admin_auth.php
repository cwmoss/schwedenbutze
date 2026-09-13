<?php

/**
 * Test-Skript für Admin-Authentifizierung und Rollensystem
 */

require_once __DIR__ . '/../admin/auth.php';
use slowfoot\admin\auth;

echo "🔍 Starte Tests für Admin-Authentifizierung & Rollensystem...\n\n";

$var_dir = __DIR__ . '/../var';
$auth_file = $var_dir . '/admin_auth.json';
$attempts_file = $var_dir . '/login_attempts.json';

// Vorbereitung: Alte Test-Dateien sichern/bereinigen
if (file_exists($attempts_file)) {
    unlink($attempts_file);
}

// 1. Test Initialisierung
auth::init();
if (!file_exists($auth_file)) {
    echo "❌ Test 1: admin_auth.json wurde nicht erzeugt!\n";
    exit(1);
}

$users = auth::get_users();
$expected_users = ['admin', 'dangebo', 'ringshult', 'oksankas-gard'];
foreach ($expected_users as $u) {
    if (!isset($users[$u])) {
        echo "❌ Test 1: Benutzer '$u' fehlt in admin_auth.json!\n";
        exit(1);
    }
}
echo "✅ Test 1: Benutzerverwaltung & Passwort-Hashes erfolgreich initialisiert (4 Benutzer).\n";

// 2. Test Fehlgeschlagener Login
$fail_res = auth::login('dangebo', 'FalschesPasswort!');
if ($fail_res['success'] !== false) {
    echo "❌ Test 2: Falsches Passwort wurde fälschlicherweise akzeptiert!\n";
    exit(1);
}
echo "✅ Test 2: Falsches Passwort wurde wie erwartet abgewiesen.\n";

// 3. Test Brute-Force Rate Limiting
for ($i = 0; $i < 5; $i++) {
    auth::login('dangebo', 'FalschesPasswort!');
}
$rate_res = auth::login('dangebo', 'FalschesPasswort!');
if ($rate_res['success'] !== false || strpos($rate_res['error'], 'Zu viele fehlgeschlagene Versuche') === false) {
    echo "❌ Test 3: Rate Limiting hat nach 5 Fehlversuchen nicht gegriffen!\n";
    exit(1);
}
echo "✅ Test 3: Rate-Limiting greift zuverlässig nach 5 Fehlversuchen.\n";

// Rate Limit für weitere Tests zurücksetzen
if (file_exists($attempts_file)) {
    unlink($attempts_file);
}

// 4. Test Erfolgreicher Login für Vermieter (Dångebo)
$ok_res = auth::login('dangebo', 'Dangebo2026!');
if (!$ok_res['success'] || empty($ok_res['user'])) {
    echo "❌ Test 4: Gültiger Login für dangebo ist fehlgeschlagen: " . ($ok_res['error'] ?? '') . "\n";
    exit(1);
}
if ($ok_res['user']['role'] !== 'landlord' || $ok_res['user']['house_id'] !== 'dangebo') {
    echo "❌ Test 4: Benutzerdaten nach Login unvollständig oder fehlerhaft!\n";
    exit(1);
}
echo "✅ Test 4: Vermieter Dångebo erfolgreich angemeldet (Rolle: landlord, Haus: dangebo).\n";

// 5. Test Berechtigungsprüfung (Vermieter darf NUR eigenes Haus)
if (!auth::can_manage_house('dangebo')) {
    echo "❌ Test 5: Vermieter dangebo darf sein eigenes Haus nicht verwalten!\n";
    exit(1);
}
if (auth::can_manage_house('ringshult')) {
    echo "❌ Test 5: Vermieter dangebo darf fremdes Haus ringshult verwalten (Sicherheitslücke)!\n";
    exit(1);
}
if (auth::can_manage_house('oksankas-gard')) {
    echo "❌ Test 5: Vermieter dangebo darf fremdes Haus oksankas-gard verwalten (Sicherheitslücke)!\n";
    exit(1);
}
echo "✅ Test 5: Berechtigungstrennung funktioniert (dangebo darf nur dangebo verwalten).\n";

// 6. Test Super-Admin Berechtigung
auth::logout();
$admin_res = auth::login('admin', 'AdminSchweden2026!');
if (!$admin_res['success'] || $admin_res['user']['role'] !== 'superadmin') {
    echo "❌ Test 6: Super-Admin Login fehlgeschlagen!\n";
    exit(1);
}
if (!auth::can_manage_house('dangebo') || !auth::can_manage_house('ringshult') || !auth::can_manage_house('oksankas-gard')) {
    echo "❌ Test 6: Super-Admin kann nicht alle Häuser verwalten!\n";
    exit(1);
}
echo "✅ Test 6: Super-Admin darf alle Häuser (dangebo, ringshult, oksankas-gard) verwalten.\n";

// 7. Test CSRF Token
$token = auth::get_csrf_token();
if (empty($token) || strlen($token) !== 64) {
    echo "❌ Test 7: Ungültiges CSRF-Token generiert ($token)!\n";
    exit(1);
}
if (!auth::verify_csrf_token($token)) {
    echo "❌ Test 7: CSRF Token Verifikation mit gültigem Token fehlgeschlagen!\n";
    exit(1);
}
if (auth::verify_csrf_token('ungueltiges_token')) {
    echo "❌ Test 7: CSRF Token Verifikation mit ungültigem Token akzeptiert!\n";
    exit(1);
}
echo "✅ Test 7: CSRF-Token Generierung und kryptografische Validierung erfolgreich.\n";

// 8. Test Logout
auth::logout();
if (auth::is_logged_in() || auth::user() !== null) {
    echo "❌ Test 8: Logout hat die Session nicht korrekt zerstört!\n";
    exit(1);
}
echo "✅ Test 8: Logout zerstört Session und Benutzerstatus vollständig.\n";

echo "\n🎉 Alle Tests für Authentifizierung & Rollensystem erfolgreich bestanden!\n";
