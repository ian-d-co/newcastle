<?php
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/helpers/functions.php';
require_once __DIR__ . '/../../app/middleware/Auth.php';
require_once __DIR__ . '/../../app/models/Event.php';

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
    $eventModel = new Event();
    $event = $eventModel->getActive();

    if (!$event || !isset($event['id'])) {
        throw new Exception('No active event found');
    }

    $userId = getCurrentUserId();
    $eventId = $event['id'];

    $db = getDbConnection();
    $db->beginTransaction();

    // Cancel activity bookings
    $stmt = $db->prepare("DELETE FROM activity_bookings WHERE user_id = :user_id AND activity_id IN (SELECT id FROM activities WHERE event_id = :event_id)");
    $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);

    // Cancel meal bookings
    $stmt = $db->prepare("DELETE FROM meal_bookings WHERE user_id = :user_id AND meal_id IN (SELECT id FROM meals WHERE event_id = :event_id)");
    $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);

    // Cancel carshare bookings made by user
    $stmt = $db->prepare("DELETE FROM carshare_bookings WHERE user_id = :user_id AND carshare_offer_id IN (SELECT id FROM carshare_offers WHERE event_id = :event_id)");
    $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);

    // Remove carshare offers by user
    $stmt = $db->prepare("DELETE FROM carshare_offers WHERE user_id = :user_id AND event_id = :event_id");
    $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);

    // Cancel hosting bookings made by user
    $stmt = $db->prepare("DELETE FROM hosting_bookings WHERE user_id = :user_id AND hosting_offer_id IN (SELECT id FROM hosting_offers WHERE event_id = :event_id)");
    $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);

    // Remove hosting offers by user
    $stmt = $db->prepare("DELETE FROM hosting_offers WHERE user_id = :user_id AND event_id = :event_id");
    $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);

    // Cancel hotel reservations
    $stmt = $db->prepare("DELETE FROM room_reservations WHERE user_id = :user_id AND hotel_room_id IN (SELECT hr.id FROM hotel_rooms hr JOIN hotels h ON hr.hotel_id = h.id WHERE h.event_id = :event_id)");
    $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);

    // Cancel attendance
    $eventModel->cancelAttendance($userId, $eventId);

    $db->commit();

    jsonResponse(['success' => true, 'message' => 'Attendance cancelled successfully']);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
