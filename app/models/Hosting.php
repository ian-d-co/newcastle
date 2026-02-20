<?php
class Hosting {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function createOffer($userId, $eventId, $capacity, $notes = '') {
        $sql = "INSERT INTO hosting_offers (user_id, event_id, capacity, available_spaces, notes)
                VALUES (:user_id, :event_id, :capacity, :available_spaces, :notes)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'event_id' => $eventId,
            'capacity' => $capacity,
            'available_spaces' => $capacity,
            'notes' => $notes
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function updateOffer($userId, $eventId, $capacity, $notes = '') {
        $existing = $this->getUserOffer($userId, $eventId);
        if (!$existing) {
            return $this->createOffer($userId, $eventId, $capacity, $notes);
        }
        $booked = $existing['capacity'] - $existing['available_spaces'];
        $newAvailable = max(0, $capacity - $booked);
        $sql = "UPDATE hosting_offers SET capacity = :capacity, available_spaces = :available_spaces, notes = :notes
                WHERE user_id = :user_id AND event_id = :event_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'capacity' => $capacity,
            'available_spaces' => $newAvailable,
            'notes' => $notes,
            'user_id' => $userId,
            'event_id' => $eventId
        ]);
        return $existing['id'];
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
    
    public function removeOffer($userId, $eventId) {
        $offer = $this->getUserOffer($userId, $eventId);

        if (!$offer) return;

        $booked = $offer['capacity'] - $offer['available_spaces'];
        if ($booked > 0) {
            throw new Exception('Cannot remove hosting offer - ' . $booked . ' guest(s) have already booked');
        }

        $sql = "DELETE FROM hosting_offers WHERE user_id = :user_id AND event_id = :event_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
    }

    public function isBooked($offerId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM hosting_bookings WHERE hosting_offer_id = :offer_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    // ========================================================================
    // REQUEST / ACCEPT WORKFLOW
    // ========================================================================

    public function createRequest($offerId, $userId, $message = '') {
        $sql = "INSERT INTO hosting_requests (hosting_offer_id, user_id, message)
                VALUES (:offer_id, :user_id, :message)
                ON DUPLICATE KEY UPDATE message = VALUES(message), status = 'pending', updated_at = NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId, 'message' => $message]);
        return $this->db->lastInsertId() ?: true;
    }

    public function getRequest($offerId, $userId) {
        $sql = "SELECT * FROM hosting_requests WHERE hosting_offer_id = :offer_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId]);
        return $stmt->fetch();
    }

    public function getPendingRequestsForHost($hostUserId, $eventId) {
        $sql = "SELECT hr.*, u.discord_name, u.name as guest_name, ho.capacity, ho.available_spaces, ho.notes as offer_notes
                FROM hosting_requests hr
                JOIN hosting_offers ho ON hr.hosting_offer_id = ho.id
                JOIN users u ON hr.user_id = u.id
                WHERE ho.user_id = :host_user_id AND ho.event_id = :event_id AND hr.status = 'pending'
                ORDER BY hr.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['host_user_id' => $hostUserId, 'event_id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function getRequestsForGuest($userId, $eventId) {
        $sql = "SELECT hr.*, ho.notes as offer_notes, u.discord_name as host_discord_name
                FROM hosting_requests hr
                JOIN hosting_offers ho ON hr.hosting_offer_id = ho.id
                JOIN users u ON ho.user_id = u.id
                WHERE hr.user_id = :user_id AND ho.event_id = :event_id
                ORDER BY hr.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function acceptRequest($requestId, $hostUserId) {
        $this->db->beginTransaction();

        try {
            // Fetch request
            $stmt = $this->db->prepare("SELECT hr.*, ho.user_id as host_id, ho.available_spaces
                    FROM hosting_requests hr
                    JOIN hosting_offers ho ON hr.hosting_offer_id = ho.id
                    WHERE hr.id = :id");
            $stmt->execute(['id' => $requestId]);
            $request = $stmt->fetch();

            if (!$request) {
                throw new Exception('Request not found');
            }
            if ($request['host_id'] != $hostUserId) {
                throw new Exception('Not authorised');
            }
            if ($request['status'] !== 'pending') {
                throw new Exception('Request is no longer pending');
            }
            if ($request['available_spaces'] <= 0) {
                throw new Exception('No available spaces');
            }

            // Update request status
            $stmt = $this->db->prepare("UPDATE hosting_requests SET status = 'accepted', updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $requestId]);

            // Decrement available_spaces
            $stmt = $this->db->prepare("UPDATE hosting_offers SET available_spaces = available_spaces - 1 WHERE id = :id AND available_spaces > 0");
            $stmt->execute(['id' => $request['hosting_offer_id']]);

            // Create booking record
            $stmt = $this->db->prepare("INSERT IGNORE INTO hosting_bookings (hosting_offer_id, user_id) VALUES (:offer_id, :user_id)");
            $stmt->execute(['offer_id' => $request['hosting_offer_id'], 'user_id' => $request['user_id']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function declineRequest($requestId, $hostUserId) {
        $stmt = $this->db->prepare("SELECT hr.*, ho.user_id as host_id
                FROM hosting_requests hr
                JOIN hosting_offers ho ON hr.hosting_offer_id = ho.id
                WHERE hr.id = :id");
        $stmt->execute(['id' => $requestId]);
        $request = $stmt->fetch();

        if (!$request) {
            throw new Exception('Request not found');
        }
        if ($request['host_id'] != $hostUserId) {
            throw new Exception('Not authorised');
        }

        $stmt = $this->db->prepare("UPDATE hosting_requests SET status = 'declined', updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $requestId]);
        return true;
    }

    public function cancelRequest($requestId, $userId) {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("SELECT * FROM hosting_requests WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $requestId, 'user_id' => $userId]);
            $request = $stmt->fetch();

            if (!$request) {
                throw new Exception('Request not found');
            }

            // If it was accepted, restore the space and remove booking
            if ($request['status'] === 'accepted') {
                $stmt = $this->db->prepare("UPDATE hosting_offers SET available_spaces = available_spaces + 1 WHERE id = :id");
                $stmt->execute(['id' => $request['hosting_offer_id']]);

                $stmt = $this->db->prepare("DELETE FROM hosting_bookings WHERE hosting_offer_id = :offer_id AND user_id = :user_id");
                $stmt->execute(['offer_id' => $request['hosting_offer_id'], 'user_id' => $userId]);
            }

            $stmt = $this->db->prepare("UPDATE hosting_requests SET status = 'cancelled', updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $requestId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
