<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
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
    $requestId = $input['request_id'] ?? null;
    $responseAction = $input['action'] ?? null;

    if (!$requestId) {
        throw new Exception('Request ID is required');
    }
    if (!in_array($responseAction, ['accept', 'decline', 'cancel'])) {
        throw new Exception('Invalid action');
    }

    $userId = getCurrentUserId();
    $hostingModel = new Hosting();

    if ($responseAction === 'accept') {
        $hostingModel->acceptRequest($requestId, $userId);
        jsonResponse(['success' => true, 'message' => 'Request accepted']);
    } elseif ($responseAction === 'decline') {
        $hostingModel->declineRequest($requestId, $userId);
        jsonResponse(['success' => true, 'message' => 'Request declined']);
    } elseif ($responseAction === 'cancel') {
        $hostingModel->cancelRequest($requestId, $userId);
        jsonResponse(['success' => true, 'message' => 'Request cancelled']);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
}
