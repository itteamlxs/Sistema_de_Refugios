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
$isSearch = $data['isSearch'];
$page = $data['page'];
$totalPages = $data['totalPages'];
$totalPeople = $data['totalPeople'];

// Si es búsqueda, no mostrar paginación
$showPagination = !$isSearch && $totalPages > 1;
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
        <div x-data="{ error: '<?php echo htmlspecialchars($error ?? ''); ?>' }" 
             class="max-w-6xl mx-auto">
            <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">Buscar Personas Albergadas</h2>
            
            <!-- Resumen -->
            <div class="bg-white p-4 rounded-lg shadow-md mb-6 text-center">
                <h3 class="text-lg font-semibold text-gray-700">Total de Personas Albergadas</h3>
                <p class="text-2xl font-bold text-blue-600"><?php echo $totalPeople; ?></p>
                <?php if (!$isSearch): ?>
                    <p class="text-sm text-gray-500">Mostrando página <?php echo $page; ?> de <?php echo $totalPages; ?></p>
                <?php endif; ?>
            </div>
            
            <div x-show="error" x-cloak class="mb-4 p-4 bg-red-100 text-red-700 rounded-md" x-text="error"></div>
            
            <!-- Formulario de búsqueda -->
            <form method="POST" class="mb-6 bg-white p-6 rounded-lg shadow-md">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="flex items-center space-x-2">
                    <input type="text" name="name" id="name" placeholder="Ingrese el nombre para buscar" 
                           class="flex-1 p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700 transition duration-200">Buscar</button>
                    <?php if ($isSearch): ?>
                        <a href="/ads/views/search.php" class="bg-gray-500 text-white p-2 rounded-md hover:bg-gray-600 transition duration-200">Ver Todos</a>
                    <?php endif; ?>
                </div>
            </form>
            
            <!-- Resultados -->
            <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
                <?php if (empty($results)): ?>
                    <p class="text-center text-gray-500">
                        <?php echo $isSearch ? 'No se encontraron resultados para la búsqueda.' : 'No hay personas albergadas registradas.'; ?>
                    </p>
                <?php else: ?>
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        <?php echo $isSearch ? 'Resultados de búsqueda' : 'Todas las Personas Albergadas'; ?>
                        (<?php echo count($results); ?> resultados)
                    </h3>
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
                                    <td class="border p-2">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                            <?php 
                                                switch(strtolower($result['status'])) {
                                                    case 'albergado':
                                                        echo 'bg-green-100 text-green-800';
                                                        break;
                                                    case 'pendiente':
                                                        echo 'bg-yellow-100 text-yellow-800';
                                                        break;
                                                    case 'en tránsito':
                                                        echo 'bg-blue-100 text-blue-800';
                                                        break;
                                                    case 'dado de alta':
                                                        echo 'bg-gray-100 text-gray-800';
                                                        break;
                                                    default:
                                                        echo 'bg-gray-100 text-gray-800';
                                                }
                                            ?>">
                                            <?php echo htmlspecialchars($result['status']); ?>
                                        </span>
                                    </td>
                                    <td class="border p-2"><?php echo htmlspecialchars($result['entry_date']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($result['entry_time']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($result['refuge_name']); ?></td>
                                    <td class="border p-2"><?php echo htmlspecialchars($result['location']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Paginación -->
                    <?php if ($showPagination): ?>
                        <div class="mt-6 flex justify-center items-center space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=1" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Primera</a>
                                <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Anterior</a>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <a href="?page=<?php echo $i; ?>" 
                                   class="px-3 py-2 rounded <?php echo $i == $page ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Siguiente</a>
                                <a href="?page=<?php echo $totalPages; ?>" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Última</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4 text-center text-sm text-gray-600">
                            Mostrando página <?php echo $page; ?> de <?php echo $totalPages; ?> 
                            (<?php echo $totalPeople; ?> personas en total)
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>