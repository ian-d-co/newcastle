<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
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

try {
    $reservationId = $input['reservation_id'] ?? null;
    
    if (!$reservationId) {
        throw new Exception('Reservation ID is required');
    }
    
    $hotelModel = new Hotel();
    
    // CRITICAL: Verify the reservation belongs to the logged-in user
    $userId = $_SESSION['user_id'];
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT user_id FROM room_reservations WHERE id = ?");
    $stmt->execute([$reservationId]);
    $reservation = $stmt->fetch();
    
    if (!$reservation) {
        throw new Exception('Reservation not found');
    }
    
    if ($reservation['user_id'] !== (int)$userId) {
        throw new Exception('You can only cancel your own reservations');
    }
    
    // Now actually cancel it
    $hotelModel->cancelReservation($reservationId, $userId);
    
    jsonResponse(['success' => true, 'message' => 'Reservation cancelled successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
