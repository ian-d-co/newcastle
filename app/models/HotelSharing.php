<?php
class HotelSharing {
    private $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    /**
     * Get all users who are open to hotel sharing.
     */
    public function getOpenUsers() {
        $sql = "SELECT id, discord_name, name FROM users WHERE open_to_hotel_sharing = 1 AND approved = 1 ORDER BY discord_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Send a sharing request from requester to target.
     */
    public function createRequest($requesterId, $targetUserId, $message = '') {
        $sql = "INSERT INTO hotel_sharing_requests (requester_id, target_user_id, message)
                VALUES (:requester_id, :target_user_id, :message)
                ON DUPLICATE KEY UPDATE message = VALUES(message), status = 'pending', updated_at = NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'requester_id'   => $requesterId,
            'target_user_id' => $targetUserId,
            'message'        => $message,
        ]);
        return $this->db->lastInsertId() ?: true;
    }

    /**
     * Get a specific request by ID.
     */
    public function getRequestById($id) {
        $sql = "SELECT * FROM hotel_sharing_requests WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Get an existing request between two users.
     */
    public function getRequest($requesterId, $targetUserId) {
        $sql = "SELECT * FROM hotel_sharing_requests WHERE requester_id = :requester_id AND target_user_id = :target_user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['requester_id' => $requesterId, 'target_user_id' => $targetUserId]);
        return $stmt->fetch();
    }

    /**
     * Get incoming pending requests for a user (they are the target).
     */
    public function getPendingRequestsForUser($userId) {
        $sql = "SELECT hsr.*, u.discord_name as requester_discord_name
                FROM hotel_sharing_requests hsr
                JOIN users u ON hsr.requester_id = u.id
                WHERE hsr.target_user_id = :user_id AND hsr.status = 'pending'
                ORDER BY hsr.created_at ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all requests a user has sent.
     */
    public function getSentRequests($userId) {
        $sql = "SELECT hsr.*, u.discord_name as target_discord_name
                FROM hotel_sharing_requests hsr
                JOIN users u ON hsr.target_user_id = u.id
                WHERE hsr.requester_id = :user_id
                ORDER BY hsr.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Accept a request (called by the target user).
     */
    public function acceptRequest($requestId, $targetUserId) {
        $request = $this->getRequestById($requestId);

        if (!$request) {
            throw new Exception('Request not found');
        }
        if ($request['target_user_id'] != $targetUserId) {
            throw new Exception('Not authorised');
        }
        if ($request['status'] !== 'pending') {
            throw new Exception('Request is no longer pending');
        }

        $stmt = $this->db->prepare("UPDATE hotel_sharing_requests SET status = 'accepted', updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $requestId]);
        return true;
    }

    /**
     * Decline a request (called by the target user).
     */
    public function declineRequest($requestId, $targetUserId) {
        $request = $this->getRequestById($requestId);

        if (!$request) {
            throw new Exception('Request not found');
        }
        if ($request['target_user_id'] != $targetUserId) {
            throw new Exception('Not authorised');
        }

        $stmt = $this->db->prepare("UPDATE hotel_sharing_requests SET status = 'declined', updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $requestId]);
        return true;
    }

    /**
     * Cancel a request (called by the requester).
     */
    public function cancelRequest($requestId, $requesterId) {
        $request = $this->getRequestById($requestId);

        if (!$request) {
            throw new Exception('Request not found');
        }
        if ($request['requester_id'] != $requesterId) {
            throw new Exception('Not authorised');
        }

        $stmt = $this->db->prepare("UPDATE hotel_sharing_requests SET status = 'cancelled', updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $requestId]);
        return true;
    }

    /**
     * Cancel an accepted match (called by either partner).
     */
    public function cancelMatch($requestId, $userId) {
        $request = $this->getRequestById($requestId);

        if (!$request) {
            throw new Exception('Request not found');
        }
        if ($request['requester_id'] != $userId && $request['target_user_id'] != $userId) {
            throw new Exception('Not authorised');
        }
        if ($request['status'] !== 'accepted') {
            throw new Exception('Match is not in accepted state');
        }

        $stmt = $this->db->prepare("UPDATE hotel_sharing_requests SET status = 'cancelled', updated_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $requestId]);
        return true;
    }

    /**
     * Get accepted matches for a user (as requester or target).
     */
    public function getAcceptedMatches($userId) {
        $sql = "SELECT hsr.*,
                       ur.discord_name as requester_discord_name,
                       ut.discord_name as target_discord_name
                FROM hotel_sharing_requests hsr
                JOIN users ur ON hsr.requester_id = ur.id
                JOIN users ut ON hsr.target_user_id = ut.id
                WHERE (hsr.requester_id = :user_id OR hsr.target_user_id = :user_id2) AND hsr.status = 'accepted'
                ORDER BY hsr.updated_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'user_id2' => $userId]);
        return $stmt->fetchAll();
    }
}
