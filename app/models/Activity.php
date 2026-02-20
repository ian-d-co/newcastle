<?php

class Activity {
    private $db;

    private static function normalizeDay($day) {
        return ucfirst(strtolower(trim($day)));
    }

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function getAll($eventId) {
        $sql = "SELECT a.*, TRIM(a.day) AS day FROM activities a 
                WHERE a.event_id = :event_id 
                ORDER BY FIELD(TRIM(a.day), 'Friday', 'Saturday', 'Sunday'), a.start_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        $activities = $stmt->fetchAll();

        // Deduplicate by ID and normalize day value
        $seenIds = [];
        $uniqueActivities = [];
        foreach ($activities as $activity) {
            if (!isset($seenIds[$activity['id']])) {
                $activity['day'] = self::normalizeDay($activity['day']);
                $uniqueActivities[] = $activity;
                $seenIds[$activity['id']] = true;
            }
        }
        return $uniqueActivities;
    }

    public function isBooked($activityId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM activity_bookings WHERE activity_id = :activity_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['activity_id' => $activityId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function getUserBookings($userId, $eventId) {
        $sql = "SELECT ab.*, TRIM(a.day) AS day, a.title, a.start_time, a.end_time, a.price, a.requires_prepayment
                FROM activity_bookings ab
                JOIN activities a ON ab.activity_id = a.id
                WHERE ab.user_id = :user_id AND a.event_id = :event_id
                ORDER BY FIELD(TRIM(a.day), 'Friday', 'Saturday', 'Sunday'), a.start_time";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$row) {
            $row['day'] = self::normalizeDay($row['day']);
        }
        return $rows;
    }
}