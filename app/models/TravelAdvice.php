<?php
class TravelAdvice {
    private $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function getAll($eventId) {
        $sql = "SELECT ta.*, u.discord_name as created_by_name
                FROM travel_advice ta
                LEFT JOIN users u ON ta.created_by = u.id
                WHERE ta.event_id = :event_id
                ORDER BY ta.travel_type, ta.from_location";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['event_id' => $eventId]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $sql = "SELECT * FROM travel_advice WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($eventId, $data, $createdBy) {
        $sql = "INSERT INTO travel_advice (event_id, travel_type, from_location, to_location, supplier, date_researched, notes, created_by)
                VALUES (:event_id, :travel_type, :from_location, :to_location, :supplier, :date_researched, :notes, :created_by)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'event_id'        => $eventId,
            'travel_type'     => $data['travel_type'],
            'from_location'   => $data['from_location'],
            'to_location'     => $data['to_location'],
            'supplier'        => $data['supplier'] ?? '',
            'date_researched' => $data['date_researched'] ?: null,
            'notes'           => $data['notes'] ?? null,
            'created_by'      => $createdBy,
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];

        foreach (['travel_type', 'from_location', 'to_location', 'supplier', 'date_researched', 'notes'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field] !== '' ? $data[$field] : null;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE travel_advice SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $sql = "DELETE FROM travel_advice WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
}
