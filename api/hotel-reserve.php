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
    $roomId = $input['room_id'] ?? null;
    $checkIn = $input['check_in'] ?? null;
    $checkOut = $input['check_out'] ?? null;
    $occupancyType = $input['occupancy_type'] ?? null;
    $nights = $input['nights'] ?? [];
    $bookDirect = !empty($input['book_direct']);
    $bookWithGroup = !empty($input['book_with_group']);

    if (!$roomId) {
        throw new Exception('Room ID is required');
    }

    // Must provide either occupancy-based OR date-based parameters
    if ($occupancyType && !empty($nights)) {
        $validOccupancy = ['single', 'double', 'triple'];
        if (!in_array($occupancyType, $validOccupancy)) {
            throw new Exception('Invalid occupancy type');
        }
        $validNights = ['friday', 'saturday'];
        foreach ($nights as $night) {
            if (!in_array($night, $validNights)) {
                throw new Exception('Invalid night selection');
            }
        }
    } elseif (!$checkIn || !$checkOut) {
        throw new Exception('Either occupancy type with nights, or check-in and check-out dates are required');
    }

    $hotelModel = new Hotel();
    $hotelModel->reserveRoom($roomId, getCurrentUserId(), $checkIn, $checkOut, $occupancyType, $nights, $bookDirect, $bookWithGroup);

    jsonResponse(['success' => true, 'message' => 'Room reserved successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
