<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/middleware/CSRF.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Event.php';

initSession();
Auth::check();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

// Validate CSRF token
if (!verifyCsrfToken($input['csrf_token'] ?? '')) {
    jsonResponse(['success' => false, 'message' => 'Invalid CSRF token'], 403);
}

try {
    $discordName = $input['discord_name'] ?? '';
    $name = $input['name'] ?? '';
    $pin = $input['pin'] ?? '';
    $daysAttending = $input['days_attending'] ?? [];
    $travelMethod = $input['travel_method'] ?? [];
    
    if (empty($discordName) || empty($name) || empty($pin)) {
        throw new Exception('Please fill in all required fields');
    }
    
    if (empty($daysAttending)) {
        throw new Exception('Please select at least one day');
    }
    
    if (empty($travelMethod)) {
        throw new Exception('Please select at least one travel method');
    }
    
    $authController = new AuthController();
    $eventModel = new Event();
    
    // Register user if new
    $userId = null;
    try {
        $userId = $authController->register($discordName, $name, $pin);
    } catch (Exception $e) {
        // User exists, that's OK - they're just updating attendance
        $userId = getCurrentUserId();
    }
    
    // Get active event
    $event = $eventModel->getActive();
    
    // Register attendance
    $eventModel->registerAttendance($userId, $event['id'], $daysAttending, $travelMethod);
    
    jsonResponse(['success' => true, 'message' => 'Attendance registered successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
