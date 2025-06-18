<?php
require_once __DIR__ . '/../models/AdminModel.php';

class AdminController {
    private $adminModel;

    public function __construct() {
        $this->adminModel = new AdminModel();
    }

    public function dashboard() {
        // Verificar autenticación y rol
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /ads/views/login.php');
            exit;
        }

        // Obtener solicitudes pendientes
        return ['requests' => $this->adminModel->getPendingRequests()];
    }

    public function processRequest() {
        // Verificar autenticación y rol
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /ads/views/login.php');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return ['error' => 'Error de validación CSRF.'];
        }

        $requestId = filter_var($_POST['request_id'], FILTER_SANITIZE_NUMBER_INT);
        $action = filter_var($_POST['action'], FILTER_SANITIZE_STRING);

        if (!$requestId || !in_array($action, ['approve', 'reject'])) {
            return ['error' => 'Solicitud o acción inválida.'];
        }

        if ($action === 'approve') {
            // Solo obtener csv_path para aprobar
            $csvPath = isset($_POST['csv_path']) ? filter_var($_POST['csv_path'], FILTER_SANITIZE_STRING) : null;
            if (!$csvPath) {
                return ['error' => 'Ruta del CSV no proporcionada.'];
            }
            $result = $this->adminModel->approveRequest($requestId, $_SESSION['user_id'], $csvPath);
            if ($result['success']) {
                return ['success' => 'Solicitud aprobada exitosamente.'];
            } else {
                return ['error' => 'Error al aprobar la solicitud: ' . $result['error']];
            }
        } else {
            // No se necesita csv_path para rechazar
            if ($this->adminModel->rejectRequest($requestId, $_SESSION['user_id'])) {
                return ['success' => 'Solicitud rechazada exitosamente.'];
            } else {
                return ['error' => 'Error al rechazar la solicitud.'];
            }
        }
    }

    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
?>