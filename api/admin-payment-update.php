<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/middleware/AdminAuth.php';

initSession();
Auth::check();
AdminAuth::check();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

try {
    $bookingId = $input['booking_id'] ?? null;
    $bookingType = $input['booking_type'] ?? null;

    if (!$bookingId || !in_array($bookingType, ['activity', 'meal'])) {
        throw new Exception('Invalid booking ID or type');
    }

    $db = getDbConnection();
    $table = $bookingType === 'activity' ? 'activity_bookings' : 'meal_bookings';

    $fields = [];
    $params = ['id' => (int)$bookingId];

    if (isset($input['payment_status'])) {
        $fields[] = 'payment_status = :payment_status';
        $params['payment_status'] = $input['payment_status'];
    }
    if (isset($input['amount_due'])) {
        $fields[] = 'amount_due = :amount_due';
        $params['amount_due'] = (float)$input['amount_due'];
    }
    if (isset($input['amount_paid'])) {
        $fields[] = 'amount_paid = :amount_paid';
        $params['amount_paid'] = (float)$input['amount_paid'];
    }
    if (isset($input['payment_notes'])) {
        $fields[] = 'payment_notes = :payment_notes';
        $params['payment_notes'] = $input['payment_notes'];
    }

    if (empty($fields)) {
        throw new Exception('No fields to update');
    }

    $sql = "UPDATE $table SET " . implode(', ', $fields) . " WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    jsonResponse(['success' => true, 'message' => 'Payment updated successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
