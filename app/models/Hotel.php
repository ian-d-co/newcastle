<?php
class Hotel {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function createHotel($eventId, $name, $address, $description, $website) {
        $sql = "INSERT INTO hotels (event_id, name, address, description, website)
                VALUES (:event_id, :name, :address, :description, :website)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'event_id' => $eventId,
            'name' => $name,
            'address' => $address,
            'description' => $description,
            'website' => $website
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function getAll($eventId) {
        $sql = "SELECT * FROM hotels WHERE event_id = :event_id ORDER BY name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM hotels WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function updateHotel($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach (['name', 'address', 'description', 'website'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE hotels SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteHotel($id) {
        $sql = "DELETE FROM hotels WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function createRoom($hotelId, $roomType, $capacity, $pricePerNight, $totalRooms, $description) {
        $sql = "INSERT INTO hotel_rooms (hotel_id, room_type, capacity, price_per_night, total_rooms, available_rooms, description)
                VALUES (:hotel_id, :room_type, :capacity, :price_per_night, :total_rooms, :total_rooms, :description)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'hotel_id' => $hotelId,
            'room_type' => $roomType,
            'capacity' => $capacity,
            'price_per_night' => $pricePerNight,
            'total_rooms' => $totalRooms,
            'description' => $description
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function getRoomsByHotel($hotelId) {
        $sql = "SELECT * FROM hotel_rooms WHERE hotel_id = :hotel_id ORDER BY price_per_night";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['hotel_id' => $hotelId]);
        return $stmt->fetchAll();
    }
    
    public function getRoomById($id) {
        $sql = "SELECT * FROM hotel_rooms WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function updateRoom($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach (['room_type', 'capacity', 'price_per_night', 'total_rooms', 'available_rooms', 'description'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE hotel_rooms SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function deleteRoom($id) {
        $sql = "DELETE FROM hotel_rooms WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function reserveRoom($roomId, $userId, $checkIn, $checkOut) {
        $this->db->beginTransaction();
        
        try {
            $room = $this->getRoomById($roomId);
            
            if ($room['available_rooms'] <= 0) {
                throw new Exception('No available rooms');
            }
            
            $checkInDate = new DateTime($checkIn);
            $checkOutDate = new DateTime($checkOut);
            $nights = $checkInDate->diff($checkOutDate)->days;
            $totalPrice = $nights * $room['price_per_night'];
            
            $sql = "INSERT INTO room_reservations (hotel_room_id, user_id, check_in, check_out, total_nights, total_price)
                    VALUES (:room_id, :user_id, :check_in, :check_out, :total_nights, :total_price)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'room_id' => $roomId,
                'user_id' => $userId,
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'total_nights' => $nights,
                'total_price' => $totalPrice
            ]);
            
            $sql = "UPDATE hotel_rooms SET available_rooms = available_rooms - 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $roomId]);
            
            $this->db->commit();
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function cancelReservation($reservationId) {
        $this->db->beginTransaction();
        
        try {
            $sql = "SELECT * FROM room_reservations WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $reservationId]);
            $reservation = $stmt->fetch();
            
            if (!$reservation) {
                throw new Exception('Reservation not found');
            }
            
            $sql = "UPDATE room_reservations SET payment_status = 'cancelled' WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $reservationId]);
            
            $sql = "UPDATE hotel_rooms SET available_rooms = available_rooms + 1 WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['id' => $reservation['hotel_room_id']]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getUserReservations($userId, $eventId) {
        $sql = "SELECT rr.*, hr.room_type, hr.capacity, h.name as hotel_name
                FROM room_reservations rr
                JOIN hotel_rooms hr ON rr.hotel_room_id = hr.id
                JOIN hotels h ON hr.hotel_id = h.id
                WHERE rr.user_id = :user_id AND h.event_id = :event_id AND rr.payment_status != 'cancelled'
                ORDER BY rr.check_in";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function updatePaymentStatus($reservationId, $status) {
        $sql = "UPDATE room_reservations SET payment_status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $reservationId, 'status' => $status]);
    }
    
    public function getAllReservations($eventId) {
        $sql = "SELECT rr.*, hr.room_type, h.name as hotel_name, u.discord_name, u.name as user_name
                FROM room_reservations rr
                JOIN hotel_rooms hr ON rr.hotel_room_id = hr.id
                JOIN hotels h ON hr.hotel_id = h.id
                JOIN users u ON rr.user_id = u.id
                WHERE h.event_id = :event_id
                ORDER BY rr.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }
}
