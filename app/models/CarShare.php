<?php
class CarShare {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function createOffer($userId, $eventId, $origin, $capacity) {
        $sql = "INSERT INTO carshare_offers (user_id, event_id, origin, passenger_capacity, available_spaces)
                VALUES (:user_id, :event_id, :origin, :capacity, :capacity)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'event_id' => $eventId,
            'origin' => $origin,
            'capacity' => $capacity
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function updateOffer($userId, $eventId, $origin, $capacity) {
        $existing = $this->getUserOffer($userId, $eventId);
        if (!$existing) {
            return $this->createOffer($userId, $eventId, $origin, $capacity);
        }
        $booked = $existing['passenger_capacity'] - $existing['available_spaces'];
        $newAvailable = max(0, $capacity - $booked);
        $sql = "UPDATE carshare_offers SET origin = :origin, passenger_capacity = :capacity, available_spaces = :available_spaces
                WHERE user_id = :user_id AND event_id = :event_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'origin' => $origin,
            'capacity' => $capacity,
            'available_spaces' => $newAvailable,
            'user_id' => $userId,
            'event_id' => $eventId
        ]);
        return $existing['id'];
    }
    
    public function getAll($eventId) {
        $sql = "SELECT cs.*, u.discord_name, u.name
                FROM carshare_offers cs
                JOIN users u ON cs.user_id = u.id
                WHERE cs.event_id = :event_id
                ORDER BY cs.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function getAvailable($eventId) {
        $sql = "SELECT cs.*, u.discord_name, u.name
                FROM carshare_offers cs
                JOIN users u ON cs.user_id = u.id
                WHERE cs.event_id = :event_id AND cs.available_spaces > 0
                ORDER BY cs.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM carshare_offers WHERE id = :id";
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
                throw new Exception('Cannot book your own carshare');
            }
            
            $sql = "INSERT INTO carshare_bookings (carshare_offer_id, user_id) 
                    VALUES (:offer_id, :user_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId]);
            
            $sql = "UPDATE carshare_offers SET available_spaces = available_spaces - 1 WHERE id = :id";
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
            $sql = "DELETE FROM carshare_bookings WHERE carshare_offer_id = :offer_id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId]);
            
            $sql = "UPDATE carshare_offers SET available_spaces = available_spaces + 1 WHERE id = :id";
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
        $sql = "SELECT cb.*, cs.origin, u.discord_name as driver_name
                FROM carshare_bookings cb
                JOIN carshare_offers cs ON cb.carshare_offer_id = cs.id
                JOIN users u ON cs.user_id = u.id
                WHERE cb.user_id = :user_id AND cs.event_id = :event_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        return $stmt->fetch();
    }
    
    public function getUserOffer($userId, $eventId) {
        $sql = "SELECT * FROM carshare_offers WHERE user_id = :user_id AND event_id = :event_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        return $stmt->fetch();
    }
    
    public function getOfferBookings($offerId) {
        $sql = "SELECT cb.*, u.discord_name, u.name
                FROM carshare_bookings cb
                JOIN users u ON cb.user_id = u.id
                WHERE cb.carshare_offer_id = :offer_id
                ORDER BY cb.created_at";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId]);
        return $stmt->fetchAll();
    }
    
    public function isBooked($offerId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM carshare_bookings WHERE carshare_offer_id = :offer_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
}
