<?php

/**
 * Admin Login API Endpoint
 * POST /admin/api/login.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../auth.php';
use slowfoot\admin\auth;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Nur POST-Anfragen erlaubt.']);
    exit;
}

$raw_input = file_get_contents('php://input');
$json_data = json_decode($raw_input, true) ?: [];

$username = $json_data['username'] ?? $_POST['username'] ?? '';
$password = $json_data['password'] ?? $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Benutzername und Passwort sind erforderlich.']);
    exit;
}

$result = auth::login($username, $password);

if (!$result['success']) {
    http_response_code(401);
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'user' => $result['user'],
    'csrf_token' => auth::get_csrf_token()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
