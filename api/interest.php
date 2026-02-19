<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';

initSession();
Auth::check();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$allowedTypes = ['activity', 'meal', 'hotel_room'];
$allowedLevels = ['interested', 'maybe', 'not_interested'];

if (!isset($data['item_type']) || !in_array($data['item_type'], $allowedTypes)) {
    jsonResponse(['success' => false, 'message' => 'Invalid item type'], 400);
}

if (!isset($data['item_id']) || !is_numeric($data['item_id'])) {
    jsonResponse(['success' => false, 'message' => 'Invalid item ID'], 400);
}

if (!isset($data['interest_level']) || !in_array($data['interest_level'], $allowedLevels)) {
    jsonResponse(['success' => false, 'message' => 'Invalid interest level'], 400);
}

try {
    $db = getDbConnection();
    $userId = getCurrentUserId();

    $stmt = $db->prepare("
        INSERT INTO user_interests (user_id, item_type, item_id, interest_level)
        VALUES (:user_id, :item_type, :item_id, :interest_level)
        ON DUPLICATE KEY UPDATE interest_level = VALUES(interest_level)
    ");

    $stmt->execute([
        'user_id' => $userId,
        'item_type' => $data['item_type'],
        'item_id' => (int)$data['item_id'],
        'interest_level' => $data['interest_level']
    ]);

    jsonResponse(['success' => true, 'message' => 'Interest updated']);
} catch (Exception $e) {
    error_log('Error updating interest: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Failed to update interest'], 500);
}
