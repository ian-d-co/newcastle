<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/middleware/CSRF.php';
require_once __DIR__ . '/../app/models/User.php';

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

$action = $input['action'] ?? '';
$userId = getCurrentUserId();
$userModel = new User();

try {
    if ($action === 'update_profile') {
        $data = [];
        if (!empty($input['discord_name'])) {
            $data['discord_name'] = trim($input['discord_name']);
        }
        if (!empty($input['name'])) {
            $data['name'] = trim($input['name']);
        }
        if (empty($data)) {
            jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
        }
        $userModel->update($userId, $data);
        jsonResponse(['success' => true, 'message' => 'Profile updated successfully']);

    } elseif ($action === 'update_hotel_sharing') {
        $val = isset($input['open_to_hotel_sharing']) ? (int)$input['open_to_hotel_sharing'] : 0;
        $userModel->update($userId, ['open_to_hotel_sharing' => $val]);
        jsonResponse(['success' => true, 'message' => $val ? 'You are now open to hotel sharing' : 'Hotel sharing preference removed']);

    } elseif ($action === 'change_pin') {
        $currentPin = $input['current_pin'] ?? '';
        $newPin     = $input['new_pin']     ?? '';

        if (empty($currentPin) || empty($newPin)) {
            jsonResponse(['success' => false, 'message' => 'Current PIN and new PIN are required'], 400);
        }

        if (!preg_match('/^\d{4,8}$/', $newPin)) {
            jsonResponse(['success' => false, 'message' => 'New PIN must be 4-8 digits'], 400);
        }

        $user = $userModel->findById($userId);
        if (!$userModel->verifyPin($user, $currentPin)) {
            jsonResponse(['success' => false, 'message' => 'Current PIN is incorrect'], 403);
        }

        $userModel->update($userId, ['pin' => $newPin]);
        jsonResponse(['success' => true, 'message' => 'PIN changed successfully']);

    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log('Error in profile-update: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
