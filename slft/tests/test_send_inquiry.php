<?php
/**
 * Test-Skript für den Buchungsanfrage-Dispatcher (send-inquiry.php)
 */

$mail_log = __DIR__ . '/../var/mail.log';
if (file_exists($mail_log)) {
    unlink($mail_log);
}

function run_inquiry(array $payload): array {
    $script = __DIR__ . '/../api/send-inquiry.php';
    $json = json_encode($payload);
    
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];
    
    $process = proc_open("php $script", $descriptors, $pipes, null, [
        'REQUEST_METHOD' => 'POST'
    ]);
    
    if (is_resource($process)) {
        fwrite($pipes[0], $json);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        
        return json_decode($output, true) ?? ['raw' => $output];
    }
    
    return ['success' => false, 'error' => 'Process spawn failed'];
}

echo "🔍 Starte Tests für E-Mail-Dispatcher (send-inquiry.php)...\n\n";

// Test 1: Gültige Anfrage für Dångebo
$res1 = run_inquiry([
    'house' => 'dangebo',
    'checkin' => '2026-09-01',
    'checkout' => '2026-09-08',
    'name' => 'Max Mustermann',
    'email' => 'max@example.com',
    'phone' => '+49 170 1234567',
    'guests' => '4',
    'message' => 'Wir freuen uns sehr auf Schweden!'
]);

if (empty($res1['success'])) {
    echo "❌ Test 1 Fehlgeschlagen: " . json_encode($res1) . "\n";
    exit(1);
}
echo "✅ Test 1 (Gültige Anfrage Dångebo): Erfolgreich verarbeitet.\n";

// Prüfen, ob Mail-Log den Vermieter von Dångebo enthält
$log_content = file_get_contents($mail_log);
if (strpos($log_content, 'dangebo@schwedenbutze.de') === false || strpos($log_content, 'max@example.com') === false) {
    echo "❌ Test 1 Mail-Log enthält nicht die erwartete Vermieter- oder Gastadresse!\n";
    exit(1);
}
echo "✅ Test 1 Mail-Routing: E-Mail an dangebo@schwedenbutze.de mit Reply-To zu max@example.com im Log nachgewiesen.\n";

// Test 2: Gültige Anfrage für Ringshult
$res2 = run_inquiry([
    'house' => 'ringshult',
    'checkin' => '2026-08-16',
    'checkout' => '2026-08-23',
    'name' => 'Erika Musterfrau',
    'email' => 'erika@example.com',
    'guests' => '2'
]);

if (empty($res2['success'])) {
    echo "❌ Test 2 Fehlgeschlagen: " . json_encode($res2) . "\n";
    exit(1);
}
echo "✅ Test 2 (Gültige Anfrage Ringshult): Erfolgreich verarbeitet.\n";

$log_content = file_get_contents($mail_log);
if (strpos($log_content, 'ringshult@schwedenbutze.de') === false) {
    echo "❌ Test 2 Mail-Log enthält nicht die Vermieteradresse von Ringshult!\n";
    exit(1);
}
echo "✅ Test 2 Mail-Routing: E-Mail an ringshult@schwedenbutze.de im Log nachgewiesen.\n";

// Test 3: Honeypot-Spam-Test
$log_size_before = filesize($mail_log);
$res3 = run_inquiry([
    'house' => 'dangebo',
    'checkin' => '2026-09-01',
    'checkout' => '2026-09-08',
    'name' => 'Spam Bot',
    'email' => 'bot@spam.com',
    'website_url' => 'http://spam-link.com' // Honeypot gefüllt
]);
$log_size_after = filesize($mail_log);

if ($log_size_before !== $log_size_after) {
    echo "❌ Test 3 Fehlgeschlagen: Trotz gefülltem Honeypot wurde eine Mail generiert!\n";
    exit(1);
}
echo "✅ Test 3 (Spam Honeypot): Bot wurde lautlos abgewiesen ohne E-Mail-Versand.\n";

// Test 4: Kollisionstest (Belegter Zeitraum)
$res4 = run_inquiry([
    'house' => 'dangebo',
    'checkin' => '2026-07-05',
    'checkout' => '2026-07-10', // Liegt in geblocktem Zeitraum 04.07. - 18.07.
    'name' => 'Kollisions Tester',
    'email' => 'test@example.com'
]);

if (!empty($res4['success'])) {
    echo "❌ Test 4 Fehlgeschlagen: Kollidierende Buchung wurde fälschlicherweise akzeptiert!\n";
    exit(1);
}
echo "✅ Test 4 (Belegungskollision): Buchung für belegte Tage wurde wie erwartet abgelehnt.\n";

echo "\n🎉 Alle E-Mail-Routing- und Validierungs-Tests erfolgreich bestanden!\n";
