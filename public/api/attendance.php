<?php
require_once __DIR__ . '/../../app/config/config.php';
require_once __DIR__ . '/../../app/middleware/Auth.php';
require_once __DIR__ . '/../../app/middleware/CSRF.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';
require_once __DIR__ . '/../../app/models/Event.php';
require_once __DIR__ . '/../../app/models/CarShare.php';
require_once __DIR__ . '/../../app/models/Hosting.php';

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
    $carshareModel = new CarShare();
    $hostingModel = new Hosting();
    
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
    
    // Handle carshare offer
    if (in_array('Car', $travelMethod) && isset($input['carshare_origin']) && isset($input['carshare_capacity'])) {
        $carshareModel->createOffer($userId, $event['id'], $input['carshare_origin'], $input['carshare_capacity']);
    }
    
    // Handle hosting offer
    if (isset($input['hosting_capacity'])) {
        $notes = $input['hosting_notes'] ?? '';
        $hostingModel->createOffer($userId, $event['id'], $input['hosting_capacity'], $notes);
    }
    
    jsonResponse(['success' => true, 'message' => 'Attendance registered successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
