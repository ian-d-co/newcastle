<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_once BASE_PATH . '/app/middleware/Auth.php';
require_once BASE_PATH . '/app/models/Meal.php';

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
    $mealId = $input['meal_id'] ?? null;
    
    if (!$mealId) {
        throw new Exception('Meal ID is required');
    }
    
    $mealModel = new Meal();
    $mealModel->cancelBooking($mealId, getCurrentUserId());
    
    jsonResponse(['success' => true, 'message' => 'Meal cancelled successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
