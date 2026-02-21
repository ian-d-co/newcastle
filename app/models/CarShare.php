<?php
class CarShare {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function createOffer($userId, $eventId, $origin, $capacity) {
        $sql = "INSERT INTO carshare_offers (user_id, event_id, origin, passenger_capacity, available_spaces)
                VALUES (:user_id, :event_id, :origin, :capacity, :available_spaces)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id'          => $userId,
            'event_id'         => $eventId,
            'origin'           => $origin,
            'capacity'         => $capacity,
            'available_spaces' => $capacity,
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

            if ($stmt->rowCount() === 0) {
                throw new Exception('Booking not found or already cancelled');
            }

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

    public function createRequest($offerId, $userId, $message = '') {
        $sql = "INSERT INTO carshare_requests (carshare_offer_id, user_id, message)
                VALUES (:offer_id, :user_id, :message) AS new_vals
                ON DUPLICATE KEY UPDATE message = new_vals.message, status = 'pending', updated_at = NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId, 'message' => $message]);
        return $this->db->lastInsertId() ?: true;
    }

    public function getRequest($offerId, $userId) {
        $sql = "SELECT * FROM carshare_requests WHERE carshare_offer_id = :offer_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId, 'user_id' => $userId]);
        return $stmt->fetch();
    }

    public function getPendingRequestsForDriver($driverUserId, $eventId) {
        $sql = "SELECT cr.*, u.discord_name, u.name as passenger_name, co.passenger_capacity, co.available_spaces, co.origin
                FROM carshare_requests cr
                JOIN carshare_offers co ON cr.carshare_offer_id = co.id
                JOIN users u ON cr.user_id = u.id
                WHERE co.user_id = :driver_user_id AND co.event_id = :event_id AND cr.status = 'pending'
                ORDER BY cr.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['driver_user_id' => $driverUserId, 'event_id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function getRequestsForPassenger($userId, $eventId) {
        $sql = "SELECT cr.*, co.origin, u.discord_name as driver_discord_name
                FROM carshare_requests cr
                JOIN carshare_offers co ON cr.carshare_offer_id = co.id
                JOIN users u ON co.user_id = u.id
                WHERE cr.user_id = :user_id AND co.event_id = :event_id
                ORDER BY cr.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function acceptRequest($requestId, $driverUserId) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT cr.*, co.user_id as driver_id, co.available_spaces
                    FROM carshare_requests cr
                    JOIN carshare_offers co ON cr.carshare_offer_id = co.id
                    WHERE cr.id = :id");
            $stmt->execute(['id' => $requestId]);
            $request = $stmt->fetch();

            if (!$request) throw new Exception('Request not found');
            if ($request['driver_id'] != $driverUserId) throw new Exception('Not authorised');
            if ($request['status'] !== 'pending') throw new Exception('Request is no longer pending');
            if ($request['available_spaces'] <= 0) throw new Exception('No available spaces');

            $stmt = $this->db->prepare("UPDATE carshare_requests SET status = 'accepted', updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $requestId]);

            $stmt = $this->db->prepare("UPDATE carshare_offers SET available_spaces = available_spaces - 1 WHERE id = :id AND available_spaces > 0");
            $stmt->execute(['id' => $request['carshare_offer_id']]);

            $stmt = $this->db->prepare("INSERT IGNORE INTO carshare_bookings (carshare_offer_id, user_id) VALUES (:offer_id, :user_id)");
            $stmt->execute(['offer_id' => $request['carshare_offer_id'], 'user_id' => $request['user_id']]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function declineRequest($requestId, $driverUserId) {
        $stmt = $this->db->prepare("SELECT cr.*, co.user_id as driver_id
                FROM carshare_requests cr
                JOIN carshare_offers co ON cr.carshare_offer_id = co.id
                WHERE cr.id = :id");
        $stmt->execute(['id' => $requestId]);
        $request = $stmt->fetch();

        if (!$request) throw new Exception('Request not found');
        if ($request['driver_id'] != $driverUserId) throw new Exception('Not authorised');

        $stmt = $this->db->prepare("UPDATE carshare_requests SET status = 'declined', updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $requestId]);
        return true;
    }

    public function cancelRequest($requestId, $userId) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("SELECT * FROM carshare_requests WHERE id = :id AND user_id = :user_id");
            $stmt->execute(['id' => $requestId, 'user_id' => $userId]);
            $request = $stmt->fetch();

            if (!$request) throw new Exception('Request not found');

            if ($request['status'] === 'accepted') {
                $stmt = $this->db->prepare("DELETE FROM carshare_bookings WHERE carshare_offer_id = :offer_id AND user_id = :user_id");
                $stmt->execute(['offer_id' => $request['carshare_offer_id'], 'user_id' => $userId]);
                if ($stmt->rowCount() > 0) {
                    $stmt = $this->db->prepare("UPDATE carshare_offers SET available_spaces = available_spaces + 1 WHERE id = :id");
                    $stmt->execute(['id' => $request['carshare_offer_id']]);
                }
            }

            $stmt = $this->db->prepare("UPDATE carshare_requests SET status = 'cancelled', updated_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $requestId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function cancelBookingAsDriver($offerId, $driverUserId, $passengerUserId) {
        $this->db->beginTransaction();
        try {
            $offer = $this->getById($offerId);
            if (!$offer) throw new Exception('Car share offer not found');
            if ($offer['user_id'] !== (int)$driverUserId) throw new Exception('Only the driver can cancel passenger bookings');

            $stmt = $this->db->prepare("DELETE FROM carshare_bookings WHERE carshare_offer_id = :offer_id AND user_id = :passenger_id");
            $stmt->execute(['offer_id' => $offerId, 'passenger_id' => $passengerUserId]);

            if ($stmt->rowCount() === 0) throw new Exception('Booking not found');

            $stmt = $this->db->prepare("UPDATE carshare_offers SET available_spaces = available_spaces + 1 WHERE id = :id");
            $stmt->execute(['id' => $offerId]);

            $stmt = $this->db->prepare("UPDATE carshare_requests SET status = 'cancelled', updated_at = NOW()
                    WHERE carshare_offer_id = :offer_id AND user_id = :passenger_id AND status = 'accepted'");
            $stmt->execute(['offer_id' => $offerId, 'passenger_id' => $passengerUserId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
