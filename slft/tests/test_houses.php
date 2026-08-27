<?php
/**
 * Test-Skript zur Validierung der Haus-Konfigurationen
 */

$houses_dir = __DIR__ . '/../content/houses';
$files = glob($houses_dir . '/*.json');

if (empty($files)) {
    echo "❌ FEHLER: Keine Haus-Konfigurationsdateien in $houses_dir gefunden!\n";
    exit(1);
}

$errors = 0;
echo "🔍 Validiere " . count($files) . " Haus-Konfigurationen...\n\n";

foreach ($files as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    $data = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ $filename: Ungültiges JSON! (" . json_last_error_msg() . ")\n";
        $errors++;
        continue;
    }

    $required_keys = ['id', 'title', 'slug', 'landlord', 'blocked_dates'];
    $missing = [];
    foreach ($required_keys as $key) {
        if (!isset($data[$key])) {
            $missing[] = $key;
        }
    }

    if (!empty($missing)) {
        echo "❌ $filename: Fehlende Pflichtfelder: " . implode(', ', $missing) . "\n";
        $errors++;
        continue;
    }

    if (empty($data['landlord']['email']) || !filter_var($data['landlord']['email'], FILTER_VALIDATE_EMAIL)) {
        echo "❌ $filename: Ungültige oder fehlende Vermieter-E-Mail!\n";
        $errors++;
        continue;
    }

    $blocked_count = count($data['blocked_dates'] ?? []);
    echo "✅ $filename: OK (Haus: {$data['title']}, Vermieter-E-Mail: {$data['landlord']['email']}, Belegte Zeiträume: $blocked_count)\n";
}

echo "\n";
if ($errors === 0) {
    echo "🎉 Alle Haus-Konfigurationen sind syntaktisch korrekt und vollständig!\n";
    exit(0);
} else {
    echo "⚠️ $errors Fehler gefunden.\n";
    exit(1);
}
