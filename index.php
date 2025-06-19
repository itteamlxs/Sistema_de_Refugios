<?php
/**
 * Sistema de Refugios v1.3.0
 * Punto de entrada principal de la aplicación
 * 
 * Este archivo redirige a la vista principal de la aplicación
 * y maneja algunas configuraciones básicas de seguridad.
 */

// Configuraciones de seguridad básicas
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    // Configurar cookies de sesión seguras
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    
    session_start();
}

// Headers de seguridad básicos
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Verificar que el directorio views existe
$viewsPath = __DIR__ . '/views/index.php';
if (!file_exists($viewsPath)) {
    // Si no existe la vista principal, mostrar error básico
    http_response_code(500);
    die('Error: No se pudo encontrar la aplicación. Verifique la instalación.');
}

// Redirigir a la vista principal
header('Location: /ads/views/index.php');
exit;
?>