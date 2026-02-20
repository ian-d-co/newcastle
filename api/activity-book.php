<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/models/Activity.php';

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
    $activityId = $input['activity_id'] ?? null;
    
    if (!$activityId) {
        throw new Exception('Activity ID is required');
    }
    
    $activityModel = new Activity();
    $activityModel->book($activityId, getCurrentUserId());
    
    jsonResponse(['success' => true, 'message' => 'Activity booked successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
