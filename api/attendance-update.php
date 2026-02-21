<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/middleware/CSRF.php';
require_once __DIR__ . '/../app/models/Event.php';

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
    $daysAttending = $input['days_attending'] ?? [];
    $travelMethod  = $input['travel_method']  ?? [];

    if (empty($daysAttending)) {
        throw new Exception('Please select at least one day');
    }

    if (empty($travelMethod)) {
        throw new Exception('Please select at least one travel method');
    }

    $userId      = getCurrentUserId();
    $eventModel  = new Event();
    $event       = $eventModel->getActive();

    $eventModel->registerAttendance($userId, $event['id'], $daysAttending, $travelMethod);

    jsonResponse(['success' => true, 'message' => 'Attendance updated successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
