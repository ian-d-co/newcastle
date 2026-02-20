<?php
/**
 * Dicksord Fest 2026 - Newcastle Event Management System
 * Admin Controller
 * 
 * Handles all admin operations for managing events, activities, meals, polls, hotels, and users.
 */

class AdminController {

    const ALLOWED_DAYS = ['Friday', 'Saturday', 'Sunday'];
    
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
        unset($activity);
        
        $pageTitle = 'Manage Activities';
        $currentPage = 'admin';
        
        include BASE_PATH . '/app/views/admin/activities.php';
    }
    
    /**
     * Create activity
     */
    public function createActivity() {
        error_log('AdminController::createActivity() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::createActivity() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::createActivity() - JSON decoded successfully');
            
            // Get and validate active event
            $event = $this->getActiveEvent();
            if (!$event || !isset($event['id'])) {
                error_log('AdminController::createActivity() - No active event found');
                jsonResponse(['success' => false, 'message' => 'No active event found'], 400);
            }
            error_log('AdminController::createActivity() - Active event ID: ' . $event['id']);
            
            // Validate required fields
            $requiredFields = ['title', 'day', 'start_time', 'end_time'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || trim($data[$field]) === '') {
                    error_log("AdminController::createActivity() - Missing required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
            }

            // Normalize and whitelist the day value
            $normalizedDay = ucfirst(strtolower(trim($data['day'])));
            if (!in_array($normalizedDay, self::ALLOWED_DAYS, true)) {
                error_log("AdminController::createActivity() - Invalid day value: " . $data['day']);
                jsonResponse(['success' => false, 'message' => 'Invalid day. Must be Friday, Saturday, or Sunday'], 400);
            }
            
            $db = getDbConnection();
            
            $sql = "INSERT INTO activities (event_id, title, description, link, day, start_time, end_time, max_capacity, requires_prepayment, no_booking_required, booking_open, price, total_price, deposit_amount, prepayment_required, pay_on_arrival, confirmation_deadline, payment_deadline)
                    VALUES (:event_id, :title, :description, :link, :day, :start_time, :end_time, :max_capacity, :requires_prepayment, :no_booking_required, :booking_open, :price, :total_price, :deposit_amount, :prepayment_required, :pay_on_arrival, :confirmation_deadline, :payment_deadline)";
            
            $params = [
                'event_id' => $event['id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'link' => $data['link'] ?? null,
                'day' => $normalizedDay,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'max_capacity' => $data['max_capacity'] ?? 20,
                'requires_prepayment' => $data['requires_prepayment'] ?? 0,
                'no_booking_required' => $data['no_booking_required'] ?? 0,
                'booking_open' => isset($data['booking_open']) ? (int)$data['booking_open'] : 1,
                'price' => $data['price'] ?? 0,
                'total_price' => $data['total_price'] ?? 0,
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'prepayment_required' => $data['prepayment_required'] ?? 0,
                'pay_on_arrival' => $data['pay_on_arrival'] ?? 0,
                'confirmation_deadline' => $data['confirmation_deadline'] ?: null,
                'payment_deadline' => $data['payment_deadline'] ?: null
            ];
            error_log('AdminController::createActivity() - SQL params: ' . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $activityId = $db->lastInsertId();
            error_log('AdminController::createActivity() - Activity created with ID: ' . $activityId);
            
            jsonResponse(['success' => true, 'message' => 'Activity created successfully', 'id' => $activityId]);
        } catch (Exception $e) {
            error_log('AdminController::createActivity() - Exception: ' . $e->getMessage());
            error_log('AdminController::createActivity() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::createActivity() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to create activity'], 500);
        }
    }
    
    /**
     * Update activity
     */
    public function updateActivity() {
        error_log('AdminController::updateActivity() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::updateActivity() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::updateActivity() - JSON decoded successfully');
            
            // Validate required fields
            $requiredFields = ['id', 'title', 'day', 'start_time', 'end_time'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    error_log("AdminController::updateActivity() - Missing required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
                // For string fields, also check they're not empty after trimming
                if ($field !== 'id' && trim($data[$field]) === '') {
                    error_log("AdminController::updateActivity() - Empty required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
            }

            // Normalize and whitelist the day value
            $normalizedDay = ucfirst(strtolower(trim($data['day'])));
            if (!in_array($normalizedDay, self::ALLOWED_DAYS, true)) {
                error_log("AdminController::updateActivity() - Invalid day value: " . $data['day']);
                jsonResponse(['success' => false, 'message' => 'Invalid day. Must be Friday, Saturday, or Sunday'], 400);
            }
            
            $db = getDbConnection();
            
            $sql = "UPDATE activities SET 
                    title = :title,
                    description = :description,
                    link = :link,
                    day = :day,
                    start_time = :start_time,
                    end_time = :end_time,
                    max_capacity = :max_capacity,
                    requires_prepayment = :requires_prepayment,
                    no_booking_required = :no_booking_required,
                    booking_open = :booking_open,
                    price = :price,
                    total_price = :total_price,
                    deposit_amount = :deposit_amount,
                    prepayment_required = :prepayment_required,
                    pay_on_arrival = :pay_on_arrival,
                    confirmation_deadline = :confirmation_deadline,
                    payment_deadline = :payment_deadline
                    WHERE id = :id";
            
            $params = [
                'id' => $data['id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'link' => $data['link'] ?? null,
                'day' => $normalizedDay,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'max_capacity' => $data['max_capacity'] ?? 20,
                'requires_prepayment' => $data['requires_prepayment'] ?? 0,
                'no_booking_required' => $data['no_booking_required'] ?? 0,
                'booking_open' => isset($data['booking_open']) ? (int)$data['booking_open'] : 1,
                'price' => $data['price'] ?? 0,
                'total_price' => $data['total_price'] ?? 0,
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'prepayment_required' => $data['prepayment_required'] ?? 0,
                'pay_on_arrival' => $data['pay_on_arrival'] ?? 0,
                'confirmation_deadline' => $data['confirmation_deadline'] ?: null,
                'payment_deadline' => $data['payment_deadline'] ?: null
            ];
            error_log('AdminController::updateActivity() - SQL params: ' . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            error_log('AdminController::updateActivity() - Activity updated with ID: ' . $data['id']);
            
            jsonResponse(['success' => true, 'message' => 'Activity updated successfully']);
        } catch (Exception $e) {
            error_log('AdminController::updateActivity() - Exception: ' . $e->getMessage());
            error_log('AdminController::updateActivity() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::updateActivity() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to update activity'], 500);
        }
    }
    
    /**
     * Delete activity
     */
    public function deleteActivity() {
        error_log('AdminController::deleteActivity() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::deleteActivity() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::deleteActivity() - JSON decoded successfully');
            
            // Validate required fields
            if (!isset($data['id'])) {
                error_log('AdminController::deleteActivity() - Missing required field: id');
                jsonResponse(['success' => false, 'message' => 'Missing required field: id'], 400);
            }
            
            $db = getDbConnection();
            
            error_log('AdminController::deleteActivity() - Deleting activity ID: ' . $data['id']);
            
            // Delete bookings first
            $stmt = $db->prepare("DELETE FROM activity_bookings WHERE activity_id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            // Delete activity
            $stmt = $db->prepare("DELETE FROM activities WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            error_log('AdminController::deleteActivity() - Activity deleted with ID: ' . $data['id']);
            
            jsonResponse(['success' => true, 'message' => 'Activity deleted successfully']);
        } catch (Exception $e) {
            error_log('AdminController::deleteActivity() - Exception: ' . $e->getMessage());
            error_log('AdminController::deleteActivity() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::deleteActivity() - Trace: ' . $e->getTraceAsString());
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
        unset($meal);
        
        $pageTitle = 'Manage Meals';
        $currentPage = 'admin';
        
        include BASE_PATH . '/app/views/admin/meals.php';
    }
    
    /**
     * Create meal
     */
    public function createMeal() {
        error_log('AdminController::createMeal() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::createMeal() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::createMeal() - JSON decoded successfully');
            
            // Get and validate active event
            $event = $this->getActiveEvent();
            if (!$event || !isset($event['id'])) {
                error_log('AdminController::createMeal() - No active event found');
                jsonResponse(['success' => false, 'message' => 'No active event found'], 400);
            }
            error_log('AdminController::createMeal() - Active event ID: ' . $event['id']);
            
            // Validate required fields
            $requiredFields = ['title', 'day', 'start_time', 'end_time'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || trim($data[$field]) === '') {
                    error_log("AdminController::createMeal() - Missing required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
            }
            
            $db = getDbConnection();
            
            $sql = "INSERT INTO meals (event_id, title, description, link, day, start_time, end_time, max_capacity, requires_prepayment, no_booking_required, booking_open, price, total_price, deposit_amount, prepayment_required, pay_on_arrival, confirmation_deadline, payment_deadline)
                    VALUES (:event_id, :title, :description, :link, :day, :start_time, :end_time, :max_capacity, :requires_prepayment, :no_booking_required, :booking_open, :price, :total_price, :deposit_amount, :prepayment_required, :pay_on_arrival, :confirmation_deadline, :payment_deadline)";
            
            $params = [
                'event_id' => $event['id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'link' => $data['link'] ?? null,
                'day' => $data['day'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'max_capacity' => $data['max_capacity'] ?? 20,
                'requires_prepayment' => $data['requires_prepayment'] ?? 0,
                'no_booking_required' => $data['no_booking_required'] ?? 0,
                'booking_open' => isset($data['booking_open']) ? (int)$data['booking_open'] : 1,
                'price' => $data['price'] ?? 0,
                'total_price' => $data['total_price'] ?? 0,
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'prepayment_required' => $data['prepayment_required'] ?? 0,
                'pay_on_arrival' => $data['pay_on_arrival'] ?? 0,
                'confirmation_deadline' => $data['confirmation_deadline'] ?: null,
                'payment_deadline' => $data['payment_deadline'] ?: null
            ];
            error_log('AdminController::createMeal() - SQL params: ' . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $mealId = $db->lastInsertId();
            error_log('AdminController::createMeal() - Meal created with ID: ' . $mealId);
            
            jsonResponse(['success' => true, 'message' => 'Meal created successfully', 'id' => $mealId]);
        } catch (Exception $e) {
            error_log('AdminController::createMeal() - Exception: ' . $e->getMessage());
            error_log('AdminController::createMeal() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::createMeal() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to create meal'], 500);
        }
    }
    
    /**
     * Update meal
     */
    public function updateMeal() {
        error_log('AdminController::updateMeal() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::updateMeal() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::updateMeal() - JSON decoded successfully');
            
            // Validate required fields
            $requiredFields = ['id', 'title', 'day', 'start_time', 'end_time'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    error_log("AdminController::updateMeal() - Missing required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
                // For string fields, also check they're not empty after trimming
                if ($field !== 'id' && trim($data[$field]) === '') {
                    error_log("AdminController::updateMeal() - Empty required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
            }
            
            $db = getDbConnection();
            
            $sql = "UPDATE meals SET 
                    title = :title,
                    description = :description,
                    link = :link,
                    day = :day,
                    start_time = :start_time,
                    end_time = :end_time,
                    max_capacity = :max_capacity,
                    requires_prepayment = :requires_prepayment,
                    no_booking_required = :no_booking_required,
                    booking_open = :booking_open,
                    price = :price,
                    total_price = :total_price,
                    deposit_amount = :deposit_amount,
                    prepayment_required = :prepayment_required,
                    pay_on_arrival = :pay_on_arrival,
                    confirmation_deadline = :confirmation_deadline,
                    payment_deadline = :payment_deadline
                    WHERE id = :id";
            
            $params = [
                'id' => $data['id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'link' => $data['link'] ?? null,
                'day' => $data['day'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'max_capacity' => $data['max_capacity'] ?? 20,
                'requires_prepayment' => $data['requires_prepayment'] ?? 0,
                'no_booking_required' => $data['no_booking_required'] ?? 0,
                'booking_open' => isset($data['booking_open']) ? (int)$data['booking_open'] : 1,
                'price' => $data['price'] ?? 0,
                'total_price' => $data['total_price'] ?? 0,
                'deposit_amount' => $data['deposit_amount'] ?? 0,
                'prepayment_required' => $data['prepayment_required'] ?? 0,
                'pay_on_arrival' => $data['pay_on_arrival'] ?? 0,
                'confirmation_deadline' => $data['confirmation_deadline'] ?: null,
                'payment_deadline' => $data['payment_deadline'] ?: null
            ];
            error_log('AdminController::updateMeal() - SQL params: ' . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            error_log('AdminController::updateMeal() - Meal updated with ID: ' . $data['id']);
            
            jsonResponse(['success' => true, 'message' => 'Meal updated successfully']);
        } catch (Exception $e) {
            error_log('AdminController::updateMeal() - Exception: ' . $e->getMessage());
            error_log('AdminController::updateMeal() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::updateMeal() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to update meal'], 500);
        }
    }
    
    /**
     * Delete meal
     */
    public function deleteMeal() {
        error_log('AdminController::deleteMeal() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::deleteMeal() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::deleteMeal() - JSON decoded successfully');
            
            // Validate required fields
            if (!isset($data['id'])) {
                error_log('AdminController::deleteMeal() - Missing required field: id');
                jsonResponse(['success' => false, 'message' => 'Missing required field: id'], 400);
            }
            
            $db = getDbConnection();
            
            error_log('AdminController::deleteMeal() - Deleting meal ID: ' . $data['id']);
            
            // Delete bookings first
            $stmt = $db->prepare("DELETE FROM meal_bookings WHERE meal_id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            // Delete meal
            $stmt = $db->prepare("DELETE FROM meals WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            error_log('AdminController::deleteMeal() - Meal deleted with ID: ' . $data['id']);
            
            jsonResponse(['success' => true, 'message' => 'Meal deleted successfully']);
        } catch (Exception $e) {
            error_log('AdminController::deleteMeal() - Exception: ' . $e->getMessage());
            error_log('AdminController::deleteMeal() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::deleteMeal() - Trace: ' . $e->getTraceAsString());
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
        unset($poll);
        
        // Get categories for dropdown
        $stmt = $db->query("SELECT * FROM poll_categories ORDER BY display_order, name");
        $categories = $stmt->fetchAll();
        
        $pageTitle = 'Manage Polls';
        $currentPage = 'admin';
        
        include BASE_PATH . '/app/views/admin/polls.php';
    }
    
    /**
     * Create poll
     */
    public function createPoll() {
        error_log('AdminController::createPoll() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::createPoll() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::createPoll() - JSON decoded successfully');
            
            // Get and validate active event
            $event = $this->getActiveEvent();
            if (!$event || !isset($event['id'])) {
                error_log('AdminController::createPoll() - No active event found');
                jsonResponse(['success' => false, 'message' => 'No active event found'], 400);
            }
            error_log('AdminController::createPoll() - Active event ID: ' . $event['id']);
            
            // Validate required fields
            if (!isset($data['question']) || trim($data['question']) === '') {
                error_log('AdminController::createPoll() - Missing required field: question');
                jsonResponse(['success' => false, 'message' => 'Missing required field: question'], 400);
            }
            
            if (empty($data['options']) || !is_array($data['options'])) {
                error_log('AdminController::createPoll() - Missing or invalid poll options');
                jsonResponse(['success' => false, 'message' => 'Poll must have at least one option'], 400);
            }
            
            $db = getDbConnection();
            $db->beginTransaction();
            
            // Create poll
            $sql = "INSERT INTO polls (event_id, category_id, question, is_anonymous, is_multiple_choice, expires_at, is_active, created_by)
                    VALUES (:event_id, :category_id, :question, :is_anonymous, :is_multiple_choice, :expires_at, 1, :created_by)";
            
            $params = [
                'event_id' => $event['id'],
                'category_id' => !empty($data['category_id']) ? (int)$data['category_id'] : null,
                'question' => $data['question'],
                'is_anonymous' => $data['is_anonymous'] ?? 0,
                'is_multiple_choice' => $data['is_multiple_choice'] ?? 0,
                'expires_at' => $data['expires_at'] ?? null,
                'created_by' => getCurrentUserId()
            ];
            error_log('AdminController::createPoll() - SQL params: ' . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $pollId = $db->lastInsertId();
            error_log('AdminController::createPoll() - Poll created with ID: ' . $pollId);
            
            // Create poll options
            $sql = "INSERT INTO poll_options (poll_id, option_text) VALUES (:poll_id, :option_text)";
            $stmt = $db->prepare($sql);
            
            foreach ($data['options'] as $option) {
                $stmt->execute([
                    'poll_id' => $pollId,
                    'option_text' => $option
                ]);
            }
            error_log('AdminController::createPoll() - Added ' . count($data['options']) . ' options');
            
            $db->commit();
            jsonResponse(['success' => true, 'message' => 'Poll created successfully', 'id' => $pollId]);
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            error_log('AdminController::createPoll() - Exception: ' . $e->getMessage());
            error_log('AdminController::createPoll() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::createPoll() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to create poll'], 500);
        }
    }
    
    /**
     * Update poll
     */
    public function updatePoll() {
        error_log('AdminController::updatePoll() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::updatePoll() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::updatePoll() - JSON decoded successfully');
            
            // Validate required fields
            $requiredFields = ['id', 'question'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    error_log("AdminController::updatePoll() - Missing required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
                // For string fields, also check they're not empty after trimming
                if ($field !== 'id' && trim($data[$field]) === '') {
                    error_log("AdminController::updatePoll() - Empty required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
            }
            
            $db = getDbConnection();
            
            $sql = "UPDATE polls SET 
                    question = :question,
                    category_id = :category_id,
                    is_anonymous = :is_anonymous,
                    is_multiple_choice = :is_multiple_choice,
                    expires_at = :expires_at,
                    is_active = :is_active
                    WHERE id = :id";
            
            $params = [
                'id' => $data['id'],
                'question' => $data['question'],
                'category_id' => !empty($data['category_id']) ? (int)$data['category_id'] : null,
                'is_anonymous' => $data['is_anonymous'] ?? 0,
                'is_multiple_choice' => $data['is_multiple_choice'] ?? 0,
                'expires_at' => $data['expires_at'] ?? null,
                'is_active' => $data['is_active'] ?? 1
            ];
            error_log('AdminController::updatePoll() - SQL params: ' . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            error_log('AdminController::updatePoll() - Poll updated with ID: ' . $data['id']);
            
            jsonResponse(['success' => true, 'message' => 'Poll updated successfully']);
        } catch (Exception $e) {
            error_log('AdminController::updatePoll() - Exception: ' . $e->getMessage());
            error_log('AdminController::updatePoll() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::updatePoll() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to update poll'], 500);
        }
    }
    
    /**
     * Delete poll
     */
    public function deletePoll() {
        error_log('AdminController::deletePoll() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::deletePoll() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::deletePoll() - JSON decoded successfully');
            
            // Validate required fields
            if (!isset($data['id'])) {
                error_log('AdminController::deletePoll() - Missing required field: id');
                jsonResponse(['success' => false, 'message' => 'Missing required field: id'], 400);
            }
            
            $db = getDbConnection();
            
            error_log('AdminController::deletePoll() - Deleting poll ID: ' . $data['id']);
            
            // Delete votes first
            $stmt = $db->prepare("DELETE FROM poll_votes WHERE poll_id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            // Delete options
            $stmt = $db->prepare("DELETE FROM poll_options WHERE poll_id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            // Delete poll
            $stmt = $db->prepare("DELETE FROM polls WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            error_log('AdminController::deletePoll() - Poll deleted with ID: ' . $data['id']);
            
            jsonResponse(['success' => true, 'message' => 'Poll deleted successfully']);
        } catch (Exception $e) {
            error_log('AdminController::deletePoll() - Exception: ' . $e->getMessage());
            error_log('AdminController::deletePoll() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::deletePoll() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to delete poll'], 500);
        }
    }
    
    // ========================================================================
    // POLL CATEGORY MANAGEMENT
    // ========================================================================
    
    /**
     * Show poll category manager
     */
    public function showPollCategoryManager() {
        try {
            $db = getDbConnection();
            
            $sql = "SELECT pc.*, COUNT(p.id) as poll_count 
                    FROM poll_categories pc
                    LEFT JOIN polls p ON pc.id = p.category_id
                    GROUP BY pc.id
                    ORDER BY pc.display_order, pc.name";
            
            $stmt = $db->query($sql);
            $categories = $stmt->fetchAll();
            
            $pageTitle = 'Manage Poll Categories';
            $currentPage = 'admin';
            
            include BASE_PATH . '/app/views/admin/poll-categories.php';
        } catch (Exception $e) {
            error_log('AdminController::showPollCategoryManager() - Exception: ' . $e->getMessage());
            error_log('AdminController::showPollCategoryManager() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::showPollCategoryManager() - Trace: ' . $e->getTraceAsString());
            renderErrorPage('Error', 'Failed to load poll categories');
        }
    }
    
    /**
     * Create poll category
     */
    public function createPollCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
        }
        
        if (!isset($data['name']) || trim($data['name']) === '') {
            jsonResponse(['success' => false, 'message' => 'Category name is required'], 400);
        }
        
        try {
            $db = getDbConnection();
            
            $stmt = $db->prepare("INSERT INTO poll_categories (name, display_order) VALUES (:name, :display_order)");
            $stmt->execute([
                'name' => trim($data['name']),
                'display_order' => isset($data['display_order']) ? (int)$data['display_order'] : 999
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Category created successfully', 'id' => $db->lastInsertId()]);
        } catch (Exception $e) {
            error_log('Error creating poll category: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to create category'], 500);
        }
    }
    
    /**
     * Update poll category
     */
    public function updatePollCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
        }
        
        if (!isset($data['id'])) {
            jsonResponse(['success' => false, 'message' => 'Category ID is required'], 400);
        }
        if (!isset($data['name']) || trim($data['name']) === '') {
            jsonResponse(['success' => false, 'message' => 'Category name is required'], 400);
        }
        
        try {
            $db = getDbConnection();
            
            $stmt = $db->prepare("UPDATE poll_categories SET name = :name, display_order = :display_order WHERE id = :id");
            $stmt->execute([
                'id' => (int)$data['id'],
                'name' => trim($data['name']),
                'display_order' => isset($data['display_order']) ? (int)$data['display_order'] : 999
            ]);
            
            jsonResponse(['success' => true, 'message' => 'Category updated successfully']);
        } catch (Exception $e) {
            error_log('Error updating poll category: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update category'], 500);
        }
    }
    
    /**
     * Delete poll category
     */
    public function deletePollCategory() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
        }
        
        if (!isset($data['id'])) {
            jsonResponse(['success' => false, 'message' => 'Category ID is required'], 400);
        }
        
        try {
            $db = getDbConnection();
            
            // Check if category has polls
            $stmt = $db->prepare("SELECT COUNT(*) as count FROM polls WHERE category_id = :id");
            $stmt->execute(['id' => (int)$data['id']]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                jsonResponse(['success' => false, 'message' => 'Cannot delete category that has polls assigned to it'], 400);
                return;
            }
            
            $stmt = $db->prepare("DELETE FROM poll_categories WHERE id = :id");
            $stmt->execute(['id' => (int)$data['id']]);
            
            jsonResponse(['success' => true, 'message' => 'Category deleted successfully']);
        } catch (Exception $e) {
            error_log('Error deleting poll category: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to delete category'], 500);
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
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM room_reservations WHERE hotel_room_id = :hotel_room_id");
                $stmt->execute(['hotel_room_id' => $room['id']]);
                $room['reservation_count'] = $stmt->fetch()['count'];
            }
            unset($room);
        }
        unset($hotel);
        
        $pageTitle = 'Manage Hotels';
        $currentPage = 'admin';
        
        include BASE_PATH . '/app/views/admin/hotels.php';
    }
    
    /**
     * Create hotel
     */
    public function createHotel() {
        error_log('AdminController::createHotel() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::createHotel() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::createHotel() - JSON decoded successfully');
            
            // Get and validate active event
            $event = $this->getActiveEvent();
            if (!$event || !isset($event['id'])) {
                error_log('AdminController::createHotel() - No active event found');
                jsonResponse(['success' => false, 'message' => 'No active event found'], 400);
            }
            error_log('AdminController::createHotel() - Active event ID: ' . $event['id']);
            
            // Validate required fields
            if (!isset($data['name']) || trim($data['name']) === '') {
                error_log('AdminController::createHotel() - Missing required field: name');
                jsonResponse(['success' => false, 'message' => 'Missing required field: name'], 400);
            }
            
            $db = getDbConnection();
            
            $sql = "INSERT INTO hotels (event_id, name, description, location, contact_phone, contact_email, link, status)
                    VALUES (:event_id, :name, :description, :location, :contact_phone, :contact_email, :link, 'active')";
            
            $params = [
                'event_id' => $event['id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'link' => $data['link'] ?? null
            ];
            error_log('AdminController::createHotel() - SQL params: ' . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $hotelId = $db->lastInsertId();
            error_log('AdminController::createHotel() - Hotel created with ID: ' . $hotelId);
            
            jsonResponse(['success' => true, 'message' => 'Hotel created successfully', 'id' => $hotelId]);
        } catch (Exception $e) {
            error_log('AdminController::createHotel() - Exception: ' . $e->getMessage());
            error_log('AdminController::createHotel() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::createHotel() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to create hotel'], 500);
        }
    }
    
    /**
     * Update hotel
     */
    public function updateHotel() {
        error_log('AdminController::updateHotel() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::updateHotel() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::updateHotel() - JSON decoded successfully');
            
            // Validate required fields
            $requiredFields = ['id', 'name'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    error_log("AdminController::updateHotel() - Missing required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
                // For string fields, also check they're not empty after trimming
                if ($field !== 'id' && trim($data[$field]) === '') {
                    error_log("AdminController::updateHotel() - Empty required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
            }
            
            $db = getDbConnection();
            
            $sql = "UPDATE hotels SET 
                    name = :name,
                    location = :location,
                    contact_phone = :contact_phone,
                    contact_email = :contact_email,
                    description = :description,
                    link = :link,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $params = [
                'id' => $data['id'],
                'name' => $data['name'],
                'location' => $data['location'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'description' => $data['description'] ?? null,
                'link' => $data['link'] ?? null
            ];
            error_log('AdminController::updateHotel() - SQL params: ' . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            error_log('AdminController::updateHotel() - Hotel updated with ID: ' . $data['id']);
            
            jsonResponse(['success' => true, 'message' => 'Hotel updated successfully']);
        } catch (Exception $e) {
            error_log('AdminController::updateHotel() - Exception: ' . $e->getMessage());
            error_log('AdminController::updateHotel() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::updateHotel() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to update hotel'], 500);
        }
    }
    
    /**
     * Delete hotel
     */
    public function deleteHotel() {
        error_log('AdminController::deleteHotel() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::deleteHotel() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::deleteHotel() - JSON decoded successfully');
            
            // Validate required fields
            if (!isset($data['id'])) {
                error_log('AdminController::deleteHotel() - Missing required field: id');
                jsonResponse(['success' => false, 'message' => 'Missing required field: id'], 400);
            }
            
            $db = getDbConnection();
            
            error_log('AdminController::deleteHotel() - Deleting hotel ID: ' . $data['id']);
            
            // Get all rooms for this hotel
            $stmt = $db->prepare("SELECT id FROM hotel_rooms WHERE hotel_id = :hotel_id");
            $stmt->execute(['hotel_id' => $data['id']]);
            $rooms = $stmt->fetchAll();
            
            // Delete reservations for each room
            foreach ($rooms as $room) {
                $stmt = $db->prepare("DELETE FROM room_reservations WHERE hotel_room_id = :hotel_room_id");
                $stmt->execute(['hotel_room_id' => $room['id']]);
            }
            
            // Delete rooms
            $stmt = $db->prepare("DELETE FROM hotel_rooms WHERE hotel_id = :hotel_id");
            $stmt->execute(['hotel_id' => $data['id']]);
            
            // Delete hotel
            $stmt = $db->prepare("DELETE FROM hotels WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            error_log('AdminController::deleteHotel() - Hotel deleted with ID: ' . $data['id']);
            
            jsonResponse(['success' => true, 'message' => 'Hotel deleted successfully']);
        } catch (Exception $e) {
            error_log('AdminController::deleteHotel() - Exception: ' . $e->getMessage());
            error_log('AdminController::deleteHotel() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::deleteHotel() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to delete hotel'], 500);
        }
    }
    
    /**
     * Create hotel room
     */
    public function createRoom() {
        error_log('AdminController::createRoom() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::createRoom() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::createRoom() - JSON decoded successfully');
            
            // Validate required fields
            $requiredFields = ['hotel_id', 'room_type'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    error_log("AdminController::createRoom() - Missing required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
                // For string fields, also check they're not empty after trimming
                if ($field === 'room_type' && trim($data[$field]) === '') {
                    error_log("AdminController::createRoom() - Empty required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
            }
            
            $db = getDbConnection();
            
            $sql = "INSERT INTO hotel_rooms (hotel_id, room_type, price, simple_price_type, capacity, quantity_available, quantity_reserved, status, booking_open,
                    single_price_friday, single_price_saturday, double_price_friday, double_price_saturday,
                    triple_price_friday, triple_price_saturday, breakfast_included, book_direct_with_hotel,
                    book_with_group, group_payment_due)
                    VALUES (:hotel_id, :room_type, :price, :simple_price_type, :capacity, :quantity_available, 0, 'available', :booking_open,
                    :single_price_friday, :single_price_saturday, :double_price_friday, :double_price_saturday,
                    :triple_price_friday, :triple_price_saturday, :breakfast_included, :book_direct_with_hotel,
                    :book_with_group, :group_payment_due)";
            
            $params = [
                'hotel_id' => (int)$data['hotel_id'],
                'room_type' => $data['room_type'],
                'price' => (float)$data['price'],
                'simple_price_type' => in_array($data['simple_price_type'] ?? '', ['per_night', 'both_nights']) ? $data['simple_price_type'] : 'per_night',
                'capacity' => (int)($data['capacity'] ?? 2),
                'quantity_available' => (int)($data['quantity_available'] ?? 1),
                'booking_open' => isset($data['booking_open']) ? (int)$data['booking_open'] : 1,
                'single_price_friday' => (float)($data['single_price_friday'] ?? 0),
                'single_price_saturday' => (float)($data['single_price_saturday'] ?? 0),
                'double_price_friday' => (float)($data['double_price_friday'] ?? 0),
                'double_price_saturday' => (float)($data['double_price_saturday'] ?? 0),
                'triple_price_friday' => (float)($data['triple_price_friday'] ?? 0),
                'triple_price_saturday' => (float)($data['triple_price_saturday'] ?? 0),
                'breakfast_included' => !empty($data['breakfast_included']) ? (int)$data['breakfast_included'] : 0,
                'book_direct_with_hotel' => !empty($data['book_direct_with_hotel']) ? (int)$data['book_direct_with_hotel'] : 0,
                'book_with_group' => !empty($data['book_with_group']) ? (int)$data['book_with_group'] : 0,
                'group_payment_due' => !empty($data['group_payment_due']) ? $data['group_payment_due'] : null
            ];
            error_log('AdminController::createRoom() - SQL params: ' . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            $roomId = $db->lastInsertId();
            error_log('AdminController::createRoom() - Room created with ID: ' . $roomId);
            
            jsonResponse(['success' => true, 'message' => 'Room created successfully', 'id' => $roomId]);
        } catch (Exception $e) {
            error_log('AdminController::createRoom() - Exception: ' . $e->getMessage());
            error_log('AdminController::createRoom() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::createRoom() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to create room'], 500);
        }
    }
    
    /**
     * Update hotel room
     */
    public function updateRoom() {
        error_log('AdminController::updateRoom() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::updateRoom() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::updateRoom() - JSON decoded successfully');
            
            // Validate required fields
            $requiredFields = ['id', 'room_type'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    error_log("AdminController::updateRoom() - Missing required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
                // For string fields, also check they're not empty after trimming
                if ($field === 'room_type' && trim($data[$field]) === '') {
                    error_log("AdminController::updateRoom() - Empty required field: $field");
                    jsonResponse(['success' => false, 'message' => "Missing required field: $field"], 400);
                }
            }
            
            $db = getDbConnection();
            
            $sql = "UPDATE hotel_rooms SET 
                    room_type = :room_type,
                    price = :price,
                    simple_price_type = :simple_price_type,
                    capacity = :capacity,
                    quantity_available = :quantity_available,
                    booking_open = :booking_open,
                    single_price_friday = :single_price_friday,
                    single_price_saturday = :single_price_saturday,
                    double_price_friday = :double_price_friday,
                    double_price_saturday = :double_price_saturday,
                    triple_price_friday = :triple_price_friday,
                    triple_price_saturday = :triple_price_saturday,
                    breakfast_included = :breakfast_included,
                    book_direct_with_hotel = :book_direct_with_hotel,
                    book_with_group = :book_with_group,
                    group_payment_due = :group_payment_due,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $params = [
                'id' => (int)$data['id'],
                'room_type' => $data['room_type'],
                'price' => (float)$data['price'],
                'simple_price_type' => in_array($data['simple_price_type'] ?? '', ['per_night', 'both_nights']) ? $data['simple_price_type'] : 'per_night',
                'capacity' => (int)($data['capacity'] ?? 2),
                'quantity_available' => (int)($data['quantity_available'] ?? 1),
                'booking_open' => isset($data['booking_open']) ? (int)$data['booking_open'] : 1,
                'single_price_friday' => (float)($data['single_price_friday'] ?? 0),
                'single_price_saturday' => (float)($data['single_price_saturday'] ?? 0),
                'double_price_friday' => (float)($data['double_price_friday'] ?? 0),
                'double_price_saturday' => (float)($data['double_price_saturday'] ?? 0),
                'triple_price_friday' => (float)($data['triple_price_friday'] ?? 0),
                'triple_price_saturday' => (float)($data['triple_price_saturday'] ?? 0),
                'breakfast_included' => isset($data['breakfast_included']) ? (int)$data['breakfast_included'] : 0,
                'book_direct_with_hotel' => isset($data['book_direct_with_hotel']) ? (int)$data['book_direct_with_hotel'] : 0,
                'book_with_group' => isset($data['book_with_group']) ? (int)$data['book_with_group'] : 0,
                'group_payment_due' => !empty($data['group_payment_due']) ? $data['group_payment_due'] : null
            ];
            error_log('AdminController::updateRoom() - SQL params: ' . json_encode($params));
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            error_log('AdminController::updateRoom() - Room updated with ID: ' . $data['id']);
            
            jsonResponse(['success' => true, 'message' => 'Room updated successfully']);
        } catch (Exception $e) {
            error_log('AdminController::updateRoom() - Exception: ' . $e->getMessage());
            error_log('AdminController::updateRoom() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::updateRoom() - Trace: ' . $e->getTraceAsString());
            jsonResponse(['success' => false, 'message' => 'Failed to update room'], 500);
        }
    }
    
    /**
     * Delete hotel room
     */
    public function deleteRoom() {
        error_log('AdminController::deleteRoom() - Method called');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }
        
        try {
            // Decode and validate JSON
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                error_log('AdminController::deleteRoom() - Failed to decode JSON');
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }
            error_log('AdminController::deleteRoom() - JSON decoded successfully');
            
            // Validate required fields
            if (!isset($data['id'])) {
                error_log('AdminController::deleteRoom() - Missing required field: id');
                jsonResponse(['success' => false, 'message' => 'Missing required field: id'], 400);
            }
            
            $db = getDbConnection();
            
            error_log('AdminController::deleteRoom() - Deleting room ID: ' . $data['id']);
            
            // Delete reservations first
            $stmt = $db->prepare("DELETE FROM room_reservations WHERE hotel_room_id = :hotel_room_id");
            $stmt->execute(['hotel_room_id' => $data['id']]);
            
            // Delete room
            $stmt = $db->prepare("DELETE FROM hotel_rooms WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            error_log('AdminController::deleteRoom() - Room deleted with ID: ' . $data['id']);
            
            jsonResponse(['success' => true, 'message' => 'Room deleted successfully']);
        } catch (Exception $e) {
            error_log('AdminController::deleteRoom() - Exception: ' . $e->getMessage());
            error_log('AdminController::deleteRoom() - File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('AdminController::deleteRoom() - Trace: ' . $e->getTraceAsString());
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
            $sql = "SELECT u.id,
                    u.discord_name,
                    u.name,
                    u.pin_hash,
                    u.is_admin,
                    u.approved,
                    u.approved_by,
                    u.approved_at,
                    u.created_at,
                    u.updated_at,
                    ea.id as attendance_id,
                    ea.days_attending,
                    ea.travel_method,
                    ea.status as attendance_status,
                    ea.created_at as registered_at
                    FROM users u
                    LEFT JOIN event_attendees ea ON u.id = ea.user_id AND ea.event_id = :event_id
                    ORDER BY u.created_at DESC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['event_id' => $event['id']]);
            $allUsers = $stmt->fetchAll();
            
            // Deduplicate users by ID, keeping first occurrence
            $users = [];
            $seenIds = [];
            foreach ($allUsers as $user) {
                if (!in_array($user['id'], $seenIds)) {
                    if ($user['days_attending']) {
                        $user['days_attending'] = explode(',', trim($user['days_attending']));
                    } else {
                        $user['days_attending'] = [];
                    }
                    if ($user['travel_method']) {
                        $user['travel_method'] = explode(',', trim($user['travel_method']));
                    } else {
                        $user['travel_method'] = [];
                    }
                    $users[] = $user;
                    $seenIds[] = $user['id'];
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
     * Show pending user approvals
     */
    public function showPendingApprovals() {
        try {
            $db = getDbConnection();
            $sql = "SELECT * FROM users WHERE approved = 0 ORDER BY created_at DESC";
            $stmt = $db->query($sql);
            $pendingUsers = $stmt->fetchAll();

            $pageTitle = 'Pending User Approvals';
            $currentPage = 'admin';
            include BASE_PATH . '/app/views/admin/approve-users.php';
        } catch (Exception $e) {
            error_log('Error loading pending users: ' . $e->getMessage());
            renderErrorPage('Error', 'Failed to load pending users');
        }
    }

    /**
     * Approve a user
     */
    public function approveUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $db = getDbConnection();
            $sql = "UPDATE users SET approved = 1, approved_by = :approved_by, approved_at = NOW() WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $data['id'], 'approved_by' => getCurrentUserId()]);

            jsonResponse(['success' => true, 'message' => 'User approved successfully']);
        } catch (Exception $e) {
            error_log('Error approving user: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to approve user'], 500);
        }
    }

    /**
     * Reject (delete) a pending user
     */
    public function rejectUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        try {
            $db = getDbConnection();
            $stmt = $db->prepare("DELETE FROM users WHERE id = :id AND approved = 0");
            $stmt->execute(['id' => $data['id']]);

            jsonResponse(['success' => true, 'message' => 'User rejected and deleted']);
        } catch (Exception $e) {
            error_log('Error rejecting user: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to reject user'], 500);
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
            $stmt = $db->prepare("DELETE FROM room_reservations WHERE user_id = :user_id");
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

    /**
     * Reset user PIN
     */
    public function resetUserPin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['new_pin'])) {
            jsonResponse(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        if (strlen($data['new_pin']) < 4) {
            jsonResponse(['success' => false, 'message' => 'PIN must be at least 4 digits'], 400);
        }

        try {
            $db = getDbConnection();
            $pinHash = password_hash($data['new_pin'], PASSWORD_DEFAULT);

            $stmt = $db->prepare("UPDATE users SET pin_hash = :pin_hash WHERE id = :id");
            $stmt->execute(['pin_hash' => $pinHash, 'id' => $data['id']]);

            jsonResponse(['success' => true, 'message' => 'PIN reset successfully']);
        } catch (Exception $e) {
            error_log('Error resetting PIN: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to reset PIN'], 500);
        }
    }

    // ========================================================================
    // PAYMENT MANAGEMENT
    // ========================================================================

    /**
     * Show payments admin page
     */
    public function showPaymentsManager() {
        $event = $this->getActiveEvent();
        $db = getDbConnection();

        // Get all users with activity, meal and hotel bookings, including payment info
        $sql = "SELECT u.id as user_id, u.discord_name, u.name,
                    ab.id as booking_id, 'activity' as booking_type,
                    a.title as item_title, a.day, a.price as item_price,
                    ab.payment_status, ab.amount_due, ab.amount_paid, ab.payment_notes
                FROM activity_bookings ab
                JOIN users u ON ab.user_id = u.id
                JOIN activities a ON ab.activity_id = a.id
                WHERE a.event_id = :event_id_1
                UNION ALL
                SELECT u.id as user_id, u.discord_name, u.name,
                    mb.id as booking_id, 'meal' as booking_type,
                    m.title as item_title, m.day, m.price as item_price,
                    mb.payment_status, mb.amount_due, mb.amount_paid, mb.payment_notes
                FROM meal_bookings mb
                JOIN users u ON mb.user_id = u.id
                JOIN meals m ON mb.meal_id = m.id
                WHERE m.event_id = :event_id_2
                UNION ALL
                SELECT u.id as user_id, u.discord_name, u.name,
                    rr.id as booking_id, 'hotel' as booking_type,
                    CONCAT(h.name, ' - ', hr.room_type) as item_title,
                    rr.check_in as day, rr.total_price as item_price,
                    rr.payment_status, NULL as amount_due,
                    COALESCE(rr.amount_paid, 0) as amount_paid, NULL as payment_notes
                FROM room_reservations rr
                JOIN hotel_rooms hr ON rr.hotel_room_id = hr.id
                JOIN hotels h ON hr.hotel_id = h.id
                JOIN users u ON rr.user_id = u.id
                WHERE h.event_id = :event_id_3 AND rr.payment_status != 'cancelled'
                ORDER BY user_id, day";

        $stmt = $db->prepare($sql);
        $stmt->execute(['event_id_1' => $event['id'], 'event_id_2' => $event['id'], 'event_id_3' => $event['id']]);
        $allBookings = $stmt->fetchAll();

        // Group by user
        $userPayments = [];
        foreach ($allBookings as $booking) {
            $uid = $booking['user_id'];
            if (!isset($userPayments[$uid])) {
                $userPayments[$uid] = [
                    'user_id' => $uid,
                    'discord_name' => $booking['discord_name'],
                    'name' => $booking['name'],
                    'bookings' => [],
                    'total_due' => 0,
                    'total_paid' => 0,
                ];
            }
            $userPayments[$uid]['bookings'][] = $booking;
            $userPayments[$uid]['total_due'] += (float)($booking['amount_due'] ?: $booking['item_price']);
            $userPayments[$uid]['total_paid'] += (float)($booking['amount_paid'] ?? 0);
        }

        $pageTitle = 'Payment Management';
        $currentPage = 'admin';
        include BASE_PATH . '/app/views/admin/payments.php';
    }

    /**
     * Update payment status for a booking
     */
    public function updatePayment() {
        error_log('AdminController::updatePayment() - Method called');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if ($data === null) {
                jsonResponse(['success' => false, 'message' => 'Invalid JSON data'], 400);
            }

            $bookingId = $data['booking_id'] ?? null;
            $bookingType = $data['booking_type'] ?? null;

            if (!$bookingId || !in_array($bookingType, ['activity', 'meal', 'hotel'])) {
                jsonResponse(['success' => false, 'message' => 'Invalid booking ID or type'], 400);
            }

            $db = getDbConnection();
            if ($bookingType === 'activity') {
                $table = 'activity_bookings';
            } elseif ($bookingType === 'meal') {
                $table = 'meal_bookings';
            } else {
                $table = 'room_reservations';
            }

            $fields = [];
            $params = ['id' => (int)$bookingId];

            if (isset($data['payment_status'])) {
                $fields[] = 'payment_status = :payment_status';
                $params['payment_status'] = $data['payment_status'];
            }
            // amount_due and payment_notes are not columns in room_reservations
            if (isset($data['amount_due']) && $bookingType !== 'hotel') {
                $fields[] = 'amount_due = :amount_due';
                $params['amount_due'] = (float)$data['amount_due'];
            }
            if (isset($data['amount_paid'])) {
                $fields[] = 'amount_paid = :amount_paid';
                $params['amount_paid'] = (float)$data['amount_paid'];
            }
            if (isset($data['payment_notes']) && $bookingType !== 'hotel') {
                $fields[] = 'payment_notes = :payment_notes';
                $params['payment_notes'] = $data['payment_notes'];
            }

            if (empty($fields)) {
                jsonResponse(['success' => false, 'message' => 'No fields to update'], 400);
            }

            $sql = "UPDATE $table SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

            jsonResponse(['success' => true, 'message' => 'Payment updated successfully']);
        } catch (Exception $e) {
            error_log('AdminController::updatePayment() - Exception: ' . $e->getMessage());
            jsonResponse(['success' => false, 'message' => 'Failed to update payment'], 500);
        }
    }
}
