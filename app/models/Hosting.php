<?php
class Hosting {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function createOffer($userId, $eventId, $capacity, $notes = '') {
        $sql = "INSERT INTO hosting_offers (user_id, event_id, capacity, available_spaces, notes)
                VALUES (:user_id, :event_id, :capacity, :capacity, :notes)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'event_id' => $eventId,
            'capacity' => $capacity,
            'notes' => $notes
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function getAll($eventId) {
        $sql = "SELECT h.*, u.discord_name, u.name
                FROM hosting_offers h
                JOIN users u ON h.user_id = u.id
                WHERE h.event_id = :event_id
                ORDER BY h.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function getAvailable($eventId) {
        $sql = "SELECT h.*, u.discord_name, u.name
                FROM hosting_offers h
                JOIN users u ON h.user_id = u.id
                WHERE h.event_id = :event_id AND h.available_spaces > 0
                ORDER BY h.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM hosting_offers WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function book($offerId, $userId) {
        $this->db->beginTransaction();
        
        try {
            $offer = $this->getById($offerId);
            
            if ($offer['available_spaces'] <= 0) {
                throw new Exception('No available spaces');
            }
            
            if ($offer['user_id'] == $userId) {
                throw new Exception('Cannot book your own hosting');
            }
            
            $sql = "INSERT INTO hosting_bookings (hosting_offer_id, user_id) 
                    VALUES (:offer_id, :user_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId]);
            
            $sql = "UPDATE hosting_offers SET available_spaces = available_spaces - 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $offerId]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function cancelBooking($offerId, $userId) {
        $this->db->beginTransaction();
        
        try {
            $sql = "DELETE FROM hosting_bookings WHERE hosting_offer_id = :offer_id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId]);
            
            $sql = "UPDATE hosting_offers SET available_spaces = available_spaces + 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $offerId]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getUserBooking($userId, $eventId) {
        $sql = "SELECT hb.*, h.notes, u.discord_name as host_name
                FROM hosting_bookings hb
                JOIN hosting_offers h ON hb.hosting_offer_id = h.id
                JOIN users u ON h.user_id = u.id
                WHERE hb.user_id = :user_id AND h.event_id = :event_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        return $stmt->fetch();
    }
    
    public function getUserOffer($userId, $eventId) {
        $sql = "SELECT * FROM hosting_offers WHERE user_id = :user_id AND event_id = :event_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        return $stmt->fetch();
    }
    
    public function getOfferBookings($offerId) {
        $sql = "SELECT hb.*, u.discord_name, u.name
                FROM hosting_bookings hb
                JOIN users u ON hb.user_id = u.id
                WHERE hb.hosting_offer_id = :offer_id
                ORDER BY hb.created_at";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId]);
        return $stmt->fetchAll();
    }
    
    public function isBooked($offerId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM hosting_bookings WHERE hosting_offer_id = :offer_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
