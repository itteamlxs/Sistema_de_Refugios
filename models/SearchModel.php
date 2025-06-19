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

    public function filterPeopleByPrefix($prefix) {
        // Filtro progresivo: solo nombres que empiezan con el prefijo
        $prefix = $prefix . '%';
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.status, p.entry_date, p.entry_time, r.refuge_name, r.location
            FROM people p
            JOIN requests r ON p.refuge_id = r.id
            WHERE r.status = 'approved' AND p.name LIKE ?
            ORDER BY p.name ASC
            LIMIT 100
        ");
        $stmt->execute([$prefix]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPeopleForPublic($limit = 50) {
        // Para mostrar inicialmente en el landing page
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.status, p.entry_date, p.entry_time, r.refuge_name, r.location
            FROM people p
            JOIN requests r ON p.refuge_id = r.id
            WHERE r.status = 'approved'
            ORDER BY p.entry_date DESC, p.name ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPeople($page = 1, $limit = 30) {
        $offset = ($page - 1) * $limit;
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.status, p.entry_date, p.entry_time, r.refuge_name, r.location
            FROM people p
            JOIN requests r ON p.refuge_id = r.id
            WHERE r.status = 'approved'
            ORDER BY p.entry_date DESC, p.entry_time DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalPeopleCount() {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total
            FROM people p
            JOIN requests r ON p.refuge_id = r.id
            WHERE r.status = 'approved'
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
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
        // Sugerencias también por prefijo para consistencia
        $name = $name . '%';
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.name
            FROM people p
            JOIN requests r ON p.refuge_id = r.id
            WHERE r.status = 'approved' AND p.name LIKE ?
            ORDER BY p.name ASC
            LIMIT 10
        ");
        $stmt->execute([$name]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'name');
    }

    public function getStatusStats() {
        $stmt = $this->db->prepare("
            SELECT 
                p.status,
                COUNT(*) as count
            FROM people p
            JOIN requests r ON p.refuge_id = r.id
            WHERE r.status = 'approved'
            GROUP BY p.status
            ORDER BY count DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}