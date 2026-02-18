<?php
class Event {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function getActive() {
        $sql = "SELECT * FROM events WHERE is_active = 1 ORDER BY start_date DESC LIMIT 1";
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM events WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach (['title', 'description', 'start_date', 'end_date', 'location', 'content'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE events SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function registerAttendance($userId, $eventId, $daysAttending, $travelMethod) {
        $sql = "INSERT INTO event_attendees (user_id, event_id, days_attending, travel_method) 
                VALUES (:user_id, :event_id, :days_attending, :travel_method)
                ON DUPLICATE KEY UPDATE 
                days_attending = :days_attending, 
                travel_method = :travel_method";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'event_id' => $eventId,
            'days_attending' => json_encode($daysAttending),
            'travel_method' => json_encode($travelMethod)
        ]);
    }
    
    public function getAttendance($userId, $eventId) {
        $sql = "SELECT * FROM event_attendees WHERE user_id = :user_id AND event_id = :event_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        $result = $stmt->fetch();
        
        if ($result) {
            $result['days_attending'] = json_decode($result['days_attending'], true);
            $result['travel_method'] = json_decode($result['travel_method'], true);
        }
        
        return $result;
    }
    
    public function getAllAttendees($eventId) {
        $sql = "SELECT ea.*, u.discord_name, u.name 
                FROM event_attendees ea
                JOIN users u ON ea.user_id = u.id
                WHERE ea.event_id = :event_id
                ORDER BY ea.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        $results = $stmt->fetchAll();
        
        foreach ($results as &$result) {
            $result['days_attending'] = json_decode($result['days_attending'], true);
            $result['travel_method'] = json_decode($result['travel_method'], true);
        }
        
        return $results;
    }
}
