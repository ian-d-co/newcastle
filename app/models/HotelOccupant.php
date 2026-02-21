<?php
class HotelOccupant {
    private $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    /**
     * Invite a user to share a room (occupant 2 or 3).
     */
    public function invite($reservationId, $invitedUserId, $occupantNumber, $invitedBy, $message = '') {
        $sql = "INSERT INTO hotel_room_occupants (reservation_id, user_id, occupant_number, invited_by, message)
                VALUES (:reservation_id, :user_id, :occupant_number, :invited_by, :message)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'reservation_id'  => $reservationId,
            'user_id'         => $invitedUserId,
            'occupant_number' => $occupantNumber,
            'invited_by'      => $invitedBy,
            'message'         => $message,
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Get occupant record by ID.
     */
    public function getById($id) {
        $sql = "SELECT * FROM hotel_room_occupants WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get all occupants for a reservation.
     */
    public function getByReservation($reservationId) {
        $sql = "SELECT hro.*, u.discord_name, ub.discord_name as invited_by_name
                FROM hotel_room_occupants hro
                JOIN users u ON hro.user_id = u.id
                JOIN users ub ON hro.invited_by = ub.id
                WHERE hro.reservation_id = :reservation_id
                ORDER BY hro.occupant_number";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['reservation_id' => $reservationId]);
        return $stmt->fetchAll();
    }

    /**
     * Get pending invitations for a user (they are the invitee).
     */
    public function getPendingInvitesForUser($userId) {
        $sql = "SELECT hro.*, rr.hotel_room_id, hr.room_type, h.name as hotel_name, u.discord_name as invited_by_name
                FROM hotel_room_occupants hro
                JOIN room_reservations rr ON hro.reservation_id = rr.id
                JOIN hotel_rooms hr ON rr.hotel_room_id = hr.id
                JOIN hotels h ON hr.hotel_id = h.id
                JOIN users u ON hro.invited_by = u.id
                WHERE hro.user_id = :user_id AND hro.status = 'pending'
                ORDER BY hro.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Accept an invitation (called by the invited user).
     */
    public function accept($id, $userId) {
        $occupant = $this->getById($id);
        if (!$occupant) throw new Exception('Invitation not found');
        if ($occupant['user_id'] != $userId) throw new Exception('Not authorised');
        if ($occupant['status'] !== 'pending') throw new Exception('Invitation is no longer pending');

        $stmt = $this->db->prepare("UPDATE hotel_room_occupants SET status = 'accepted', updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return true;
    }

    /**
     * Decline an invitation (called by the invited user).
     */
    public function decline($id, $userId) {
        $occupant = $this->getById($id);
        if (!$occupant) throw new Exception('Invitation not found');
        if ($occupant['user_id'] != $userId) throw new Exception('Not authorised');

        $stmt = $this->db->prepare("UPDATE hotel_room_occupants SET status = 'declined', updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return true;
    }

    /**
     * Cancel an invitation (called by the inviter/reservation owner).
     */
    public function cancel($id, $invitedBy) {
        $occupant = $this->getById($id);
        if (!$occupant) throw new Exception('Invitation not found');
        if ($occupant['invited_by'] != $invitedBy) throw new Exception('Not authorised');

        $stmt = $this->db->prepare("UPDATE hotel_room_occupants SET status = 'cancelled', updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return true;
    }
}
