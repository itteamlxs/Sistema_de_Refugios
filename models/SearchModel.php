<?php
require_once __DIR__ . '/../config/database.php';

class SearchModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function searchPeople($name) {
        $name = '%' . $name . '%';
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.status, p.entry_date, p.entry_time, r.refuge_name, r.location
            FROM people p
            JOIN requests r ON p.refuge_id = r.id
            WHERE p.name LIKE ?
            LIMIT 50
        ");
        $stmt->execute([$name]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchPeopleByRefuge($refuge_id) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.status, p.entry_date, p.entry_time, r.refuge_name, r.location
            FROM people p
            JOIN requests r ON p.refuge_id = r.id
            WHERE p.refuge_id = ? AND r.status = 'approved'
            LIMIT 1000
        ");
        $stmt->execute([$refuge_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRefugeInfo($refuge_id) {
        $stmt = $this->db->prepare("SELECT refuge_name FROM requests WHERE id = ? AND status = 'approved'");
        $stmt->execute([$refuge_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function suggestNames($name) {
        $name = '%' . $name . '%';
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.name
            FROM people p
            JOIN requests r ON p.refuge_id = r.id
            WHERE p.name LIKE ?
            LIMIT 10
        ");
        $stmt->execute([$name]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
    }
}
?>