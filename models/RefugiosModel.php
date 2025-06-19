<?php
require_once __DIR__ . '/../config/database.php';

class RefugiosModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllRefugios() {
        $stmt = $this->db->prepare("
            SELECT 
                r.id,
                r.refuge_name,
                r.location,
                r.created_at,
                COUNT(p.id) as total_albergados,
                COUNT(CASE WHEN p.status = 'Albergado' THEN 1 END) as activos,
                COUNT(CASE WHEN p.status = 'En tránsito' THEN 1 END) as en_transito,
                COUNT(CASE WHEN p.status = 'Pendiente' THEN 1 END) as pendientes,
                COUNT(CASE WHEN p.status = 'Dado de alta' THEN 1 END) as dados_alta
            FROM requests r
            LEFT JOIN people p ON r.id = p.refuge_id
            WHERE r.status = 'approved'
            GROUP BY r.id, r.refuge_name, r.location, r.created_at
            ORDER BY r.created_at DESC, r.refuge_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRefugioById($refugio_id) {
        $stmt = $this->db->prepare("
            SELECT 
                r.id,
                r.refuge_name,
                r.location,
                r.created_at,
                COUNT(p.id) as total_albergados
            FROM requests r
            LEFT JOIN people p ON r.id = p.refuge_id
            WHERE r.id = ? AND r.status = 'approved'
            GROUP BY r.id, r.refuge_name, r.location, r.created_at
        ");
        $stmt->execute([$refugio_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPeopleByRefugio($refugio_id) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.name, p.status, p.entry_date, p.entry_time, r.refuge_name, r.location
            FROM people p
            JOIN requests r ON p.refuge_id = r.id
            WHERE p.refuge_id = ? AND r.status = 'approved'
            ORDER BY p.entry_date DESC, p.name ASC
        ");
        $stmt->execute([$refugio_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRefugiosStats() {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT r.id) as total_refugios,
                COUNT(p.id) as total_personas,
                COUNT(CASE WHEN p.status = 'Albergado' THEN 1 END) as personas_activas,
                AVG(refugio_counts.personas_por_refugio) as promedio_por_refugio
            FROM requests r
            LEFT JOIN people p ON r.id = p.refuge_id
            LEFT JOIN (
                SELECT refuge_id, COUNT(*) as personas_por_refugio
                FROM people
                GROUP BY refuge_id
            ) refugio_counts ON r.id = refugio_counts.refuge_id
            WHERE r.status = 'approved'
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function searchRefugios($query) {
        $query = '%' . $query . '%';
        $stmt = $this->db->prepare("
            SELECT 
                r.id,
                r.refuge_name,
                r.location,
                r.created_at,
                COUNT(p.id) as total_albergados,
                COUNT(CASE WHEN p.status = 'Albergado' THEN 1 END) as activos,
                COUNT(CASE WHEN p.status = 'En tránsito' THEN 1 END) as en_transito,
                COUNT(CASE WHEN p.status = 'Pendiente' THEN 1 END) as pendientes,
                COUNT(CASE WHEN p.status = 'Dado de alta' THEN 1 END) as dados_alta
            FROM requests r
            LEFT JOIN people p ON r.id = p.refuge_id
            WHERE r.status = 'approved' AND (r.refuge_name LIKE ? OR r.location LIKE ?)
            GROUP BY r.id, r.refuge_name, r.location, r.created_at
            ORDER BY r.created_at DESC, r.refuge_name ASC
        ");
        $stmt->execute([$query, $query]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}