<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';

initSession();
Auth::check();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$itemType = $_GET['item_type'] ?? '';
$itemId   = (int)($_GET['item_id'] ?? 0);

$allowedTypes = ['activity', 'meal', 'hotel_room'];

if (!in_array($itemType, $allowedTypes)) {
    jsonResponse(['success' => false, 'message' => 'Invalid item type'], 400);
}

if (!$itemId) {
    jsonResponse(['success' => false, 'message' => 'Invalid item ID'], 400);
}

try {
    $db = getDbConnection();

    $stmt = $db->prepare("
        SELECT ui.interest_level, u.discord_name
        FROM user_interests ui
        JOIN users u ON ui.user_id = u.id
        WHERE ui.item_type = :item_type AND ui.item_id = :item_id
        ORDER BY ui.interest_level, u.discord_name
    ");
    $stmt->execute(['item_type' => $itemType, 'item_id' => $itemId]);
    $rows = $stmt->fetchAll();

    $result = [
        'interested'     => [],
        'maybe'          => [],
        'not_interested' => [],
    ];

    foreach ($rows as $row) {
        $level = $row['interest_level'];
        if (isset($result[$level])) {
            $result[$level][] = isGuestMode() ? 'Hidden' : $row['discord_name'];
        }
    }

    jsonResponse(['success' => true, 'data' => $result]);
} catch (Exception $e) {
    error_log('Error in interest-who API: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
