<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /ads/views/login.php');
    exit;
}

require_once __DIR__ . '/../controllers/AdminController.php';
$controller = new AdminController();
$csrf_token = $controller->generateCsrfToken();

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->processRequest();
}

$requests = $controller->dashboard()['requests'];

// Contar solicitudes para resumen
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();
$stats = $db->query("SELECT COUNT(*) as total, SUM(status = 'pending') as pending, SUM(status = 'approved') as approved, SUM(status = 'rejected') as rejected FROM requests")->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Administración - Sistema de Refugios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="flex-grow p-4">
        <div x-data="{ message: '<?php echo htmlspecialchars($result['success'] ?? $result['error'] ?? ''); ?>', isError: <?php echo isset($result['error']) ? 'true' : 'false'; ?> }" 
             class="max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">Dashboard de Administración</h2>
            <div x-show="message" x-cloak class="mb-4 p-4 rounded-md" 
                 :class="isError ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'" x-text="message"></div>
            <!-- Resumen de estadísticas -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded-lg shadow-md text-center">
                    <h3 class="text-lg font-semibold text-gray-700">Total Solicitudes</h3>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $stats['total']; ?></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md text-center">
                    <h3 class="text-lg font-semibold text-gray-700">Pendientes</h3>
                    <p class="text-2xl font-bold text-yellow-600"><?php echo $stats['pending']; ?></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md text-center">
                    <h3 class="text-lg font-semibold text-gray-700">Aprobadas</h3>
                    <p class="text-2xl font-bold text-green-600"><?php echo $stats['approved']; ?></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md text-center">
                    <h3 class="text-lg font-semibold text-gray-700">Rechazadas</h3>
                    <p class="text-2xl font-bold text-red-600"><?php echo $stats['rejected']; ?></p>
                </div>
            </div>
            <!-- Tabla de solicitudes -->
            <?php if (empty($requests)): ?>
                <p class="text-center text-gray-500">No hay solicitudes pendientes.</p>
            <?php else: ?>
                <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border p-2 text-left">ID</th>
                                <th class="border p-2 text-left">Refugio</th>
                                <th class="border p-2 text-left">Ubicación</th>
                                <th class="border p-2 text-left">IP</th>
                                <th class="border p-2 text-left">Usuario</th>
                                <th class="border p-2 text-left">Fecha</th>
                                <th class="border p-2 text-left">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requests as $request): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="border p-2"><?php echo htmlspecialchars($request['id']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($request['refuge_name']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($request['location']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($request['ip']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($request['email']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($request['created_at']); ?></td>
                                    <td class="border p-2 flex space-x-2">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                            <input type="hidden" name="csv_path" value="<?php echo htmlspecialchars($request['csv_path']); ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition duration-200">Aprobar</button>
                                        </form>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($request['id']); ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition duration-200">Rechazar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>