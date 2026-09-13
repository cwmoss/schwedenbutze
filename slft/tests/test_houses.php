<?php
/**
 * Test-Skript zur Validierung der Haus-Konfigurationen
 */

$houses_dir = __DIR__ . '/../content/houses';
$modular_dirs = glob($houses_dir . '/*', GLOB_ONLYDIR);
$legacy_files = glob($houses_dir . '/*.json');

$all_configs = [];
foreach ($modular_dirs as $dir) {
    $house_json = $dir . '/house.json';
    if (file_exists($house_json)) {
        $all_configs[] = [
            'type' => 'modular',
            'file' => $house_json,
            'dir' => $dir,
            'name' => basename($dir) . '/house.json'
        ];
    }
}
foreach ($legacy_files as $file) {
    $all_configs[] = [
        'type' => 'legacy',
        'file' => $file,
        'dir' => null,
        'name' => basename($file)
    ];
}

if (empty($all_configs)) {
    echo "❌ FEHLER: Keine Haus-Konfigurationsdateien in $houses_dir gefunden!\n";
    exit(1);
}

$errors = 0;
echo "🔍 Validiere " . count($all_configs) . " Haus-Konfigurationen...\n\n";

foreach ($all_configs as $item) {
    $name = $item['name'];
    $content = file_get_contents($item['file']);
    $data = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ $name: Ungültiges JSON! (" . json_last_error_msg() . ")\n";
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
        echo "❌ $name: Fehlende Pflichtfelder: " . implode(', ', $missing) . "\n";
        $errors++;
        continue;
    }

    if (empty($data['landlord']['email']) || !filter_var($data['landlord']['email'], FILTER_VALIDATE_EMAIL)) {
        echo "❌ $name: Ungültige oder fehlende Vermieter-E-Mail!\n";
        $errors++;
        continue;
    }

    $blocked_count = count($data['blocked_dates'] ?? []);
    
    // Wenn modular: Prüfe auch Unterseiten
    $subpages_info = "";
    if ($item['type'] === 'modular' && $item['dir']) {
        $subpages = glob($item['dir'] . '/subpages/*.md');
        $subpage_count = count($subpages);
        $subpages_info = ", Subpages: $subpage_count";
        if ($subpage_count === 0) {
            echo "⚠️ $name: Keine Unterseiten in subpages/ gefunden!\n";
        }
    }

    echo "✅ $name: OK (Haus: {$data['title']}, Vermieter-E-Mail: {$data['landlord']['email']}, Belegte Zeiträume: $blocked_count$subpages_info)\n";
}

echo "\n";
if ($errors === 0) {
    echo "🎉 Alle Haus-Konfigurationen sind syntaktisch korrekt und vollständig!\n";
    exit(0);
} else {
    echo "⚠️ $errors Fehler gefunden.\n";
    exit(1);
}
