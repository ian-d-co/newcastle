<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/middleware/CSRF.php';
require_once __DIR__ . '/../app/models/HotelOccupant.php';
require_once __DIR__ . '/../app/models/Hotel.php';

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

$action   = $input['action'] ?? '';
$userId   = getCurrentUserId();
$model    = new HotelOccupant();

try {
    if ($action === 'invite') {
        $reservationId  = (int)($input['reservation_id'] ?? 0);
        $invitedUserId  = (int)($input['user_id'] ?? 0);
        $occupantNumber = (int)($input['occupant_number'] ?? 2);
        $message        = trim($input['message'] ?? '');

        if (!$reservationId || !$invitedUserId) {
            jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
        }
        if ($invitedUserId === (int)$userId) {
            jsonResponse(['success' => false, 'message' => 'You cannot invite yourself'], 400);
        }
        if ($occupantNumber < 2 || $occupantNumber > 3) {
            jsonResponse(['success' => false, 'message' => 'Invalid occupant number'], 400);
        }

        // Verify that this reservation belongs to the current user
        $hotelModel  = new Hotel();
        $db          = getDbConnection();
        $stmt        = $db->prepare("SELECT user_id, occupancy_type FROM room_reservations WHERE id = :id");
        $stmt->execute(['id' => $reservationId]);
        $reservation = $stmt->fetch();

        if (!$reservation) {
            jsonResponse(['success' => false, 'message' => 'Reservation not found'], 404);
        }
        if ($reservation['user_id'] != $userId) {
            jsonResponse(['success' => false, 'message' => 'Not authorised'], 403);
        }

        // Ensure occupancy type supports the requested occupant number
        $occupancyType = $reservation['occupancy_type'] ?? '';
        if ($occupantNumber === 3 && $occupancyType !== 'triple') {
            jsonResponse(['success' => false, 'message' => 'This room does not support a third occupant'], 400);
        }
        if ($occupantNumber === 2 && !in_array($occupancyType, ['double', 'triple'])) {
            jsonResponse(['success' => false, 'message' => 'This room does not support a second occupant'], 400);
        }

        $id = $model->invite($reservationId, $invitedUserId, $occupantNumber, $userId, $message);
        jsonResponse(['success' => true, 'message' => 'Invitation sent', 'id' => $id]);

    } elseif ($action === 'accept') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Invalid ID'], 400);
        }
        $model->accept($id, $userId);
        jsonResponse(['success' => true, 'message' => 'Invitation accepted']);

    } elseif ($action === 'decline') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Invalid ID'], 400);
        }
        $model->decline($id, $userId);
        jsonResponse(['success' => true, 'message' => 'Invitation declined']);

    } elseif ($action === 'cancel') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Invalid ID'], 400);
        }
        $model->cancel($id, $userId);
        jsonResponse(['success' => true, 'message' => 'Invitation cancelled']);

    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log('Error in hotel-occupant API: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
