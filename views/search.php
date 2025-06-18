<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /ads/views/login.php');
    exit;
}

require_once __DIR__ . '/../controllers/SearchController.php';
$controller = new SearchController();
$csrf_token = $controller->generateCsrfToken();

$data = $controller->search();
$results = $data['results'];
$error = $data['error'];

// Contar personas para resumen
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();
$total_people = $db->query("SELECT COUNT(*) as count FROM people")->fetch(PDO::FETCH_ASSOC)['count'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Personas Albergadas - Sistema de Refugios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="flex-grow p-4">
        <div x-data="{ error: '<?php echo htmlspecialchars($error ?? ''); ?>', showResults: <?php echo !empty($results) || ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) ? 'true' : 'false'; ?> }" 
             class="max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">Buscar Personas Albergadas</h2>
            <!-- Resumen -->
            <div class="bg-white p-4 rounded-lg shadow-md mb-6 text-center">
                <h3 class="text-lg font-semibold text-gray-700">Total de Personas Albergadas</h3>
                <p class="text-2xl font-bold text-blue-600"><?php echo $total_people; ?></p>
            </div>
            <div x-show="error" x-cloak class="mb-4 p-4 bg-red-100 text-red-700 rounded-md" x-text="error"></div>
            <form method="POST" class="mb-6 bg-white p-6 rounded-lg shadow-md">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="flex items-center space-x-2">
                    <input type="text" name="name" id="name" placeholder="Ingrese el nombre" required
                           class="flex-1 p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700 transition duration-200">Buscar</button>
                </div>
            </form>
            <div x-show="showResults" x-cloak class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
                <?php if (empty($results)): ?>
                    <p class="text-center text-gray-500">No se encontraron resultados.</p>
                <?php else: ?>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border p-2 text-left">Nombre</th>
                                <th class="border p-2 text-left">Estatus</th>
                                <th class="border p-2 text-left">Fecha de Ingreso</th>
                                <th class="border p-2 text-left">Hora de Ingreso</th>
                                <th class="border p-2 text-left">Refugio</th>
                                <th class="border p-2 text-left">Ubicación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $result): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="border p-2"><?php echo htmlspecialchars($result['name']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($result['status']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($result['entry_date']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($result['entry_time']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($result['refuge_name']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($result['location']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>