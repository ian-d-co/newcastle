<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/middleware/CSRF.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Event.php';
require_once __DIR__ . '/../app/models/CarShare.php';
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

    } elseif ($action === 'update_carshare') {
        $origin   = trim($input['carshare_origin'] ?? '');
        $capacity = (int)($input['carshare_capacity'] ?? 0);
        if (empty($origin)) {
            jsonResponse(['success' => false, 'message' => 'Travel origin is required'], 400);
        }
        if ($capacity < 1 || $capacity > 8) {
            jsonResponse(['success' => false, 'message' => 'Passenger capacity must be between 1 and 8'], 400);
        }
        $eventModel = new Event();
        $event = $eventModel->getActive();
        if (!$event) {
            jsonResponse(['success' => false, 'message' => 'No active event found'], 400);
        }
        $carshareModel = new CarShare();
        $carshareModel->updateOffer($userId, $event['id'], $origin, $capacity);
        jsonResponse(['success' => true, 'message' => 'Car share offer saved']);

    } elseif ($action === 'remove_carshare') {
        $eventModel = new Event();
        $event = $eventModel->getActive();
        if (!$event) {
            jsonResponse(['success' => false, 'message' => 'No active event found'], 400);
        }
        $carshareModel = new CarShare();
        $carshareModel->removeOffer($userId, $event['id']);
        jsonResponse(['success' => true, 'message' => 'Car share offer removed']);

    } elseif ($action === 'update_hosting') {
        $capacity = (int)($input['hosting_capacity'] ?? 0);
        $notes    = trim($input['hosting_notes'] ?? '');
        if ($capacity < 1) {
            jsonResponse(['success' => false, 'message' => 'Hosting capacity must be at least 1'], 400);
        }
        $eventModel = new Event();
        $event = $eventModel->getActive();
        if (!$event) {
            jsonResponse(['success' => false, 'message' => 'No active event found'], 400);
        }
        $hostingModel = new Hosting();
        $hostingModel->updateOffer($userId, $event['id'], $capacity, $notes);
        jsonResponse(['success' => true, 'message' => 'Hosting offer saved']);

    } elseif ($action === 'remove_hosting') {
        $eventModel = new Event();
        $event = $eventModel->getActive();
        if (!$event) {
            jsonResponse(['success' => false, 'message' => 'No active event found'], 400);
        }
        $hostingModel = new Hosting();
        $hostingModel->removeOffer($userId, $event['id']);
        jsonResponse(['success' => true, 'message' => 'Hosting offer removed']);

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
