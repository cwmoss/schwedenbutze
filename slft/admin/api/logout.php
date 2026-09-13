<?php

/**
 * Admin Logout API Endpoint
 * POST /admin/api/logout.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../auth.php';
use slowfoot\admin\auth;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Nur POST-Anfragen erlaubt.']);
    exit;
}

auth::logout();

echo json_encode([
    'success' => true,
    'message' => 'Erfolgreich abgemeldet.'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
