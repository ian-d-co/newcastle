<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/middleware/CSRF.php';
require_once __DIR__ . '/../app/models/HotelSharing.php';

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

$action   = $input['action'] ?? '';
$userId   = getCurrentUserId();
$model    = new HotelSharing();

try {
    if ($action === 'request') {
        $targetUserId = (int)($input['target_user_id'] ?? 0);
        if (!$targetUserId) {
            jsonResponse(['success' => false, 'message' => 'Invalid target user'], 400);
        }
        if ($targetUserId === (int)$userId) {
            jsonResponse(['success' => false, 'message' => 'You cannot request yourself'], 400);
        }
        $message = trim($input['message'] ?? '');
        $model->createRequest($userId, $targetUserId, $message);
        jsonResponse(['success' => true, 'message' => 'Request sent successfully']);

    } elseif ($action === 'accept') {
        $requestId = (int)($input['request_id'] ?? 0);
        if (!$requestId) {
            jsonResponse(['success' => false, 'message' => 'Invalid request ID'], 400);
        }
        $model->acceptRequest($requestId, $userId);
        jsonResponse(['success' => true, 'message' => 'Request accepted']);

    } elseif ($action === 'decline') {
        $requestId = (int)($input['request_id'] ?? 0);
        if (!$requestId) {
            jsonResponse(['success' => false, 'message' => 'Invalid request ID'], 400);
        }
        $model->declineRequest($requestId, $userId);
        jsonResponse(['success' => true, 'message' => 'Request declined']);

    } elseif ($action === 'cancel') {
        $requestId = (int)($input['request_id'] ?? 0);
        if (!$requestId) {
            jsonResponse(['success' => false, 'message' => 'Invalid request ID'], 400);
        }
        $model->cancelRequest($requestId, $userId);
        jsonResponse(['success' => true, 'message' => 'Request cancelled']);

    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log('Error in hotel-sharing API: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
}
