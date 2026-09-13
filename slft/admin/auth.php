<?php

/**
 * Admin Authentication & Session Management
 * 
 * Handles user authentication, session security, role-based access control,
 * rate limiting against brute-force attacks, and CSRF token protection.
 */

namespace slowfoot\admin;

class auth {

    private static string $auth_file = __DIR__ . '/../var/admin_auth.json';
    private static string $attempts_file = __DIR__ . '/../var/login_attempts.json';
    private static int $max_attempts = 5;
    private static int $lockout_seconds = 900; // 15 Minuten

    /**
     * Stellt sicher, dass die Benutzer-Konfiguration existiert.
     */
    public static function init(): void {
        $var_dir = dirname(self::$auth_file);
        if (!is_dir($var_dir)) {
            mkdir($var_dir, 0755, true);
        }

        if (!file_exists(self::$auth_file)) {
            $default_users = [
                'admin' => [
                    'username' => 'admin',
                    'name' => 'Super Administrator',
                    'role' => 'superadmin',
                    'house_id' => null,
                    'password_hash' => password_hash('AdminSchweden2026!', PASSWORD_DEFAULT),
                    'created_at' => date('c')
                ],
                'dangebo' => [
                    'username' => 'dangebo',
                    'name' => 'Vermieter Dångebo',
                    'role' => 'landlord',
                    'house_id' => 'dangebo',
                    'password_hash' => password_hash('Dangebo2026!', PASSWORD_DEFAULT),
                    'created_at' => date('c')
                ],
                'ringshult' => [
                    'username' => 'ringshult',
                    'name' => 'Vermieter Ringshult',
                    'role' => 'landlord',
                    'house_id' => 'ringshult',
                    'password_hash' => password_hash('Ringshult2026!', PASSWORD_DEFAULT),
                    'created_at' => date('c')
                ],
                'oksankas-gard' => [
                    'username' => 'oksankas-gard',
                    'name' => 'Vermieter Oksankas Gård',
                    'role' => 'landlord',
                    'house_id' => 'oksankas-gard',
                    'password_hash' => password_hash('Oksankas2026!', PASSWORD_DEFAULT),
                    'created_at' => date('c')
                ]
            ];

            file_put_contents(self::$auth_file, json_encode(['users' => $default_users], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }

    /**
     * Startet eine gehärtete PHP-Session.
     */
    public static function start_session(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        if (!headers_sent()) {
            $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $is_https,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            @ini_set('session.use_strict_mode', '1');
            @ini_set('session.use_only_cookies', '1');
        }

        @session_start();
    }

    /**
     * Lädt alle Benutzerdaten.
     */
    public static function get_users(): array {
        self::init();
        if (!file_exists(self::$auth_file)) {
            return [];
        }
        $data = json_decode(file_get_contents(self::$auth_file), true);
        return $data['users'] ?? [];
    }

    /**
     * Prüft, ob ein Login-Versuch durch Rate-Limiting blockiert ist.
     */
    public static function is_rate_limited(string $identifier): bool {
        if (!file_exists(self::$attempts_file)) {
            return false;
        }

        $attempts = json_decode(file_get_contents(self::$attempts_file), true) ?: [];
        if (!isset($attempts[$identifier])) {
            return false;
        }

        $entry = $attempts[$identifier];
        if (time() - $entry['last_attempt'] > self::$lockout_seconds) {
            unset($attempts[$identifier]);
            file_put_contents(self::$attempts_file, json_encode($attempts));
            return false;
        }

        return $entry['count'] >= self::$max_attempts;
    }

    /**
     * Protokolliert einen fehlgeschlagenen Login-Versuch.
     */
    public static function record_failed_attempt(string $identifier): void {
        $attempts = [];
        if (file_exists(self::$attempts_file)) {
            $attempts = json_decode(file_get_contents(self::$attempts_file), true) ?: [];
        }

        $now = time();
        if (!isset($attempts[$identifier]) || ($now - $attempts[$identifier]['last_attempt'] > self::$lockout_seconds)) {
            $attempts[$identifier] = ['count' => 1, 'last_attempt' => $now];
        } else {
            $attempts[$identifier]['count']++;
            $attempts[$identifier]['last_attempt'] = $now;
        }

        file_put_contents(self::$attempts_file, json_encode($attempts));
    }

    /**
     * Löscht Fehlversuche nach erfolgreichem Login.
     */
    public static function clear_failed_attempts(string $identifier): void {
        if (!file_exists(self::$attempts_file)) {
            return;
        }
        $attempts = json_decode(file_get_contents(self::$attempts_file), true) ?: [];
        if (isset($attempts[$identifier])) {
            unset($attempts[$identifier]);
            file_put_contents(self::$attempts_file, json_encode($attempts));
        }
    }

    /**
     * Führt den Login durch.
     */
    public static function login(string $username, string $password): array {
        self::start_session();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $rate_key = $ip . '_' . strtolower(trim($username));

        if (self::is_rate_limited($rate_key)) {
            return [
                'success' => false,
                'error' => 'Zu viele fehlgeschlagene Versuche. Bitte warten Sie 15 Minuten vor dem nächsten Versuch.'
            ];
        }

        $users = self::get_users();
        $clean_user = strtolower(trim($username));

        if (!isset($users[$clean_user])) {
            self::record_failed_attempt($rate_key);
            return [
                'success' => false,
                'error' => 'Ungültiger Benutzername oder Passwort.'
            ];
        }

        $user_data = $users[$clean_user];
        if (!password_verify($password, $user_data['password_hash'])) {
            self::record_failed_attempt($rate_key);
            return [
                'success' => false,
                'error' => 'Ungültiger Benutzername oder Passwort.'
            ];
        }

        // Erfolgreicher Login
        self::clear_failed_attempts($rate_key);
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_regenerate_id(true);
        }

        $_SESSION['admin_user'] = [
            'username' => $user_data['username'],
            'name' => $user_data['name'] ?? $user_data['username'],
            'role' => $user_data['role'],
            'house_id' => $user_data['house_id'] ?? null,
            'logged_in_at' => time()
        ];

        // CSRF Token neu generieren
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        return [
            'success' => true,
            'user' => $_SESSION['admin_user']
        ];
    }

    /**
     * Meldet den aktuellen Benutzer ab.
     */
    public static function logout(): void {
        self::start_session();
        $_SESSION = [];
        if (ini_get("session.use_cookies") && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    /**
     * Gibt den aktuell angemeldeten Benutzer zurück.
     */
    public static function user(): ?array {
        self::start_session();
        return $_SESSION['admin_user'] ?? null;
    }

    /**
     * Prüft, ob ein Benutzer eingeloggt ist.
     */
    public static function is_logged_in(): bool {
        return self::user() !== null;
    }

    /**
     * Prüft, ob der angemeldete Benutzer Berechtigung für ein bestimmtes Haus hat.
     */
    public static function can_manage_house(string $house_id): bool {
        $user = self::user();
        if (!$user) return false;

        // Super-Admin darf alle Häuser verwalten
        if ($user['role'] === 'superadmin') {
            return true;
        }

        // Vermieter darf ausschließlich sein zugewiesenes Haus verwalten
        if ($user['role'] === 'landlord' && !empty($user['house_id'])) {
            return $user['house_id'] === $house_id;
        }

        return false;
    }

    /**
     * Erzwingt Login (liefert 401 für APIs bzw. leitet weiter).
     */
    public static function require_login(): void {
        if (!self::is_logged_in()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => 'Nicht autorisiert. Bitte melden Sie sich an.'
            ]);
            exit;
        }
    }

    /**
     * Erzwingt Haus-Berechtigung.
     */
    public static function require_house(string $house_id): void {
        self::require_login();
        if (!self::can_manage_house($house_id)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'error' => "Zugriff verweigert: Sie haben keine Berechtigung für Haus '$house_id'."
            ]);
            exit;
        }
    }

    /**
     * Liefert das CSRF-Token der aktuellen Session.
     */
    public static function get_csrf_token(): string {
        self::start_session();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verifiziert ein CSRF-Token.
     */
    public static function verify_csrf_token(?string $token): bool {
        self::start_session();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Erzwingt CSRF-Validierung für ändernde HTTP-Methoden.
     */
    public static function require_csrf(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
            if (!self::verify_csrf_token($token)) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error' => 'Ungültiges oder fehlendes CSRF-Sicherheitstoken.'
                ]);
                exit;
            }
        }
    }
}
