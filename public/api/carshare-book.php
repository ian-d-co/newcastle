<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_once BASE_PATH . '/app/middleware/Auth.php';
require_once BASE_PATH . '/app/models/CarShare.php';

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
    $offerId = $input['offer_id'] ?? null;
    
    if (!$offerId) {
        throw new Exception('Offer ID is required');
    }
    
    $carshareModel = new CarShare();
    $carshareModel->book($offerId, getCurrentUserId());
    
    jsonResponse(['success' => true, 'message' => 'Carshare booked successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
