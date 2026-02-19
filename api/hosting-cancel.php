<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
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
    $offerId = $input['offer_id'] ?? null;
    
    if (!$offerId) {
        throw new Exception('Offer ID is required');
    }
    
    $hostingModel = new Hosting();
    $hostingModel->cancelBooking($offerId, getCurrentUserId());
    
    jsonResponse(['success' => true, 'message' => 'Hosting cancelled successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
