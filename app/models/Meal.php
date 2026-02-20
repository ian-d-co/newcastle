<?php
class Meal {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO meals (event_id, title, description, day, start_time, end_time, max_capacity, requires_prepayment, price, total_price, deposit_amount, prepayment_required, pay_on_arrival)
                VALUES (:event_id, :title, :description, :day, :start_time, :end_time, :max_capacity, :requires_prepayment, :price, :total_price, :deposit_amount, :prepayment_required, :pay_on_arrival)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    public function getAll($eventId) {
        $sql = "SELECT * FROM meals WHERE event_id = :event_id ORDER BY 
                FIELD(day, 'Friday', 'Saturday', 'Sunday'), start_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM meals WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach (['title', 'description', 'day', 'start_time', 'end_time', 'max_capacity', 'requires_prepayment', 'price', 'total_price', 'deposit_amount', 'prepayment_required', 'pay_on_arrival'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE meals SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM meals WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function book($mealId, $userId) {
        $this->db->beginTransaction();
        
        try {
            $meal = $this->getById($mealId);
            
            if ($meal['current_bookings'] >= $meal['max_capacity']) {
                throw new Exception('Meal is full');
            }
            
            $paymentStatus = $meal['requires_prepayment'] ? 'pending' : 'not_required';
            
            $sql = "INSERT INTO meal_bookings (meal_id, user_id, payment_status) 
                    VALUES (:meal_id, :user_id, :payment_status)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'meal_id' => $mealId,
                'user_id' => $userId,
                'payment_status' => $paymentStatus
            ]);
            
            $sql = "UPDATE meals SET current_bookings = current_bookings + 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $mealId]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function cancelBooking($mealId, $userId) {
        $this->db->beginTransaction();
        
        try {
            $sql = "DELETE FROM meal_bookings WHERE meal_id = :meal_id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['meal_id' => $mealId, 'user_id' => $userId]);
            
            $sql = "UPDATE meals SET current_bookings = current_bookings - 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $mealId]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getUserBookings($userId, $eventId) {
        $sql = "SELECT mb.*, m.title, m.day, m.start_time, m.end_time, m.price, m.requires_prepayment
                FROM meal_bookings mb
                JOIN meals m ON mb.meal_id = m.id
                WHERE mb.user_id = :user_id AND m.event_id = :event_id
                ORDER BY FIELD(m.day, 'Friday', 'Saturday', 'Sunday'), m.start_time";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function isBooked($mealId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM meal_bookings WHERE meal_id = :meal_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['meal_id' => $mealId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    public function updatePaymentStatus($bookingId, $status) {
        $sql = "UPDATE meal_bookings SET payment_status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $bookingId, 'status' => $status]);
    }
    
    public function getBookingsByMeal($mealId) {
        $sql = "SELECT mb.*, u.discord_name, u.name
                FROM meal_bookings mb
                JOIN users u ON mb.user_id = u.id
                WHERE mb.meal_id = :meal_id
                ORDER BY mb.created_at";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['meal_id' => $mealId]);
        return $stmt->fetchAll();
    }
}
