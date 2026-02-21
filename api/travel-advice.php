<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/helpers/functions.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/middleware/AdminAuth.php';
require_once __DIR__ . '/../app/middleware/CSRF.php';
require_once __DIR__ . '/../app/models/TravelAdvice.php';
require_once __DIR__ . '/../app/models/Event.php';

initSession();
Auth::check();
AdminAuth::check();

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

try {
    $eventModel = new Event();
    $event = $eventModel->getActive();
    if (!$event) {
        jsonResponse(['success' => false, 'message' => 'No active event'], 400);
    }

    $model = new TravelAdvice();

    if ($action === 'create') {
        $required = ['travel_type', 'from_location', 'to_location'];
        foreach ($required as $field) {
            if (empty(trim($input[$field] ?? ''))) {
                jsonResponse(['success' => false, 'message' => "Field '$field' is required"], 400);
            }
        }
        $data = [
            'travel_type'     => trim($input['travel_type']),
            'from_location'   => trim($input['from_location']),
            'to_location'     => trim($input['to_location']),
            'supplier'        => trim($input['supplier'] ?? ''),
            'date_researched' => trim($input['date_researched'] ?? ''),
            'notes'           => trim($input['notes'] ?? ''),
        ];
        $id = $model->create($event['id'], $data, $userId);
        jsonResponse(['success' => true, 'message' => 'Travel advice added', 'id' => $id]);

    } elseif ($action === 'update') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Invalid ID'], 400);
        }
        $data = [
            'travel_type'     => trim($input['travel_type'] ?? ''),
            'from_location'   => trim($input['from_location'] ?? ''),
            'to_location'     => trim($input['to_location'] ?? ''),
            'supplier'        => trim($input['supplier'] ?? ''),
            'date_researched' => trim($input['date_researched'] ?? ''),
            'notes'           => trim($input['notes'] ?? ''),
        ];
        $model->update($id, $data);
        jsonResponse(['success' => true, 'message' => 'Travel advice updated']);

    } elseif ($action === 'delete') {
        $id = (int)($input['id'] ?? 0);
        if (!$id) {
            jsonResponse(['success' => false, 'message' => 'Invalid ID'], 400);
        }
        $model->delete($id);
        jsonResponse(['success' => true, 'message' => 'Travel advice deleted']);

    } else {
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    error_log('Error in travel-advice API: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'An error occurred'], 500);
}
