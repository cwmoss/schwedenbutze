<?php

/**
 * Admin Current Session API Endpoint
 * GET /admin/api/me.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../auth.php';
use slowfoot\admin\auth;

$user = auth::user();

if (!$user) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'logged_in' => false,
        'error' => 'Nicht angemeldet.'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'success' => true,
    'logged_in' => true,
    'user' => $user,
    'csrf_token' => auth::get_csrf_token()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
