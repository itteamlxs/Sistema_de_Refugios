<?php
require_once __DIR__ . '/../config/database.php'; // Debería resolver a /opt/lampp/htdocs/ads/config/database.php

class UserModel {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function authenticate($email, $password) {
        $stmt = $this->db->prepare("SELECT login_attempts, last_attempt FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['login_attempts'] >= 5 && 
            strtotime($user['last_attempt']) > time() - 900) {
            return ['error' => 'Demasiados intentos. Intenta de nuevo en 15 minutos.'];
        }

        $stmt = $this->db->prepare("SELECT id, email, password_hash, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $stmt = $this->db->prepare("UPDATE users SET login_attempts = 0, last_attempt = NULL WHERE email = ?");
            $stmt->execute([$email]);
            return $user;
        } else {
            $stmt = $this->db->prepare("UPDATE users SET login_attempts = login_attempts + 1, last_attempt = NOW() WHERE email = ?");
            $stmt->execute([$email]);
            return ['error' => 'Credenciales inválidas.'];
        }
    }
}
?>