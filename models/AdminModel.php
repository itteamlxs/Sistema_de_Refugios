<?php
require_once __DIR__ . '/../config/database.php';

// Definir la raíz del proyecto
define('PROJECT_ROOT', realpath(__DIR__ . '/../'));

class AdminModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getPendingRequests() {
        $stmt = $this->db->prepare("
            SELECT r.id, r.refuge_name, r.location, r.ip, r.csv_path, r.created_at, u.email
            FROM requests r
            JOIN users u ON r.user_id = u.id
            WHERE r.status = 'pending'
            ORDER BY r.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function approveRequest($requestId, $userId, $csvPath) {
        // Iniciar transacción
        $this->db->beginTransaction();
        try {
            // Construir ruta absoluta del archivo
            $filePath = PROJECT_ROOT . '/' . $csvPath;
            if (!file_exists($filePath)) {
                throw new Exception('El archivo CSV no existe en la ruta: ' . $filePath);
            }

            // Parsear CSV y guardar datos
            $data = $this->parseCsv($filePath);
            if (!$data) {
                throw new Exception('Error al parsear el CSV o formato inválido.');
            }

            // Insertar datos en la tabla people
            $stmt = $this->db->prepare("
                INSERT INTO people (name, status, refuge_id, entry_date, entry_time)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach ($data as $row) {
                if (!$row['fecha'] || !$row['hora']) {
                    throw new Exception('Datos inválidos en el CSV: fecha o hora no válidos.');
                }
                $stmt->execute([$row['nombre'], $row['estatus'], $requestId, $row['fecha'], $row['hora']]);
            }

            // Actualizar estado de la solicitud
            $stmt = $this->db->prepare("UPDATE requests SET status = 'approved' WHERE id = ?");
            $stmt->execute([$requestId]);

            // Registrar acción en logs
            $stmt = $this->db->prepare("
                INSERT INTO logs (user_id, action, request_id)
                VALUES (?, 'approve', ?)
            ");
            $stmt->execute([$userId, $requestId]);

            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function rejectRequest($requestId, $userId) {
        $this->db->beginTransaction();
        try {
            // Actualizar estado de la solicitud
            $stmt = $this->db->prepare("UPDATE requests SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$requestId]);

            // Registrar acción en logs
            $stmt = $this->db->prepare("
                INSERT INTO logs (user_id, action, request_id)
                VALUES (?, 'reject', ?)
            ");
            $stmt->execute([$userId, $requestId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    private function parseCsv($filePath) {
        if (!file_exists($filePath)) {
            return false;
        }

        $data = [];
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return false;
        }

        $header = fgetcsv($handle);
        $expectedHeader = ['nombre', 'estatus', 'fecha', 'hora'];
        if ($header !== $expectedHeader) {
            fclose($handle);
            return false;
        }

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 4) {
                // Sanitizar datos
                $data[] = [
                    'nombre' => filter_var($row[0], FILTER_SANITIZE_STRING),
                    'estatus' => filter_var($row[1], FILTER_SANITIZE_STRING),
                    'fecha' => $this->validateDate($row[2]) ? $row[2] : null,
                    'hora' => $this->validateTime($row[3]) ? $row[3] : null
                ];
            }
        }
        fclose($handle);
        return $data;
    }

    private function validateDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function validateTime($time) {
        return preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $time);
    }
}
?>