<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    public function create($discordName, $name, $pin) {
        $sql = "INSERT INTO users (discord_name, name, pin_hash, approved) VALUES (:discord_name, :name, :pin_hash, 0)";
        $stmt = $this->db->prepare($sql);
        $pinHash = password_hash($pin, PASSWORD_BCRYPT);
        
        $stmt->execute([
            'discord_name' => $discordName,
            'name' => $name,
            'pin_hash' => $pinHash
        ]);
        
        return $this->db->lastInsertId();
    }
    
    public function findByDiscordName($discordName) {
        $sql = "SELECT * FROM users WHERE discord_name = :discord_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['discord_name' => $discordName]);
        return $stmt->fetch();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    public function verifyPin($user, $pin) {
        return password_verify($pin, $user['pin_hash']);
    }
    
    public function getAll() {
        $sql = "SELECT id, discord_name, name, is_admin, created_at FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];
        
        if (isset($data['discord_name'])) {
            $fields[] = "discord_name = :discord_name";
            $params['discord_name'] = $data['discord_name'];
        }
        
        if (isset($data['name'])) {
            $fields[] = "name = :name";
            $params['name'] = $data['name'];
        }
        
        if (isset($data['is_admin'])) {
            $fields[] = "is_admin = :is_admin";
            $params['is_admin'] = $data['is_admin'];
        }
        
        if (isset($data['open_to_hotel_sharing'])) {
            $fields[] = "open_to_hotel_sharing = :open_to_hotel_sharing";
            $params['open_to_hotel_sharing'] = $data['open_to_hotel_sharing'] ? 1 : 0;
        }

        if (isset($data['pin'])) {
            $fields[] = "pin_hash = :pin_hash";
            $params['pin_hash'] = password_hash($data['pin'], PASSWORD_BCRYPT);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
