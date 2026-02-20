<?php

class Activity {
    private $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function getAll($event_id) {
        $sql = "SELECT a.* FROM activities a 
                WHERE a.event_id = :event_id 
                GROUP BY a.id 
                ORDER BY FIELD(a.day, 'Friday', 'Saturday', 'Sunday'), a.start_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $event_id]);
        return $stmt->fetchAll();
    }

    public function isBooked($activityId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM activity_bookings WHERE activity_id = :activity_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['activity_id' => $activityId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
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
}