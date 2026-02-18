<?php
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/middleware/Auth.php';
require_once __DIR__ . '/../../app/models/Hotel.php';

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
    $roomId = $input['room_id'] ?? null;
    $checkIn = $input['check_in'] ?? null;
    $checkOut = $input['check_out'] ?? null;
    
    if (!$roomId || !$checkIn || !$checkOut) {
        throw new Exception('All fields are required');
    }
    
    $hotelModel = new Hotel();
    $hotelModel->reserveRoom($roomId, getCurrentUserId(), $checkIn, $checkOut);
    
    jsonResponse(['success' => true, 'message' => 'Room reserved successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
