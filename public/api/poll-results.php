<?php
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/helpers/functions.php';
require_once __DIR__ . '/../../app/middleware/Auth.php';
require_once __DIR__ . '/../../app/models/Poll.php';

initSession();
Auth::check();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

try {
    $pollId = $_GET['poll_id'] ?? null;
    
    if (!$pollId) {
        throw new Exception('Poll ID is required');
    }
    
    $pollModel = new Poll();
    $results = $pollModel->getResults($pollId);
    
    jsonResponse(['success' => true, 'options' => $results]);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
