<?php
require_once __DIR__ . '/../models/UserModel.php'; // Debería resolver a /opt/lampp/htdocs/ads/models/UserModel.php

class LoginController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    public function login() {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die('Error de validación CSRF.');
        }

        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['error' => 'Correo inválido.'];
        }

        $result = $this->userModel->authenticate($email, $password);

        if (isset($result['error'])) {
            return $result;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $result['id'];
        $_SESSION['role'] = $result['role'];

        return ['success' => true, 'role' => $result['role']];
    }

    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
?>