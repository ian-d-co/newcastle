<?php
/**
 * Dicksord Fest 2026 - Newcastle Event Management System
 * Main Entry Point
 * 
 * This file serves as the front controller for the application,
 * handling all routing, authentication, and request dispatching.
 */

// Enable error logging for debugging
error_log('=== Dicksord Fest 2026 Request Start ===');
error_log('Request URI: ' . ($_SERVER['REQUEST_URI'] ?? 'N/A'));
error_log('Request Method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));

// Define base path - Robust detection for various deployment scenarios
// This handles both standard repo structure and Hostinger public_html deployment
$basePath = realpath(dirname(__DIR__));

// Verify that app directory exists at the detected location
if (!is_dir($basePath . '/app')) {
    error_log('Warning: app directory not found at expected location: ' . $basePath . '/app');
    
    // Try using DOCUMENT_ROOT as reference (for cases where symlinks or non-standard setups exist)
    if (isset($_SERVER['DOCUMENT_ROOT']) && is_dir($_SERVER['DOCUMENT_ROOT'] . '/../app')) {
        $basePath = realpath($_SERVER['DOCUMENT_ROOT'] . '/..');
        error_log('Found app directory using DOCUMENT_ROOT: ' . $basePath . '/app');
    } 
    else {
        error_log('FATAL: Could not locate app directory. Checked: ' . 
                  $basePath . '/app, ' . 
                  ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . '/../app');
    }
}

define('BASE_PATH', $basePath);

try {
    // Load configuration and helpers
    require_once BASE_PATH . '/app/config/config.php';
    require_once BASE_PATH . '/app/helpers/functions.php';
    require_once BASE_PATH . '/app/helpers/Database.php';
    require_once BASE_PATH . '/app/helpers/Auth.php';
    
    // Load middleware
    require_once BASE_PATH . '/app/middleware/Auth.php';
    require_once BASE_PATH . '/app/middleware/AdminAuth.php';
    require_once BASE_PATH . '/app/middleware/CSRF.php';
    
    error_log('All configuration and helper files loaded successfully');
} catch (Exception $e) {
    error_log('FATAL: Failed to load required files: ' . $e->getMessage());
    if (defined('APP_DEBUG') && APP_DEBUG) {
        die('<html><body style="font-family: Arial; padding: 20px;"><h1>Configuration Error</h1><p>' . 
            htmlspecialchars($e->getMessage()) . '</p></body></html>');
    } else {
        die('<html><body style="font-family: Arial; padding: 20px;"><h1>Service Unavailable</h1>' .
            '<p>The website is currently being configured. Please try again later.</p></body></html>');
    }
}

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Remove PHP version header for security
header_remove('X-Powered-By');

/**
 * Helper function to render error pages
 * Ensures error responses are never blank and include proper HTML
 */
function renderErrorPage($title, $message, $debugInfo = null) {
    http_response_code(500);
    $html = '<html><head><title>' . htmlspecialchars($title) . '</title>';
    $html .= '<style>body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }';
    $html .= 'h1 { color: #d32f2f; } .debug { background: #f5f5f5; padding: 15px; border-left: 3px solid #d32f2f; margin-top: 20px; }';
    $html .= 'pre { white-space: pre-wrap; word-wrap: break-word; }</style></head><body>';
    $html .= '<h1>' . htmlspecialchars($title) . '</h1>';
    $html .= '<p>' . htmlspecialchars($message) . '</p>';
    
    if (defined('APP_DEBUG') && APP_DEBUG && $debugInfo) {
        $html .= '<div class="debug"><strong>Debug Information:</strong><pre>' . htmlspecialchars($debugInfo) . '</pre></div>';
    }
    
    $html .= '<p><a href="/index.php?page=home">Return to Home</a></p>';
    $html .= '</body></html>';
    
    echo $html;
    exit;
}

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

// Homepage (PUBLIC - No authentication required)
if ($page === 'home') {
    try {
        require_once BASE_PATH . '/app/models/Event.php';
        
        // Create fallback event data in case of database failure
        $event = [
            'id' => 1,
            'title' => 'Dicksord Fest 2026 - Newcastle',
            'description' => '',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-22',
            'location' => 'Newcastle',
            'content' => '<p>Welcome to Dicksord Fest 2026! More details coming soon.</p>'
        ];
        $isAttending = false;
        
        // Try to fetch real event data from database
        try {
            $eventModel = new Event();
            $activeEvent = $eventModel->getActive();
            
            // If active event exists, use it instead of fallback
            if ($activeEvent) {
                $event = $activeEvent;
            } else {
                error_log('Warning: No active event found in database, using default event data');
            }
            
            // Check if user is logged in and attending
            if (isLoggedIn()) {
                $userId = getCurrentUserId();
                $attendance = $eventModel->getAttendance($userId, $event['id']);
                $isAttending = $attendance !== false;
            }
        } catch (Exception $dbError) {
            // Database connection failed, but we can still show the page with fallback data
            error_log('Warning: Database error on homepage, using fallback data: ' . $dbError->getMessage());
        }
        
        $pageTitle = 'Home';
        include BASE_PATH . '/app/views/public/home.php';
        exit;
    } catch (Exception $e) {
        error_log('Error loading homepage: ' . $e->getMessage());
        if (APP_DEBUG) {
            die('<html><body style="font-family: Arial; padding: 20px;"><h1>Error Loading Homepage</h1><p>' . 
                htmlspecialchars($e->getMessage()) . '</p><p><strong>Trace:</strong></p><pre>' . 
                htmlspecialchars($e->getTraceAsString()) . '</pre></body></html>');
        } else {
            die('<html><body style="font-family: Arial; padding: 20px;"><h1>Service Unavailable</h1>' .
                '<p>The website is currently experiencing technical difficulties.</p></body></html>');
        }
    }
}

// ============================================================================
// AUTHENTICATED ROUTES (Require login)
// ============================================================================

// All other pages require authentication
Auth::check();

// Log request details for debugging
error_log('=== AUTHENTICATED ROUTE REQUEST ===');
error_log('Page: ' . $page);
error_log('User ID: ' . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log('Is Admin: ' . (isset($_SESSION['is_admin']) ? ($_SESSION['is_admin'] ? 'TRUE' : 'FALSE') : 'NOT SET'));
error_log('Is Logged In: ' . (isLoggedIn() ? 'TRUE' : 'FALSE'));

// Wrap authenticated routes in try/catch to prevent blank 500 errors
try {
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

    // Get active event with fallback
    $event = $eventModel->getActive();
    
    // If no active event found, use fallback data
    // Note: ID 1 is used as it would be the natural first event in the database.
    // If this ID doesn't exist, database queries will simply return empty results,
    // which is the expected behavior when there's no active event.
    if (!$event || !isset($event['id'])) {
        error_log('Warning: No active event found for authenticated page request: ' . $page . ', using fallback data');
        $event = [
            'id' => 1,
            'title' => 'Dicksord Fest 2026 - Newcastle',
            'description' => '',
            'start_date' => '2026-11-20',
            'end_date' => '2026-11-22',
            'location' => 'Newcastle',
            'content' => '<p>Welcome to Dicksord Fest 2026! More details coming soon.</p>'
        ];
    }
    
    // Get current user ID for authenticated routes
    $userId = getCurrentUserId();
} catch (Exception $e) {
    // Log DETAILED error with full context
    error_log('===========================================');
    error_log('FATAL ERROR loading models or active event');
    error_log('Error: ' . $e->getMessage());
    error_log('File: ' . $e->getFile() . ':' . $e->getLine());
    error_log('Page requested: ' . ($page ?? 'UNKNOWN'));
    error_log('Stack trace: ' . $e->getTraceAsString());
    error_log('===========================================');
    
    // If debug mode, show the actual error
    if (APP_DEBUG) {
        die('<pre>' . htmlspecialchars($e->getMessage()) . "\n\n" . 
            htmlspecialchars($e->getTraceAsString()) . '</pre>');
    }
    
    // Render error page
    renderErrorPage(
        'Service Error',
        'We encountered an error loading the requested page. Please try again later.',
        $e->getMessage() . "\n\nStack trace:\n" . $e->getTraceAsString()
    );
}

// ============================================================================
// ROUTING
// ============================================================================

// Route requests based on page parameter
// Wrap in try/catch to prevent blank 500 errors during route execution
try {
    switch ($page) {
        // ========================================================================
        // USER ROUTES
        // ========================================================================
        
        case 'dashboard':
            $pageTitle = 'My Plans';
            $attendance = $eventModel->getAttendance($userId, $event['id']);
            $isAttending = $attendance !== false;
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
            
        // Admin Event Management
        case 'admin_event':
            AdminAuth::check();
            require_once BASE_PATH . '/app/controllers/AdminController.php';
            $adminController = new AdminController();
            
            if ($action === 'update') {
                $adminController->updateEvent();
            } else {
                $adminController->showEventEditor();
            }
            break;
            
        // Admin Activity Management
        case 'admin_activities':
            AdminAuth::check();
            require_once BASE_PATH . '/app/controllers/AdminController.php';
            $adminController = new AdminController();
            
            if ($action === 'create') {
                $adminController->createActivity();
            } elseif ($action === 'update') {
                $adminController->updateActivity();
            } elseif ($action === 'delete') {
                $adminController->deleteActivity();
            } else {
                $adminController->showActivityManager();
            }
            break;
            
        // Admin Meal Management
        case 'admin_meals':
            AdminAuth::check();
            require_once BASE_PATH . '/app/controllers/AdminController.php';
            $adminController = new AdminController();
            
            if ($action === 'create') {
                $adminController->createMeal();
            } elseif ($action === 'update') {
                $adminController->updateMeal();
            } elseif ($action === 'delete') {
                $adminController->deleteMeal();
            } else {
                $adminController->showMealManager();
            }
            break;
            
        // Admin Poll Management
        case 'admin_polls':
            AdminAuth::check();
            require_once BASE_PATH . '/app/controllers/AdminController.php';
            $adminController = new AdminController();
            
            if ($action === 'create') {
                $adminController->createPoll();
            } elseif ($action === 'update') {
                $adminController->updatePoll();
            } elseif ($action === 'delete') {
                $adminController->deletePoll();
            } else {
                $adminController->showPollManager();
            }
            break;
            
        // Admin Poll Category Management
        case 'admin_poll_categories':
            AdminAuth::check();
            require_once BASE_PATH . '/app/controllers/AdminController.php';
            $adminController = new AdminController();
            
            if ($action === 'create') {
                $adminController->createPollCategory();
            } elseif ($action === 'update') {
                $adminController->updatePollCategory();
            } elseif ($action === 'delete') {
                $adminController->deletePollCategory();
            } else {
                $adminController->showPollCategoryManager();
            }
            break;
            
        // Admin Hotel Management
        case 'admin_hotels':
            AdminAuth::check();
            require_once BASE_PATH . '/app/controllers/AdminController.php';
            $adminController = new AdminController();
            
            if ($action === 'create_hotel') {
                $adminController->createHotel();
            } elseif ($action === 'update_hotel') {
                $adminController->updateHotel();
            } elseif ($action === 'delete_hotel') {
                $adminController->deleteHotel();
            } elseif ($action === 'create_room') {
                $adminController->createRoom();
            } elseif ($action === 'update_room') {
                $adminController->updateRoom();
            } elseif ($action === 'delete_room') {
                $adminController->deleteRoom();
            } else {
                $adminController->showHotelManager();
            }
            break;
            
        // Admin User Management
        case 'admin_users':
            AdminAuth::check();
            require_once BASE_PATH . '/app/controllers/AdminController.php';
            $adminController = new AdminController();
            
            if ($action === 'toggle_admin') {
                $adminController->toggleAdmin();
            } elseif ($action === 'delete') {
                $adminController->deleteUser();
            } else {
                $adminController->showUserManager();
            }
            break;

        // ========================================================================
        // 404 - PAGE NOT FOUND
        // ========================================================================
        
        default:
            // Redirect unknown pages to home
            redirect('/index.php?page=home');
            break;
    }
} catch (Exception $e) {
    // Log DETAILED error with full context
    error_log('===========================================');
    error_log('FATAL ERROR during route execution');
    error_log('Error: ' . $e->getMessage());
    error_log('File: ' . $e->getFile() . ':' . $e->getLine());
    error_log('Page: ' . $page);
    error_log('Stack trace: ' . $e->getTraceAsString());
    error_log('===========================================');
    
    // If debug mode, show the actual error
    if (APP_DEBUG) {
        die('<pre>Route: ' . htmlspecialchars($page) . "\n\n" .
            htmlspecialchars($e->getMessage()) . "\n\n" . 
            htmlspecialchars($e->getTraceAsString()) . '</pre>');
    }
    
    // Render error page
    renderErrorPage(
        'Page Error',
        'We encountered an error loading this page. Please try again later.',
        'Page: ' . $page . "\n" . $e->getMessage() . "\n\nStack trace:\n" . $e->getTraceAsString()
    );
}
