<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/models/Poll.php';

initSession();
Auth::check();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!verifyCsrfToken($input['csrf_token'] ?? '')) {
    jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

try {
    $pollId = $input['poll_id'] ?? null;
    $optionIds = $input['option_ids'] ?? [];
    
    if (!$pollId || empty($optionIds)) {
        throw new Exception('Poll ID and options are required');
    }
    
    $pollModel = new Poll();
    $pollModel->vote($pollId, $optionIds, getCurrentUserId());
    
    jsonResponse(['success' => true, 'message' => 'Vote submitted successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
