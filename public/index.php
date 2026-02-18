<?php
// Dicksord Fest 2026 - Newcastle - Main Entry Point
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/middleware/Auth.php';
require_once __DIR__ . '/../app/middleware/AdminAuth.php';
require_once __DIR__ . '/../app/middleware/CSRF.php';

// Start session
initSession();

// Get page and action
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? null;

// Handle logout action
if ($action === 'logout') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    $authController = new AuthController();
    $authController->logout();
}

// Handle login action
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    $authController = new AuthController();
    echo $authController->login();
    exit;
}

// Public pages (no auth required)
if ($page === 'login') {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    $authController = new AuthController();
    echo $authController->showLogin();
    exit;
}

// All other pages require authentication
Auth::check();

// Load models
require_once __DIR__ . '/../app/models/Event.php';
require_once __DIR__ . '/../app/models/Activity.php';
require_once __DIR__ . '/../app/models/Meal.php';
require_once __DIR__ . '/../app/models/Poll.php';
require_once __DIR__ . '/../app/models/CarShare.php';
require_once __DIR__ . '/../app/models/Hosting.php';
require_once __DIR__ . '/../app/models/Hotel.php';

$eventModel = new Event();
$activityModel = new Activity();
$mealModel = new Meal();
$pollModel = new Poll();
$carshareModel = new CarShare();
$hostingModel = new Hosting();
$hotelModel = new Hotel();

$event = $eventModel->getActive();
$userId = getCurrentUserId();

// Route pages
switch ($page) {
    case 'home':
        $pageTitle = 'Home';
        $isAttending = $eventModel->getAttendance($userId, $event['id']) !== false;
        include __DIR__ . '/../app/views/public/home.php';
        break;

    case 'activities':
        $pageTitle = 'Activities';
        $activities = $activityModel->getAll($event['id']);
        
        // Check which activities user has booked
        foreach ($activities as &$activity) {
            $activity['is_booked'] = $activityModel->isBooked($activity['id'], $userId);
            
            // Get payment status if booked
            if ($activity['is_booked']) {
                $bookings = $activityModel->getUserBookings($userId, $event['id']);
                foreach ($bookings as $booking) {
                    if ($booking['activity_id'] == $activity['id']) {
                        $activity['payment_status'] = $booking['payment_status'];
                        break;
                    }
                }
            }
        }
        
        include __DIR__ . '/../app/views/public/activities.php';
        break;

    case 'meals':
        $pageTitle = 'Meals';
        $meals = $mealModel->getAll($event['id']);
        
        // Check which meals user has booked
        foreach ($meals as &$meal) {
            $meal['is_booked'] = $mealModel->isBooked($meal['id'], $userId);
            
            // Get payment status if booked
            if ($meal['is_booked']) {
                $bookings = $mealModel->getUserBookings($userId, $event['id']);
                foreach ($bookings as $booking) {
                    if ($booking['meal_id'] == $meal['id']) {
                        $meal['payment_status'] = $booking['payment_status'];
                        break;
                    }
                }
            }
        }
        
        include __DIR__ . '/../app/views/public/meals.php';
        break;

    case 'polls':
        $pageTitle = 'Polls';
        $polls = $pollModel->getActive($event['id']);
        
        // Check which polls user has voted on and get options
        foreach ($polls as &$poll) {
            $poll['has_voted'] = $pollModel->hasVoted($poll['id'], $userId);
            $poll['user_votes'] = $pollModel->getUserVotes($poll['id'], $userId);
            $poll['options'] = $pollModel->getOptions($poll['id']);
        }
        
        include __DIR__ . '/../app/views/public/polls.php';
        break;

    case 'carshare':
        $pageTitle = 'Carshare';
        $availableOffers = $carshareModel->getAvailable($event['id']);
        $userOffer = $carshareModel->getUserOffer($userId, $event['id']);
        $userBooking = $carshareModel->getUserBooking($userId, $event['id']);
        $offerBookings = $userOffer ? $carshareModel->getOfferBookings($userOffer['id']) : [];
        
        include __DIR__ . '/../app/views/public/carshare.php';
        break;

    case 'hosting':
        $pageTitle = 'Hosting';
        $availableOffers = $hostingModel->getAvailable($event['id']);
        $userOffer = $hostingModel->getUserOffer($userId, $event['id']);
        $userBooking = $hostingModel->getUserBooking($userId, $event['id']);
        $offerBookings = $userOffer ? $hostingModel->getOfferBookings($userOffer['id']) : [];
        
        include __DIR__ . '/../app/views/public/hosting.php';
        break;

    case 'hotels':
        $pageTitle = 'Hotels';
        $hotels = $hotelModel->getAll($event['id']);
        
        // Get rooms for each hotel
        foreach ($hotels as &$hotel) {
            $hotel['rooms'] = $hotelModel->getRoomsByHotel($hotel['id']);
        }
        
        $userReservations = $hotelModel->getUserReservations($userId, $event['id']);
        
        include __DIR__ . '/../app/views/public/hotels.php';
        break;

    case 'dashboard':
        $pageTitle = 'Dashboard';
        $attendance = $eventModel->getAttendance($userId, $event['id']);
        $activityBookings = $activityModel->getUserBookings($userId, $event['id']);
        $mealBookings = $mealModel->getUserBookings($userId, $event['id']);
        $carshareOffer = $carshareModel->getUserOffer($userId, $event['id']);
        $carshareBooking = $carshareModel->getUserBooking($userId, $event['id']);
        $hostingOffer = $hostingModel->getUserOffer($userId, $event['id']);
        $hostingBooking = $hostingModel->getUserBooking($userId, $event['id']);
        $hotelReservations = $hotelModel->getUserReservations($userId, $event['id']);
        
        // Get polls voted on
        $db = getDbConnection();
        $sql = "SELECT DISTINCT p.id, p.question, pv.created_at as voted_at 
                FROM polls p
                JOIN poll_votes pv ON p.id = pv.poll_id
                WHERE pv.user_id = :user_id AND p.event_id = :event_id
                ORDER BY pv.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $event['id']]);
        $pollsVoted = $stmt->fetchAll();
        
        include __DIR__ . '/../app/views/public/dashboard.php';
        break;

    case 'admin':
        AdminAuth::check();
        // For now, redirect to home - admin panel would be implemented here
        redirect('/index.php?page=home');
        break;

    default:
        redirect('/index.php?page=home');
        break;
}
