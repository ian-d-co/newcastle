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

$input = json_decode(file_get_contents('php://input'), true);

if (!verifyCsrfToken($input['csrf_token'] ?? '')) {
    jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

try {
    $itemType = $input['item_type'] ?? null;
    $itemId = (int)($input['item_id'] ?? 0);
    $userId = $_SESSION['user_id'];

    $allowedTypes = [
        'activity' => ['item_table' => 'activities', 'booking_table' => 'activity_bookings', 'id_column' => 'activity_id'],
        'meal'     => ['item_table' => 'meals',      'booking_table' => 'meal_bookings',     'id_column' => 'meal_id'],
    ];

    if (!isset($allowedTypes[$itemType])) {
        throw new Exception('Invalid item type');
    }

    if (!$itemId) {
        throw new Exception('Item ID is required');
    }

    $db = getDbConnection();

    $itemTable = $allowedTypes[$itemType]['item_table'];
    $bookingTable = $allowedTypes[$itemType]['booking_table'];
    $idColumn = $allowedTypes[$itemType]['id_column'];

    $stmt = $db->prepare("SELECT id, no_booking_required FROM $itemTable WHERE id = ?");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();

    if (!$item) {
        throw new Exception('Item not found');
    }

    if (!$item['no_booking_required']) {
        throw new Exception('This item requires a booking');
    }

    $stmt = $db->prepare("SELECT id FROM $bookingTable WHERE user_id = ? AND $idColumn = ?");
    $stmt->execute([$userId, $itemId]);

    if ($stmt->fetch()) {
        throw new Exception('Already marked as attending');
    }

    $stmt = $db->prepare("INSERT INTO $bookingTable (user_id, $idColumn, payment_status, created_at) VALUES (?, ?, 'not_required', NOW())");
    $stmt->execute([$userId, $itemId]);

    jsonResponse(['success' => true, 'message' => 'Marked as attending successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
