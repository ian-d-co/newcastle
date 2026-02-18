<?php
/**
 * Dicksord Fest 2026 - Newcastle Event Management System
 * Admin Controller
 * 
 * Handles all admin operations for managing events, activities, meals, polls, hotels, and users.
 */

class AdminController {
    
    /**
     * Get the active event
     */
    private function getActiveEvent() {
        require_once BASE_PATH . '/app/models/Event.php';
        $eventModel = new Event();
        return $eventModel->getActive();
    }
    
    // ========================================================================
    // EVENT MANAGEMENT
    // ========================================================================
    
    /**
     * Show event editor
     */
    public function showEventEditor() {
        $event = $this->getActiveEvent();
        $pageTitle = 'Edit Event';
        $currentPage = 'admin';
        
        include BASE_PATH . '/app/views/admin/event.php';
    }
    
    /**
     * Update event
     */
    public function updateEvent() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            $sql = "UPDATE events SET 
                    title = :title,
                    description = :description,
                    start_date = :start_date,
                    end_date = :end_date,
                    location = :location,
                    content = :content,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id' => $data['id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'location' => $data['location'],
                'content' => $data['content']
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Event updated successfully']);
        } catch (Exception $e) {
            error_log('Error updating event: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update event'], 500);
        }
    }
    
    // ========================================================================
    // ACTIVITY MANAGEMENT
    // ========================================================================
    
    /**
     * Show activity manager
     */
    public function showActivityManager() {
        $event = $this->getActiveEvent();
        
        require_once BASE_PATH . '/app/models/Activity.php';
        $activityModel = new Activity();
        $activities = $activityModel->getAll($event['id']);
        
        // Get booking counts for each activity
        $db = getDbConnection();
        foreach ($activities as &$activity) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM activity_bookings WHERE activity_id = :activity_id");
            $stmt->execute(['activity_id' => $activity['id']]);
            $activity['booking_count'] = $stmt->fetch()['count'];
        }
        
        $pageTitle = 'Manage Activities';
        $currentPage = 'admin';
        
        include BASE_PATH . '/app/views/admin/activities.php';
    }
    
    /**
     * Create activity
     */
    public function createActivity() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $event = $this->getActiveEvent();
        
        try {
            $db = getDbConnection();
            
            $sql = "INSERT INTO activities (event_id, title, description, day, start_time, end_time, max_capacity, requires_prepayment, price)
                    VALUES (:event_id, :title, :description, :day, :start_time, :end_time, :max_capacity, :requires_prepayment, :price)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'event_id' => $event['id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'day' => $data['day'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'max_capacity' => $data['max_capacity'] ?? 20,
                'requires_prepayment' => $data['requires_prepayment'] ?? 0,
                'price' => $data['price'] ?? 0
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Activity created successfully', 'id' => $db->lastInsertId()]);
        } catch (Exception $e) {
            error_log('Error creating activity: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to create activity'], 500);
        }
    }
    
    /**
     * Update activity
     */
    public function updateActivity() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            $sql = "UPDATE activities SET 
                    title = :title,
                    description = :description,
                    day = :day,
                    start_time = :start_time,
                    end_time = :end_time,
                    max_capacity = :max_capacity,
                    requires_prepayment = :requires_prepayment,
                    price = :price
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id' => $data['id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'day' => $data['day'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'max_capacity' => $data['max_capacity'] ?? 20,
                'requires_prepayment' => $data['requires_prepayment'] ?? 0,
                'price' => $data['price'] ?? 0
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Activity updated successfully']);
        } catch (Exception $e) {
            error_log('Error updating activity: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update activity'], 500);
        }
    }
    
    /**
     * Delete activity
     */
    public function deleteActivity() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            // Delete bookings first
            $stmt = $db->prepare("DELETE FROM activity_bookings WHERE activity_id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            // Delete activity
            $stmt = $db->prepare("DELETE FROM activities WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Activity deleted successfully']);
        } catch (Exception $e) {
            error_log('Error deleting activity: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to delete activity'], 500);
        }
    }
    
    // ========================================================================
    // MEAL MANAGEMENT
    // ========================================================================
    
    /**
     * Show meal manager
     */
    public function showMealManager() {
        $event = $this->getActiveEvent();
        
        require_once BASE_PATH . '/app/models/Meal.php';
        $mealModel = new Meal();
        $meals = $mealModel->getAll($event['id']);
        
        // Get booking counts for each meal
        $db = getDbConnection();
        foreach ($meals as &$meal) {
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM meal_bookings WHERE meal_id = :meal_id");
            $stmt->execute(['meal_id' => $meal['id']]);
            $meal['booking_count'] = $stmt->fetch()['count'];
        }
        
        $pageTitle = 'Manage Meals';
        $currentPage = 'admin';
        
        include BASE_PATH . '/app/views/admin/meals.php';
    }
    
    /**
     * Create meal
     */
    public function createMeal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $event = $this->getActiveEvent();
        
        try {
            $db = getDbConnection();
            
            $sql = "INSERT INTO meals (event_id, title, description, day, start_time, end_time, max_capacity, requires_prepayment, price)
                    VALUES (:event_id, :title, :description, :day, :start_time, :end_time, :max_capacity, :requires_prepayment, :price)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'event_id' => $event['id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'day' => $data['day'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'max_capacity' => $data['max_capacity'] ?? 20,
                'requires_prepayment' => $data['requires_prepayment'] ?? 0,
                'price' => $data['price'] ?? 0
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Meal created successfully', 'id' => $db->lastInsertId()]);
        } catch (Exception $e) {
            error_log('Error creating meal: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to create meal'], 500);
        }
    }
    
    /**
     * Update meal
     */
    public function updateMeal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            $sql = "UPDATE meals SET 
                    title = :title,
                    description = :description,
                    day = :day,
                    start_time = :start_time,
                    end_time = :end_time,
                    max_capacity = :max_capacity,
                    requires_prepayment = :requires_prepayment,
                    price = :price
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id' => $data['id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'day' => $data['day'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'max_capacity' => $data['max_capacity'] ?? 20,
                'requires_prepayment' => $data['requires_prepayment'] ?? 0,
                'price' => $data['price'] ?? 0
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Meal updated successfully']);
        } catch (Exception $e) {
            error_log('Error updating meal: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update meal'], 500);
        }
    }
    
    /**
     * Delete meal
     */
    public function deleteMeal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            // Delete bookings first
            $stmt = $db->prepare("DELETE FROM meal_bookings WHERE meal_id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            // Delete meal
            $stmt = $db->prepare("DELETE FROM meals WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Meal deleted successfully']);
        } catch (Exception $e) {
            error_log('Error deleting meal: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to delete meal'], 500);
        }
    }
    
    // ========================================================================
    // POLL MANAGEMENT
    // ========================================================================
    
    /**
     * Show poll manager
     */
    public function showPollManager() {
        $event = $this->getActiveEvent();
        
        require_once BASE_PATH . '/app/models/Poll.php';
        $pollModel = new Poll();
        $polls = $pollModel->getAll($event['id']);
        
        // Get vote counts and options for each poll
        $db = getDbConnection();
        foreach ($polls as &$poll) {
            $stmt = $db->prepare("SELECT COUNT(DISTINCT user_id) as count FROM poll_votes WHERE poll_id = :poll_id");
            $stmt->execute(['poll_id' => $poll['id']]);
            $poll['vote_count'] = $stmt->fetch()['count'];
            
            $poll['options'] = $pollModel->getOptions($poll['id']);
        }
        
        $pageTitle = 'Manage Polls';
        $currentPage = 'admin';
        
        include BASE_PATH . '/app/views/admin/polls.php';
    }
    
    /**
     * Create poll
     */
    public function createPoll() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $event = $this->getActiveEvent();
        
        try {
            $db = getDbConnection();
            $db->beginTransaction();
            
            // Create poll
            $sql = "INSERT INTO polls (event_id, question, is_anonymous, is_multiple_choice, expires_at, is_active, created_by)
                    VALUES (:event_id, :question, :is_anonymous, :is_multiple_choice, :expires_at, 1, :created_by)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'event_id' => $event['id'],
                'question' => $data['question'],
                'is_anonymous' => $data['is_anonymous'] ?? 0,
                'is_multiple_choice' => $data['is_multiple_choice'] ?? 0,
                'expires_at' => $data['expires_at'] ?? null,
                'created_by' => getCurrentUserId()
            ]);
            
            $pollId = $db->lastInsertId();
            
            // Create poll options
            if (!empty($data['options'])) {
                $sql = "INSERT INTO poll_options (poll_id, option_text) VALUES (:poll_id, :option_text)";
                $stmt = $db->prepare($sql);
                
                foreach ($data['options'] as $option) {
                    $stmt->execute([
                        'poll_id' => $pollId,
                        'option_text' => $option
                    ]);
                }
            }
            
            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Poll created successfully', 'id' => $pollId]);
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Error creating poll: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to create poll'], 500);
        }
    }
    
    /**
     * Update poll
     */
    public function updatePoll() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            $sql = "UPDATE polls SET 
                    question = :question,
                    is_anonymous = :is_anonymous,
                    is_multiple_choice = :is_multiple_choice,
                    expires_at = :expires_at,
                    is_active = :is_active
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id' => $data['id'],
                'question' => $data['question'],
                'is_anonymous' => $data['is_anonymous'] ?? 0,
                'is_multiple_choice' => $data['is_multiple_choice'] ?? 0,
                'expires_at' => $data['expires_at'] ?? null,
                'is_active' => $data['is_active'] ?? 1
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Poll updated successfully']);
        } catch (Exception $e) {
            error_log('Error updating poll: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update poll'], 500);
        }
    }
    
    /**
     * Delete poll
     */
    public function deletePoll() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            // Delete votes first
            $stmt = $db->prepare("DELETE FROM poll_votes WHERE poll_id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            // Delete options
            $stmt = $db->prepare("DELETE FROM poll_options WHERE poll_id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            // Delete poll
            $stmt = $db->prepare("DELETE FROM polls WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Poll deleted successfully']);
        } catch (Exception $e) {
            error_log('Error deleting poll: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to delete poll'], 500);
        }
    }
    
    // ========================================================================
    // HOTEL MANAGEMENT
    // ========================================================================
    
    /**
     * Show hotel manager
     */
    public function showHotelManager() {
        $event = $this->getActiveEvent();
        
        require_once BASE_PATH . '/app/models/Hotel.php';
        $hotelModel = new Hotel();
        $hotels = $hotelModel->getAll($event['id']);
        
        // Get rooms for each hotel
        foreach ($hotels as &$hotel) {
            $hotel['rooms'] = $hotelModel->getRoomsByHotel($hotel['id']);
            
            // Get reservation count for each room
            $db = getDbConnection();
            foreach ($hotel['rooms'] as &$room) {
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM hotel_reservations WHERE room_id = :room_id");
                $stmt->execute(['room_id' => $room['id']]);
                $room['reservation_count'] = $stmt->fetch()['count'];
            }
        }
        
        $pageTitle = 'Manage Hotels';
        $currentPage = 'admin';
        
        include BASE_PATH . '/app/views/admin/hotels.php';
    }
    
    /**
     * Create hotel
     */
    public function createHotel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $event = $this->getActiveEvent();
        
        try {
            $db = getDbConnection();
            
            $sql = "INSERT INTO hotels (event_id, name, address, phone, website, description, created_at, updated_at)
                    VALUES (:event_id, :name, :address, :phone, :website, :description, NOW(), NOW())";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'event_id' => $event['id'],
                'name' => $data['name'],
                'address' => $data['address'] ?? '',
                'phone' => $data['phone'] ?? '',
                'website' => $data['website'] ?? '',
                'description' => $data['description'] ?? ''
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Hotel created successfully', 'id' => $db->lastInsertId()]);
        } catch (Exception $e) {
            error_log('Error creating hotel: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to create hotel'], 500);
        }
    }
    
    /**
     * Update hotel
     */
    public function updateHotel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            $sql = "UPDATE hotels SET 
                    name = :name,
                    address = :address,
                    phone = :phone,
                    website = :website,
                    description = :description,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id' => $data['id'],
                'name' => $data['name'],
                'address' => $data['address'] ?? '',
                'phone' => $data['phone'] ?? '',
                'website' => $data['website'] ?? '',
                'description' => $data['description'] ?? ''
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Hotel updated successfully']);
        } catch (Exception $e) {
            error_log('Error updating hotel: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update hotel'], 500);
        }
    }
    
    /**
     * Delete hotel
     */
    public function deleteHotel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            // Get all rooms for this hotel
            $stmt = $db->prepare("SELECT id FROM hotel_rooms WHERE hotel_id = :hotel_id");
            $stmt->execute(['hotel_id' => $data['id']]);
            $rooms = $stmt->fetchAll();
            
            // Delete reservations for each room
            foreach ($rooms as $room) {
                $stmt = $db->prepare("DELETE FROM hotel_reservations WHERE room_id = :room_id");
                $stmt->execute(['room_id' => $room['id']]);
            }
            
            // Delete rooms
            $stmt = $db->prepare("DELETE FROM hotel_rooms WHERE hotel_id = :hotel_id");
            $stmt->execute(['hotel_id' => $data['id']]);
            
            // Delete hotel
            $stmt = $db->prepare("DELETE FROM hotels WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Hotel deleted successfully']);
        } catch (Exception $e) {
            error_log('Error deleting hotel: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to delete hotel'], 500);
        }
    }
    
    /**
     * Create hotel room
     */
    public function createRoom() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            $sql = "INSERT INTO hotel_rooms (hotel_id, room_type, price_per_night, max_occupancy, available_rooms, created_at, updated_at)
                    VALUES (:hotel_id, :room_type, :price_per_night, :max_occupancy, :available_rooms, NOW(), NOW())";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'hotel_id' => $data['hotel_id'],
                'room_type' => $data['room_type'],
                'price_per_night' => $data['price_per_night'],
                'max_occupancy' => $data['max_occupancy'] ?? 2,
                'available_rooms' => $data['available_rooms'] ?? 1
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Room created successfully', 'id' => $db->lastInsertId()]);
        } catch (Exception $e) {
            error_log('Error creating room: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to create room'], 500);
        }
    }
    
    /**
     * Update hotel room
     */
    public function updateRoom() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            $sql = "UPDATE hotel_rooms SET 
                    room_type = :room_type,
                    price_per_night = :price_per_night,
                    max_occupancy = :max_occupancy,
                    available_rooms = :available_rooms,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id' => $data['id'],
                'room_type' => $data['room_type'],
                'price_per_night' => $data['price_per_night'],
                'max_occupancy' => $data['max_occupancy'] ?? 2,
                'available_rooms' => $data['available_rooms'] ?? 1
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Room updated successfully']);
        } catch (Exception $e) {
            error_log('Error updating room: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update room'], 500);
        }
    }
    
    /**
     * Delete hotel room
     */
    public function deleteRoom() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            // Delete reservations first
            $stmt = $db->prepare("DELETE FROM hotel_reservations WHERE room_id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            // Delete room
            $stmt = $db->prepare("DELETE FROM hotel_rooms WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Room deleted successfully']);
        } catch (Exception $e) {
            error_log('Error deleting room: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to delete room'], 500);
        }
    }
    
    // ========================================================================
    // USER MANAGEMENT
    // ========================================================================
    
    /**
     * Show user manager
     */
    public function showUserManager() {
        $event = $this->getActiveEvent();
        
        try {
            $db = getDbConnection();
            
            // Get all users with their attendance status
            $sql = "SELECT u.*, 
                    ea.id as attendance_id,
                    ea.days_attending,
                    ea.dietary_requirements,
                    ea.created_at as registered_at
                    FROM users u
                    LEFT JOIN event_attendees ea ON u.id = ea.user_id AND ea.event_id = :event_id
                    ORDER BY u.created_at DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['event_id' => $event['id']]);
            $users = $stmt->fetchAll();
            
            // Parse days_attending JSON for each user
            foreach ($users as &$user) {
                if ($user['days_attending']) {
                    $user['days_attending'] = json_decode($user['days_attending'], true);
                }
            }
            
            $pageTitle = 'Manage Users';
            $currentPage = 'admin';
            
            include BASE_PATH . '/app/views/admin/users.php';
        } catch (Exception $e) {
            error_log('Error loading users: ' . $e->getMessage());
            renderErrorPage('Error', 'Failed to load users');
        }
    }
    
    /**
     * Toggle user admin status
     */
    public function toggleAdmin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            
            // Get current admin status
            $stmt = $db->prepare("SELECT is_admin FROM users WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            $user = $stmt->fetch();
            
            if (!$user) {
                jsonResponse(['success' => false, 'message' => 'User not found'], 404);
            }
            
            // Toggle admin status
            $newStatus = $user['is_admin'] ? 0 : 1;
            
            $stmt = $db->prepare("UPDATE users SET is_admin = :is_admin WHERE id = :id");
            $stmt->execute([
                'id' => $data['id'],
                'is_admin' => $newStatus
            ]);
            
            jsonResponse([
                'success' => true, 
                'message' => 'User admin status updated successfully',
                'is_admin' => (bool)$newStatus
            ]);
        } catch (Exception $e) {
            error_log('Error toggling admin status: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update user'], 500);
        }
    }
    
    /**
     * Delete user
     */
    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        try {
            $db = getDbConnection();
            $db->beginTransaction();
            
            // Delete all user data
            $userId = $data['id'];
            
            // Event attendance
            $stmt = $db->prepare("DELETE FROM event_attendees WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            
            // Activity bookings
            $stmt = $db->prepare("DELETE FROM activity_bookings WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            
            // Meal bookings
            $stmt = $db->prepare("DELETE FROM meal_bookings WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            
            // Poll votes
            $stmt = $db->prepare("DELETE FROM poll_votes WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            
            // Carshare offers and bookings
            $stmt = $db->prepare("DELETE FROM carshare_bookings WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $stmt = $db->prepare("DELETE FROM carshare_offers WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            
            // Hosting offers and bookings
            $stmt = $db->prepare("DELETE FROM hosting_bookings WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $stmt = $db->prepare("DELETE FROM hosting_offers WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            
            // Hotel reservations
            $stmt = $db->prepare("DELETE FROM hotel_reservations WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            
            // Finally, delete the user
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute(['id' => $userId]);
            
            $db->commit();
            jsonResponse(['success' => true, 'message' => 'User deleted successfully']);
        } catch (Exception $e) {
            $db->rollBack();
            error_log('Error deleting user: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to delete user'], 500);
        }
    }
}
