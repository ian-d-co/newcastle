<?php
class Activity {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function create($data) {
        $sql = "INSERT INTO activities (event_id, title, description, day, start_time, end_time, max_capacity, requires_prepayment, price)
                VALUES (:event_id, :title, :description, :day, :start_time, :end_time, :max_capacity, :requires_prepayment, :price)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    public function getAll($eventId) {
        $sql = "SELECT DISTINCT a.* FROM activities a WHERE a.event_id = :event_id ORDER BY 
                FIELD(a.day, 'Friday', 'Saturday', 'Sunday'), a.start_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        $activities = $stmt->fetchAll();

        // Deduplicate by ID as fallback
        $seenIds = [];
        $uniqueActivities = [];
        foreach ($activities as $activity) {
            if (!isset($seenIds[$activity['id']])) {
                $uniqueActivities[] = $activity;
                $seenIds[$activity['id']] = true;
            }
        }
        return $uniqueActivities;
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM activities WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach (['title', 'description', 'day', 'start_time', 'end_time', 'max_capacity', 'requires_prepayment', 'price'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE activities SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM activities WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function book($activityId, $userId) {
        $this->db->beginTransaction();
        
        try {
            $activity = $this->getById($activityId);
            
            if ($activity['current_bookings'] >= $activity['max_capacity']) {
                throw new Exception('Activity is full');
            }
            
            $paymentStatus = $activity['requires_prepayment'] ? 'pending' : 'not_required';
            
            $sql = "INSERT INTO activity_bookings (activity_id, user_id, payment_status) 
                    VALUES (:activity_id, :user_id, :payment_status)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'activity_id' => $activityId,
                'user_id' => $userId,
                'payment_status' => $paymentStatus
            ]);
            
            $sql = "UPDATE activities SET current_bookings = current_bookings + 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $activityId]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function cancelBooking($activityId, $userId) {
        $this->db->beginTransaction();
        
        try {
            $sql = "DELETE FROM activity_bookings WHERE activity_id = :activity_id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['activity_id' => $activityId, 'user_id' => $userId]);
            
            $sql = "UPDATE activities SET current_bookings = current_bookings - 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $activityId]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getUserBookings($userId, $eventId) {
        $sql = "SELECT ab.*, a.title, a.day, a.start_time, a.end_time, a.price, a.requires_prepayment
                FROM activity_bookings ab
                JOIN activities a ON ab.activity_id = a.id
                WHERE ab.user_id = :user_id AND a.event_id = :event_id
                ORDER BY FIELD(a.day, 'Friday', 'Saturday', 'Sunday'), a.start_time";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function isBooked($activityId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM activity_bookings WHERE activity_id = :activity_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['activity_id' => $activityId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    public function updatePaymentStatus($bookingId, $status) {
        $sql = "UPDATE activity_bookings SET payment_status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $bookingId, 'status' => $status]);
    }
    
    public function getBookingsByActivity($activityId) {
        $sql = "SELECT ab.*, u.discord_name, u.name
                FROM activity_bookings ab
                JOIN users u ON ab.user_id = u.id
                WHERE ab.activity_id = :activity_id
                ORDER BY ab.created_at";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['activity_id' => $activityId]);
        return $stmt->fetchAll();
    }
}
