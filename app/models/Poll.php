<?php
class Poll {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function create($eventId, $question, $options, $createdBy, $isAnonymous = 0, $isMultipleChoice = 0, $expiresAt = null) {
        $this->db->beginTransaction();
        
        try {
            $sql = "INSERT INTO polls (event_id, question, is_anonymous, is_multiple_choice, expires_at, created_by)
                    VALUES (:event_id, :question, :is_anonymous, :is_multiple_choice, :expires_at, :created_by)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'event_id' => $eventId,
                'question' => $question,
                'is_anonymous' => $isAnonymous,
                'is_multiple_choice' => $isMultipleChoice,
                'expires_at' => $expiresAt,
                'created_by' => $createdBy
            ]);
            
            $pollId = $this->db->lastInsertId();
            
            foreach ($options as $option) {
                $sql = "INSERT INTO poll_options (poll_id, option_text) VALUES (:poll_id, :option_text)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['poll_id' => $pollId, 'option_text' => $option]);
            }
            
            $this->db->commit();
            return $pollId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function getAll($eventId) {
        $sql = "SELECT * FROM polls WHERE event_id = :event_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function getAllWithCategories($eventId) {
        $sql = "SELECT p.*,
                       pc.name as category_name,
                       pc.display_order as category_display_order
                FROM polls p
                LEFT JOIN poll_categories pc ON p.category_id = pc.id
                WHERE p.event_id = :event_id
                ORDER BY
                    COALESCE(pc.display_order, 999),
                    pc.name ASC,
                    CASE WHEN p.expires_at IS NULL THEN 1 ELSE 0 END,
                    p.expires_at ASC,
                    p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function getActive($eventId) {
        $sql = "SELECT p.*, 
                       pc.name as category_name,
                       pc.display_order as category_display_order
                FROM polls p
                LEFT JOIN poll_categories pc ON p.category_id = pc.id
                WHERE p.event_id = :event_id 
                AND p.is_active = 1 
                AND (p.expires_at IS NULL OR p.expires_at > NOW())
                ORDER BY 
                    COALESCE(pc.display_order, 999),
                    pc.name ASC,
                    CASE WHEN p.expires_at IS NULL THEN 1 ELSE 0 END,
                    p.expires_at ASC,
                    p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM polls WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function getOptions($pollId) {
        $sql = "SELECT * FROM poll_options WHERE poll_id = :poll_id ORDER BY id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['poll_id' => $pollId]);
        return $stmt->fetchAll();
    }
    
    public function vote($pollId, $optionIds, $userId) {
        $poll = $this->getById($pollId);
        
        if (!$poll['is_active'] || ($poll['expires_at'] && strtotime($poll['expires_at']) < time())) {
            throw new Exception('Poll is not active');
        }
        
        if (!$poll['is_multiple_choice'] && count($optionIds) > 1) {
            throw new Exception('Only one option allowed');
        }
        
        $this->db->beginTransaction();
        
        try {
            // Remove existing votes
            $sql = "DELETE FROM poll_votes WHERE poll_id = :poll_id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['poll_id' => $pollId, 'user_id' => $userId]);
            
            // Add new votes
            foreach ($optionIds as $optionId) {
                $sql = "INSERT INTO poll_votes (poll_id, poll_option_id, user_id) 
                        VALUES (:poll_id, :poll_option_id, :user_id)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'poll_id' => $pollId,
                    'poll_option_id' => $optionId,
                    'user_id' => $userId
                ]);
                
                // Update vote count
                $sql = "UPDATE poll_options SET vote_count = vote_count + 1 WHERE id = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->execute(['id' => $optionId]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function hasVoted($pollId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM poll_votes WHERE poll_id = :poll_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['poll_id' => $pollId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    public function getUserVotes($pollId, $userId) {
        $sql = "SELECT poll_option_id FROM poll_votes WHERE poll_id = :poll_id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['poll_id' => $pollId, 'user_id' => $userId]);
        return array_column($stmt->fetchAll(), 'poll_option_id');
    }
    
    public function getResults($pollId) {
        $options = $this->getOptions($pollId);
        $totalVotes = array_sum(array_column($options, 'vote_count'));
        
        foreach ($options as &$option) {
            $option['percentage'] = $totalVotes > 0 ? ($option['vote_count'] / $totalVotes) * 100 : 0;
        }
        
        return $options;
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        foreach (['question', 'is_anonymous', 'is_multiple_choice', 'expires_at', 'is_active'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE polls SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM polls WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    public function closeExpired() {
        $sql = "UPDATE polls SET is_active = 0 WHERE expires_at IS NOT NULL AND expires_at <= NOW() AND is_active = 1";
        $stmt = $this->db->query($sql);
        return $stmt->rowCount();
    }
}
