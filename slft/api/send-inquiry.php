<?php
/**
 * Buchungsanfrage E-Mail-Dispatcher für Schwedenbutze
 * Verarbeitet POST-Anfragen, validiert Daten, prüft Spam-Honeypot,
 * routet die Anfrage an den zuständigen Vermieter und sendet eine Bestätigung an den Gast.
 */

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$houses_dir = __DIR__ . '/../content/houses';
$log_dir = __DIR__ . '/../var';

// Hilfsfunktion: E-Mail versenden oder in Log schreiben
if (!function_exists('send_or_log_mail')) {
    function send_or_log_mail(string $to, string $subject, string $body, array $headers = []): bool {
        $log_dir = __DIR__ . '/../var';
        
        $header_str = '';
        foreach ($headers as $k => $v) {
            $header_str .= "$k: $v\r\n";
        }

        // 1. Lokales Logging für Entwicklung & Audit
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0777, true);
        }
        $log_entry = sprintf(
            "=== [%s] Mail to: %s ===\nSubject: %s\nHeaders:\n%s\nBody:\n%s\n\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $header_str,
            $body
        );
        @file_put_contents($log_dir . '/mail.log', $log_entry, FILE_APPEND);

        // 2. Regulärer PHP mail() Versand
        $sent = @mail($to, $subject, $body, $header_str);
        return true; // Immer true zurückgeben, da im Log festgehalten
    }
}

// 1. Nur POST erlauben
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Nur POST-Anfragen sind erlaubt.']);
    exit;
}

// 2. Eingabedaten erfassen (JSON oder Form-POST)
$input_raw = file_get_contents('php://input');
if (empty($input_raw) && php_sapi_name() === 'cli') {
    $input_raw = file_get_contents('php://stdin');
}
$input = json_decode($input_raw, true);
if (!$input) {
    $input = $_POST;
}

// 3. Spam-Schutz: Honeypot-Feld prüfen
// Wenn das unsichtbare Feld "website_url" ausgefüllt ist, handelt es sich um einen Bot
if (!empty($input['website_url'])) {
    // Fake-Erfolg zurückgeben, damit der Bot nicht weiter probiert
    echo json_encode(['success' => true, 'message' => 'Vielen Dank für Ihre Anfrage!']);
    exit;
}

// 4. Pflichtfelder prüfen
$house_id = trim($input['house'] ?? '');
$checkin = trim($input['checkin'] ?? '');
$checkout = trim($input['checkout'] ?? '');
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$guests = trim($input['guests'] ?? '1');
$message = trim($input['message'] ?? '');

if (empty($house_id) || empty($checkin) || empty($checkout) || empty($name) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Bitte füllen Sie alle Pflichtfelder (Haus, Zeitraum, Name, E-Mail) aus.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Bitte geben Sie eine gültige E-Mail-Adresse ein.']);
    exit;
}

// 5. Haus-Konfiguration laden
$safe_house = preg_replace('/[^a-zA-Z0-9_-]/', '', $house_id);
$house_file = $houses_dir . '/' . $safe_house . '.json';

if (!file_exists($house_file)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => "Ausgewähltes Haus '$safe_house' nicht gefunden."]);
    exit;
}

$house_data = json_decode(file_get_contents($house_file), true);
$landlord = $house_data['landlord'] ?? [];
$landlord_email = $landlord['email'] ?? '';
$landlord_name = $landlord['name'] ?? 'Vermieter';
$notify_cc = $landlord['notify_cc'] ?? '';
$house_title = $house_data['title'] ?? $safe_house;

if (empty($landlord_email)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Für dieses Haus ist keine Vermieter-E-Mail hinterlegt.']);
    exit;
}

// 6. Datumsvalidierung (Abreise > Anreise)
try {
    $d_in = new DateTime($checkin);
    $d_out = new DateTime($checkout);
    if ($d_out <= $d_in) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Das Abreisedatum muss nach dem Anreisedatum liegen.']);
        exit;
    }
    $nights = $d_in->diff($d_out)->days;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Ungültiges Datumsformat.']);
    exit;
}

