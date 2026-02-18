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

    // Admin Event Editor
    case 'admin-event':
        AdminAuth::check();
        $pageTitle = 'Edit Event';
        
        // Handle save action
        if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'start_date' => $_POST['start_date'] ?? '',
                'end_date' => $_POST['end_date'] ?? '',
                'location' => $_POST['location'] ?? '',
                'content' => $_POST['content'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            if ($eventModel->update($event['id'], $data)) {
                $success = 'Event updated successfully!';
                $event = $eventModel->getById($event['id']);
            } else {
                $error = 'Failed to update event.';
            }
        }
        
        include BASE_PATH . '/app/views/admin/event.php';
        break;
    
    // Admin Users Management
    case 'admin-users':
        AdminAuth::check();
        $pageTitle = 'User Management';
        
        require_once BASE_PATH . '/app/models/User.php';
        $userModel = new User();
        
        // Handle edit action
        if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $updateData = [
                'discord_name' => $_POST['discord_name'] ?? '',
                'name' => $_POST['name'] ?? '',
                'is_admin' => isset($_POST['is_admin']) ? 1 : 0
            ];
            
            if (!empty($_POST['new_pin'])) {
                $updateData['pin'] = $_POST['new_pin'];
            }
            
            if ($userModel->update($_POST['user_id'], $updateData)) {
                $success = 'User updated successfully!';
            } else {
                $error = 'Failed to update user.';
            }
        }
        
        // Handle delete action
        if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            if ($userModel->delete($_POST['user_id'])) {
                $success = 'User deleted successfully!';
            } else {
                $error = 'Failed to delete user.';
            }
        }
        
        // Get users (with search if provided)
        $users = $userModel->getAll();
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $search = strtolower($_GET['search']);
            $users = array_filter($users, function($user) use ($search) {
                return stripos($user['discord_name'], $search) !== false || 
                       stripos($user['name'], $search) !== false;
            });
        }
        
        include BASE_PATH . '/app/views/admin/users.php';
        break;
    
    // Admin Activities Management
    case 'admin-activities':
        AdminAuth::check();
        $pageTitle = 'Activity Management';
        
        // Handle create action
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $data = [
                'event_id' => $event['id'],
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'day' => $_POST['day'] ?? '',
                'start_time' => $_POST['start_time'] ?? '',
                'end_time' => $_POST['end_time'] ?? '',
                'max_capacity' => intval($_POST['max_capacity'] ?? 0),
                'requires_prepayment' => intval($_POST['requires_prepayment'] ?? 0),
                'price' => floatval($_POST['price'] ?? 0)
            ];
            
            if ($activityModel->create($data)) {
                $success = 'Activity created successfully!';
            } else {
                $error = 'Failed to create activity.';
            }
        }
        
        // Handle edit action
        if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'day' => $_POST['day'] ?? '',
                'start_time' => $_POST['start_time'] ?? '',
                'end_time' => $_POST['end_time'] ?? '',
                'max_capacity' => intval($_POST['max_capacity'] ?? 0),
                'requires_prepayment' => intval($_POST['requires_prepayment'] ?? 0),
                'price' => floatval($_POST['price'] ?? 0)
            ];
            
            if ($activityModel->update($_POST['activity_id'], $data)) {
                $success = 'Activity updated successfully!';
            } else {
                $error = 'Failed to update activity.';
            }
        }
        
        // Handle delete action
        if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            if ($activityModel->delete($_POST['activity_id'])) {
                $success = 'Activity deleted successfully!';
            } else {
                $error = 'Failed to delete activity.';
            }
        }
        
        // Handle payment update
        if ($action === 'payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            if ($activityModel->updatePaymentStatus($_POST['booking_id'], $_POST['status'])) {
                $success = 'Payment status updated!';
            } else {
                $error = 'Failed to update payment status.';
            }
        }
        
        $activities = $activityModel->getAll($event['id']);
        // Add bookings data for each activity
        foreach ($activities as &$activity) {
            $activity['bookings'] = $activityModel->getBookingsByActivity($activity['id']);
        }
        include BASE_PATH . '/app/views/admin/activities.php';
        break;
    
    // Admin Meals Management
    case 'admin-meals':
        AdminAuth::check();
        $pageTitle = 'Meal Management';
        
        // Handle create action
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $data = [
                'event_id' => $event['id'],
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'day' => $_POST['day'] ?? '',
                'start_time' => $_POST['start_time'] ?? '',
                'end_time' => $_POST['end_time'] ?? '',
                'max_capacity' => intval($_POST['max_capacity'] ?? 0),
                'requires_prepayment' => intval($_POST['requires_prepayment'] ?? 0),
                'price' => floatval($_POST['price'] ?? 0)
            ];
            
            if ($mealModel->create($data)) {
                $success = 'Meal created successfully!';
            } else {
                $error = 'Failed to create meal.';
            }
        }
        
        // Handle edit action
        if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $data = [
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? '',
                'day' => $_POST['day'] ?? '',
                'start_time' => $_POST['start_time'] ?? '',
                'end_time' => $_POST['end_time'] ?? '',
                'max_capacity' => intval($_POST['max_capacity'] ?? 0),
                'requires_prepayment' => intval($_POST['requires_prepayment'] ?? 0),
                'price' => floatval($_POST['price'] ?? 0)
            ];
            
            if ($mealModel->update($_POST['meal_id'], $data)) {
                $success = 'Meal updated successfully!';
            } else {
                $error = 'Failed to update meal.';
            }
        }
        
        // Handle delete action
        if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            if ($mealModel->delete($_POST['meal_id'])) {
                $success = 'Meal deleted successfully!';
            } else {
                $error = 'Failed to delete meal.';
            }
        }
        
        // Handle payment update
        if ($action === 'payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            if ($mealModel->updatePaymentStatus($_POST['booking_id'], $_POST['status'])) {
                $success = 'Payment status updated!';
            } else {
                $error = 'Failed to update payment status.';
            }
        }
        
        $meals = $mealModel->getAll($event['id']);
        // Add bookings data for each meal
        foreach ($meals as &$meal) {
            $meal['bookings'] = $mealModel->getBookingsByMeal($meal['id']);
        }
        include BASE_PATH . '/app/views/admin/meals.php';
        break;
    
    // Admin Polls Management
    case 'admin-polls':
        AdminAuth::check();
        $pageTitle = 'Poll Management';
        
        // Handle create action
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $question = $_POST['question'] ?? '';
            $options = $_POST['options'] ?? [];
            $isAnonymous = isset($_POST['anonymous']) ? 1 : 0;
            $isMultipleChoice = isset($_POST['allow_multiple']) ? 1 : 0;
            $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
            
            if ($pollModel->create($event['id'], $question, $options, $isAnonymous, $isMultipleChoice, $expiresAt, $userId)) {
                $success = 'Poll created successfully!';
            } else {
                $error = 'Failed to create poll.';
            }
        }
        
        // Handle edit action
        if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $data = [
                'question' => $_POST['question'] ?? '',
                'allow_multiple' => isset($_POST['allow_multiple']) ? 1 : 0,
                'anonymous' => isset($_POST['anonymous']) ? 1 : 0,
                'expires_at' => !empty($_POST['expires_at']) ? $_POST['expires_at'] : null
            ];
            
            if ($pollModel->update($_POST['poll_id'], $data)) {
                $success = 'Poll updated successfully!';
            } else {
                $error = 'Failed to update poll.';
            }
        }
        
        // Handle toggle active
        if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $data = ['is_active' => intval($_POST['is_active'] ?? 0)];
            if ($pollModel->update($_POST['poll_id'], $data)) {
                $success = 'Poll status updated!';
            } else {
                $error = 'Failed to update poll status.';
            }
        }
        
        // Handle delete action
        if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            if ($pollModel->delete($_POST['poll_id'])) {
                $success = 'Poll deleted successfully!';
            } else {
                $error = 'Failed to delete poll.';
            }
        }
        
        // Get all polls with options and votes
        $polls = $pollModel->getAll($event['id']);
        foreach ($polls as &$poll) {
            $poll['options'] = $pollModel->getOptions($poll['id']);
            foreach ($poll['options'] as &$option) {
                $option['votes'] = $option['vote_count'];
            }
        }
        include BASE_PATH . '/app/views/admin/polls.php';
        break;
    
    // Admin Hotels Management
    case 'admin-hotels':
        AdminAuth::check();
        $pageTitle = 'Hotel Management';
        
        // Handle create hotel
        if ($action === 'create-hotel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            // Use location as address and empty website
            $name = $_POST['name'] ?? '';
            $address = $_POST['location'] ?? ''; // Map location to address
            $description = $_POST['description'] ?? '';
            $website = null;
            
            if ($hotelModel->createHotel($event['id'], $name, $address, $description, $website)) {
                $success = 'Hotel created successfully!';
            } else {
                $error = 'Failed to create hotel.';
            }
        }
        
        // Handle edit hotel
        if ($action === 'edit-hotel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $data = [
                'name' => $_POST['name'] ?? '',
                'address' => $_POST['location'] ?? '', // Map location to address
                'description' => $_POST['description'] ?? '',
                'website' => $_POST['website'] ?? null
            ];
            
            if ($hotelModel->updateHotel($_POST['hotel_id'], $data)) {
                $success = 'Hotel updated successfully!';
            } else {
                $error = 'Failed to update hotel.';
            }
        }
        
        // Handle delete hotel
        if ($action === 'delete-hotel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            if ($hotelModel->deleteHotel($_POST['hotel_id'])) {
                $success = 'Hotel deleted successfully!';
            } else {
                $error = 'Failed to delete hotel.';
            }
        }
        
        // Handle create room
        if ($action === 'create-room' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $hotelId = intval($_POST['hotel_id'] ?? 0);
            $roomType = $_POST['room_type'] ?? '';
            $capacity = intval($_POST['capacity'] ?? 0);
            $pricePerNight = floatval($_POST['price_per_night'] ?? 0);
            $totalRooms = intval($_POST['quantity_available'] ?? 0);
            $description = '';
            
            if ($hotelModel->createRoom($hotelId, $roomType, $capacity, $pricePerNight, $totalRooms, $description)) {
                $success = 'Room added successfully!';
            } else {
                $error = 'Failed to add room.';
            }
        }
        
        // Handle edit room
        if ($action === 'edit-room' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $data = [
                'room_type' => $_POST['room_type'] ?? '',
                'capacity' => intval($_POST['capacity'] ?? 0),
                'price_per_night' => floatval($_POST['price_per_night'] ?? 0),
                'total_rooms' => intval($_POST['quantity_available'] ?? 0)
            ];
            
            if ($hotelModel->updateRoom($_POST['room_id'], $data)) {
                $success = 'Room updated successfully!';
            } else {
                $error = 'Failed to update room.';
            }
        }
        
        // Handle delete room
        if ($action === 'delete-room' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            if ($hotelModel->deleteRoom($_POST['room_id'])) {
                $success = 'Room deleted successfully!';
            } else {
                $error = 'Failed to delete room.';
            }
        }
        
        // Handle cancel reservation
        if ($action === 'cancel-reservation' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            if ($hotelModel->cancelReservation($_POST['reservation_id'])) {
                $success = 'Reservation cancelled successfully!';
            } else {
                $error = 'Failed to cancel reservation.';
            }
        }
        
        // Handle payment update
        if ($action === 'payment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            if ($hotelModel->updatePaymentStatus($_POST['reservation_id'], $_POST['status'])) {
                $success = 'Payment status updated!';
            } else {
                $error = 'Failed to update payment status.';
            }
        }
        
        $hotels = $hotelModel->getAll($event['id']);
        
        // For each hotel, get rooms and for each room get reservations
        foreach ($hotels as &$hotel) {
            $hotel['rooms'] = $hotelModel->getRoomsByHotel($hotel['id']);
            foreach ($hotel['rooms'] as &$room) {
                // Get reservations for this room
                $db = getDbConnection();
                $sql = "SELECT rr.*, u.discord_name
                        FROM room_reservations rr
                        JOIN users u ON rr.user_id = u.id
                        WHERE rr.hotel_room_id = :room_id AND rr.payment_status != 'cancelled'
                        ORDER BY rr.check_in";
                $stmt = $db->prepare($sql);
                $stmt->execute(['room_id' => $room['id']]);
                $room['reservations'] = $stmt->fetchAll();
            }
        }
        
        include BASE_PATH . '/app/views/admin/hotels.php';
        break;
    
    // Admin Payments Tracking
    case 'admin-payments':
        AdminAuth::check();
        $pageTitle = 'Payment Tracking';
        
        $db = getDbConnection();
        
        // Handle payment status update
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validate();
            
            $type = $_POST['type'] ?? '';
            $status = $_POST['status'] ?? 'pending';
            
            if ($type === 'activity' && isset($_POST['booking_id'])) {
                if ($activityModel->updatePaymentStatus($_POST['booking_id'], $status)) {
                    $success = 'Payment status updated!';
                }
            } elseif ($type === 'meal' && isset($_POST['booking_id'])) {
                if ($mealModel->updatePaymentStatus($_POST['booking_id'], $status)) {
                    $success = 'Payment status updated!';
                }
            } elseif ($type === 'hotel' && isset($_POST['reservation_id'])) {
                if ($hotelModel->updatePaymentStatus($_POST['reservation_id'], $status)) {
                    $success = 'Payment status updated!';
                }
            }
        }
        
        // Get filter parameters
        $statusFilter = $_GET['status'] ?? 'all';
        $typeFilter = $_GET['type'] ?? 'all';
        
        // Build SQL conditions
        $statusCondition = ($statusFilter !== 'all') ? "AND payment_status = '$statusFilter'" : '';
        
        // Get activity bookings
        $activityBookings = [];
        if ($typeFilter === 'all' || $typeFilter === 'activity') {
            $sql = "SELECT ab.*, u.discord_name, a.title as activity_title, a.day, a.price as payment_amount
                    FROM activity_bookings ab
                    JOIN users u ON ab.user_id = u.id
                    JOIN activities a ON ab.activity_id = a.id
                    WHERE a.event_id = :event_id AND a.requires_prepayment = 1 $statusCondition
                    ORDER BY ab.created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['event_id' => $event['id']]);
            $activityBookings = $stmt->fetchAll();
        }
        
        // Get meal bookings
        $mealBookings = [];
        if ($typeFilter === 'all' || $typeFilter === 'meal') {
            $sql = "SELECT mb.*, u.discord_name, m.title as meal_title, m.day, m.price as payment_amount
                    FROM meal_bookings mb
                    JOIN users u ON mb.user_id = u.id
                    JOIN meals m ON mb.meal_id = m.id
                    WHERE m.event_id = :event_id AND m.requires_prepayment = 1 $statusCondition
                    ORDER BY mb.created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['event_id' => $event['id']]);
            $mealBookings = $stmt->fetchAll();
        }
        
        // Get hotel reservations
        $hotelReservations = [];
        if ($typeFilter === 'all' || $typeFilter === 'hotel') {
            $sql = "SELECT rr.*, u.discord_name, h.name as hotel_name, hr.room_type, rr.total_price
                    FROM room_reservations rr
                    JOIN users u ON rr.user_id = u.id
                    JOIN hotel_rooms hr ON rr.hotel_room_id = hr.id
                    JOIN hotels h ON hr.hotel_id = h.id
                    WHERE h.event_id = :event_id $statusCondition
                    ORDER BY rr.created_at DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute(['event_id' => $event['id']]);
            $hotelReservations = $stmt->fetchAll();
        }
        
        // Calculate statistics
        $stats = [
            'total_due' => 0,
            'total_received' => 0,
            'total_pending' => 0,
            'pending_count' => 0
        ];
        
        foreach ($activityBookings as $booking) {
            $stats['total_due'] += $booking['payment_amount'];
            if ($booking['payment_status'] === 'received') {
                $stats['total_received'] += $booking['payment_amount'];
            } else {
                $stats['total_pending'] += $booking['payment_amount'];
                $stats['pending_count']++;
            }
        }
        
        foreach ($mealBookings as $booking) {
            $stats['total_due'] += $booking['payment_amount'];
            if ($booking['payment_status'] === 'received') {
                $stats['total_received'] += $booking['payment_amount'];
            } else {
                $stats['total_pending'] += $booking['payment_amount'];
                $stats['pending_count']++;
            }
        }
        
        foreach ($hotelReservations as $reservation) {
            $stats['total_due'] += $reservation['total_price'];
            if ($reservation['payment_status'] === 'received') {
                $stats['total_received'] += $reservation['total_price'];
            } else {
                $stats['total_pending'] += $reservation['total_price'];
                $stats['pending_count']++;
            }
        }
        
        include BASE_PATH . '/app/views/admin/payments.php';
        break;

    // ========================================================================
    // 404 - PAGE NOT FOUND
    // ========================================================================
    
    default:
        // Show 404 page for unknown routes
        http_response_code(404);
        include BASE_PATH . '/app/views/pages/404.php';
        break;
}
