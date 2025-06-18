<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'refuge_user') {
    header('Location: /ads/views/login.php');
    exit;
}

require_once __DIR__ . '/../controllers/CsvUploadController.php';
$controller = new CsvUploadController();
$csrf_token = $controller->generateCsrfToken();

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->uploadCsv();
}

if (isset($_GET['action']) && $_GET['action'] === 'download_template') {
    $controller->downloadTemplate();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir CSV - Sistema de Refugios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="flex-grow flex items-center justify-center p-4">
        <div x-data="{ message: '<?php echo htmlspecialchars($result['success'] ?? $result['error'] ?? ''); ?>', isError: <?php echo isset($result['error']) ? 'true' : 'false'; ?> }" 
             class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">Subir Lista de Albergados</h2>
            <div x-show="message" x-cloak class="mb-4 p-4 rounded-md" 
                 :class="isError ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'" x-text="message"></div>
            <a href="?action=download_template" class="block text-center mb-4 text-blue-500 hover:underline">Descargar plantilla CSV</a>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div>
                    <label for="refuge_name" class="block text-sm font-medium text-gray-700">Nombre del Refugio</label>
                    <input type="text" name="refuge_name" id="refuge_name" required
                           class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Ubicación</label>
                    <input type="text" name="location" id="location" required
                           class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="csv_file" class="block text-sm font-medium text-gray-700">Archivo CSV</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required
                           class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                    <p class="text-sm text-gray-500 mt-1">Formato: nombre,estatus,fecha,hora</p>
                </div>
                <button type="submit"
                        class="w-full bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700 transition duration-200">Subir CSV</button>
            </form>
        </div>
    </main>
</body>
</html>