<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/middleware/CSRF.php';
require_once __DIR__ . '/../app/models/Event.php';
require_once __DIR__ . '/../app/models/CarShare.php';
require_once __DIR__ . '/../app/models/Hosting.php';

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
    $carshare    = new CarShare();
    $hosting     = new Hosting();
    $event       = $eventModel->getActive();

    $eventModel->registerAttendance($userId, $event['id'], $daysAttending, $travelMethod);

    if (in_array('Car', $travelMethod) && !empty($input['carshare_origin']) && !empty($input['carshare_capacity'])) {
        $carshare->createOffer($userId, $event['id'], $input['carshare_origin'], (int)$input['carshare_capacity']);
    }

    if (!empty($input['hosting_capacity'])) {
        $hosting->createOffer($userId, $event['id'], (int)$input['hosting_capacity'], $input['hosting_notes'] ?? '');
    }

    jsonResponse(['success' => true, 'message' => 'Attendance updated successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
