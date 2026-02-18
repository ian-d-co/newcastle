<?php
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/middleware/Auth.php';
require_once __DIR__ . '/../../app/models/Poll.php';

initSession();
Auth::check();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

try {
    $pollModel = new Poll();
    $expired = $pollModel->closeExpired();
    
    jsonResponse(['success' => true, 'expired' => $expired]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
