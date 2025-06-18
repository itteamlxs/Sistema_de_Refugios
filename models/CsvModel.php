<?php
require_once __DIR__ . '/../config/database.php';

class CsvModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function saveRequest($userId, $refugeName, $location, $ip, $csvPath) {
        $stmt = $this->db->prepare("
            INSERT INTO requests (user_id, refuge_name, location, ip, csv_path, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        return $stmt->execute([$userId, $refugeName, $location, $ip, $csvPath]);
    }
}
?>