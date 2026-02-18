<?php
/**
 * Dicksord Fest 2026 - Newcastle Event Management System
 * Main Entry Point
 * 
 * This file serves as the front controller for the application,
 * handling all routing, authentication, and request dispatching.
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load configuration and helpers
require_once BASE_PATH . '/app/config/config.php';
require_once BASE_PATH . '/app/helpers/Database.php';
require_once BASE_PATH . '/app/helpers/Auth.php';

// Load middleware
require_once BASE_PATH . '/app/middleware/Auth.php';
require_once BASE_PATH . '/app/middleware/AdminAuth.php';
require_once BASE_PATH . '/app/middleware/CSRF.php';

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Remove PHP version header for security
header_remove('X-Powered-By');

// Start session
initSession();

// Get page and action from query parameters
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? null;

// ============================================================================
// AUTHENTICATION ACTIONS
// ============================================================================

// Handle logout action
if ($action === 'logout') {
    require_once BASE_PATH . '/app/controllers/AuthController.php';
    $authController = new AuthController();
    $authController->logout();
}

// Handle login POST request
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once BASE_PATH . '/app/controllers/AuthController.php';
    $authController = new AuthController();
    echo $authController->login();
    exit;
}

// ============================================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================================

// Login page
if ($page === 'login') {
    require_once BASE_PATH . '/app/controllers/AuthController.php';
    $authController = new AuthController();
    echo $authController->showLogin();
    exit;
}

// Register page (handled within home page)
if ($page === 'register') {
    $page = 'home'; // Registration is handled on home page
}

// ============================================================================
// AUTHENTICATED ROUTES (Require login)
// ============================================================================

// All other pages require authentication
Auth::check();

// Load required models
require_once BASE_PATH . '/app/models/Event.php';
require_once BASE_PATH . '/app/models/Activity.php';
require_once BASE_PATH . '/app/models/Meal.php';
require_once BASE_PATH . '/app/models/Poll.php';
require_once BASE_PATH . '/app/models/CarShare.php';
require_once BASE_PATH . '/app/models/Hosting.php';
require_once BASE_PATH . '/app/models/Hotel.php';

// Initialize models
$eventModel = new Event();
$activityModel = new Activity();
$mealModel = new Meal();
$pollModel = new Poll();
$carshareModel = new CarShare();
$hostingModel = new Hosting();
$hotelModel = new Hotel();

// Get active event and current user ID
$event = $eventModel->getActive();
$userId = getCurrentUserId();

// ============================================================================
// ROUTING
// ============================================================================

// Route requests based on page parameter
switch ($page) {
    // ========================================================================
    // USER ROUTES
    // ========================================================================
    
    case 'home':
    case 'dashboard':
    case 'home':
    case 'dashboard':
        if ($page === 'home') {
            $pageTitle = 'Home';
            $isAttending = $eventModel->getAttendance($userId, $event['id']) !== false;
            include BASE_PATH . '/app/views/public/home.php';
        } else {
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
            
            include BASE_PATH . '/app/views/public/dashboard.php';
        }
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
        
        include BASE_PATH . '/app/views/public/activities.php';
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
        
        include BASE_PATH . '/app/views/public/meals.php';
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
        
        include BASE_PATH . '/app/views/public/polls.php';
        break;

    case 'carshare':
        $pageTitle = 'Carshare';
        $availableOffers = $carshareModel->getAvailable($event['id']);
        $userOffer = $carshareModel->getUserOffer($userId, $event['id']);
        $userBooking = $carshareModel->getUserBooking($userId, $event['id']);
        $offerBookings = $userOffer ? $carshareModel->getOfferBookings($userOffer['id']) : [];
        
        include BASE_PATH . '/app/views/public/carshare.php';
        break;

    case 'hosting':
        $pageTitle = 'Hosting';
        $availableOffers = $hostingModel->getAvailable($event['id']);
        $userOffer = $hostingModel->getUserOffer($userId, $event['id']);
        $userBooking = $hostingModel->getUserBooking($userId, $event['id']);
        $offerBookings = $userOffer ? $hostingModel->getOfferBookings($userOffer['id']) : [];
        
        include BASE_PATH . '/app/views/public/hosting.php';
        break;

    case 'hotels':
        $pageTitle = 'Hotels';
        $hotels = $hotelModel->getAll($event['id']);
        
        // Get rooms for each hotel
        foreach ($hotels as &$hotel) {
            $hotel['rooms'] = $hotelModel->getRoomsByHotel($hotel['id']);
        }
        
        $userReservations = $hotelModel->getUserReservations($userId, $event['id']);
        
        include BASE_PATH . '/app/views/public/hotels.php';
        break;

    // ========================================================================
    // ADMIN ROUTES (Require admin privileges)
    // ========================================================================
    
    case 'admin':
        AdminAuth::check();
        $pageTitle = 'Admin Dashboard';
        
        // Get statistics
        $db = getDbConnection();
        
        $stats = [];
        
        // Total attendees
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM event_attendees WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event['id']]);
        $stats['total_attendees'] = $stmt->fetch()['count'];
        
        // Total activities
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM activities WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event['id']]);
        $stats['total_activities'] = $stmt->fetch()['count'];
        
        // Total meals
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM meals WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event['id']]);
        $stats['total_meals'] = $stmt->fetch()['count'];
        
        // Carshare offers
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM carshare_offers WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event['id']]);
        $stats['carshare_offers'] = $stmt->fetch()['count'];
        
        // Hosting offers
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM hosting_offers WHERE event_id = :event_id");
        $stmt->execute(['event_id' => $event['id']]);
        $stats['hosting_offers'] = $stmt->fetch()['count'];
        
        // Active polls
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM polls WHERE event_id = :event_id AND is_active = 1");
        $stmt->execute(['event_id' => $event['id']]);
        $stats['active_polls'] = $stmt->fetch()['count'];
        
        // Recent attendees
        $recentAttendees = $eventModel->getAllAttendees($event['id']);
        $recentAttendees = array_slice($recentAttendees, 0, 10);
        
        include BASE_PATH . '/app/views/admin/dashboard.php';
        break;

    // ========================================================================
    // 404 - PAGE NOT FOUND
    // ========================================================================
    
    default:
        // Redirect unknown pages to home
        redirect('/index.php?page=home');
        break;
}
