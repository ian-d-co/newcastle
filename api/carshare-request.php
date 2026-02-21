<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/models/CarShare.php';

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
    $message = $input['message'] ?? '';

    if (!$offerId) {
        throw new Exception('Offer ID is required');
    }

    $userId = getCurrentUserId();
    $carshareModel = new CarShare();

    $offer = $carshareModel->getById($offerId);
    if (!$offer) {
        throw new Exception('Car share offer not found');
    }
    if ($offer['user_id'] == $userId) {
        throw new Exception('Cannot request to join your own car share');
    }
    if ($offer['available_spaces'] <= 0) {
        throw new Exception('No available spaces');
    }

    $carshareModel->createRequest($offerId, $userId, $message);

    jsonResponse(['success' => true, 'message' => 'Request to join sent successfully']);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
