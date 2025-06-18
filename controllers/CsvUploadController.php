<?php
require_once __DIR__ . '/../models/CsvModel.php';

class CsvUploadController {
    private $csvModel;
    private $uploadDir = __DIR__ . '/../uploads/';
    private $maxFileSize = 2 * 1024 * 1024; // 2MB
    private $allowedExtensions = ['csv'];

    public function __construct() {
        $this->csvModel = new CsvModel();
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                die('Error: No se pudo crear la carpeta uploads/.');
            }
        }
        if (!is_writable($this->uploadDir)) {
            die('Error: La carpeta uploads/ no tiene permisos de escritura.');
        }
    }

    public function uploadCsv() {
        // Verificar autenticación y rol
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'refuge_user') {
            header('Location: /ads/views/login.php');
            exit;
        }

        // Verificar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            return ['error' => 'Error de validación CSRF.'];
        }

        // Sanitizar entradas
        $refugeName = filter_var($_POST['refuge_name'], FILTER_SANITIZE_STRING);
        $location = filter_var($_POST['location'], FILTER_SANITIZE_STRING);
        $ip = $_SERVER['REMOTE_ADDR'];

        if (!$refugeName || !$location) {
            return ['error' => 'Nombre del refugio y ubicación son obligatorios.'];
        }

        // Validar archivo CSV
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] === UPLOAD_ERR_NO_FILE) {
            return ['error' => 'No se ha subido ningún archivo.'];
        }

        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'Error al subir el archivo: Código ' . $file['error']];
        }

        if ($file['size'] > $this->maxFileSize) {
            return ['error' => 'El archivo excede el tamaño máximo de 2MB.'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return ['error' => 'Solo se permiten archivos CSV.'];
        }

        // Validar contenido del CSV
        if (!$this->validateCsvContent($file['tmp_name'])) {
            return ['error' => 'El archivo CSV no tiene el formato correcto. Verifique los encabezados: nombre,estatus,fecha,hora.'];
        }

        // Generar nombre único para el archivo
        $fileName = uniqid('csv_') . '.' . $extension;
        $destination = $this->uploadDir . $fileName;

        // Mover archivo a carpeta segura
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['error' => 'Error al guardar el archivo. Verifique permisos de la carpeta uploads/.'];
        }

        // Guardar solicitud en la base de datos
        $csvPath = 'uploads/' . $fileName;
        if ($this->csvModel->saveRequest($_SESSION['user_id'], $refugeName, $location, $ip, $csvPath)) {
            return ['success' => 'Archivo subido exitosamente. Pendiente de aprobación.'];
        } else {
            unlink($destination); // Eliminar archivo si falla la DB
            return ['error' => 'Error al registrar la solicitud en la base de datos.'];
        }
    }

    private function validateCsvContent($filePath) {
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

        $row = fgetcsv($handle);
        if (!$row || count($row) !== 4) {
            fclose($handle);
            return false;
        }

        fclose($handle);
        return true;
    }

    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function downloadTemplate() {
        $filename = 'template_albergados.csv';
        $content = "nombre,estatus,fecha,hora\nEjemplo Pérez,Albergado,2025-06-17,14:30\n";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $content;
        exit;
    }
}
?>