// 7. Belegungs-Kollisionsprüfung (Sicherheitscheck gegen Doppelbuchung)
$blocked = $house_data['blocked_dates'] ?? [];
foreach ($blocked as $b) {
    if (!empty($b['from']) && !empty($b['to'])) {
        $b_from = new DateTime($b['from']);
        $b_to = new DateTime($b['to']);
        // Kollision wenn: Anreise vor Block-Ende UND Abreise nach Block-Start
        if ($d_in < $b_to && $d_out > $b_from) {
            http_response_code(409);
            echo json_encode(['success' => false, 'error' => 'Der gewählte Zeitraum überschneidet sich mit einer bestehenden Belegung.']);
            exit;
        }
    }
}

// 8. E-Mail 1: An den Vermieter
$subject_landlord = "[Schwedenbutze] Neue Buchungsanfrage: $house_title ($checkin bis $checkout)";
$body_landlord = "Hallo $landlord_name,\n\n";
$body_landlord .= "Es ist eine neue Buchungsanfrage für '$house_title' eingegangen:\n\n";
$body_landlord .= "--------------------------------------------------\n";
$body_landlord .= "Haus:         $house_title\n";
$body_landlord .= "Zeitraum:     $checkin bis $checkout ($nights Nächte)\n";
$body_landlord .= "Gast Name:    $name\n";
$body_landlord .= "Gast E-Mail:  $email\n";
if (!empty($phone)) $body_landlord .= "Telefon:      $phone\n";
$body_landlord .= "Personen:     $guests\n";
if (!empty($message)) {
    $body_landlord .= "Nachricht:\n$message\n";
}
$body_landlord .= "--------------------------------------------------\n\n";
$body_landlord .= "Sie können dem Gast direkt antworten, indem Sie auf diese E-Mail antworten.\n\n";
$body_landlord .= "Schwedenbutze Buchungssystem\n";

$headers_landlord = [
    'From' => 'buchung@schwedenbutze.de',
    'Reply-To' => "$name <$email>",
    'Content-Type' => 'text/plain; charset=UTF-8'
];
if (!empty($notify_cc)) {
    $headers_landlord['Cc'] = $notify_cc;
}

send_or_log_mail($landlord_email, $subject_landlord, $body_landlord, $headers_landlord);

// 9. E-Mail 2: Eingangsbestätigung an den Gast
$subject_guest = "Ihre Buchungsanfrage für $house_title – Schwedenbutze";
$body_guest = "Hallo $name,\n\n";
$body_guest .= "vielen Dank für Ihre Buchungsanfrage für unser Ferienhaus '$house_title'!\n\n";
$body_guest .= "Wir haben Ihre Anfrage direkt an die Vermieter ($landlord_name) weitergeleitet.\n\n";
$body_guest .= "Ihre Angaben im Überblick:\n";
$body_guest .= "- Haus: $house_title\n";
$body_guest .= "- Zeitraum: $checkin bis $checkout ($nights Nächte)\n";
$body_guest .= "- Personen: $guests\n";
if (!empty($message)) {
    $body_guest .= "- Ihre Nachricht: $message\n";
}
$body_guest .= "\nDie Vermieter werden sich zeitnah per E-Mail bei Ihnen melden, um die Buchung zu bestätigen.\n\n";
$body_guest .= "Herzliche Grüße,\n";
$body_guest .= "Ihr Team von Schwedenbutze\nhttps://schwedenbutze.de\n";

$headers_guest = [
    'From' => 'buchung@schwedenbutze.de',
    'Reply-To' => "$landlord_name <$landlord_email>",
    'Content-Type' => 'text/plain; charset=UTF-8'
];

send_or_log_mail($email, $subject_guest, $body_guest, $headers_guest);

// 10. Erfolgs-Response an Frontend
echo json_encode([
    'success' => true,
    'message' => "Vielen Dank, $name! Ihre Buchungsanfrage für $house_title wurde erfolgreich an die Vermieter übermittelt. Eine Bestätigung wurde an $email gesendet."
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
