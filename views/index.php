<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../controllers/SearchController.php';
$controller = new SearchController();
$data = $controller->publicSearch();
$results = $data['results'];
$error = $data['error'];
$refuge_name = $data['refuge_name'];

// Manejar paginación directamente en el index
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 30;

// Obtener estadísticas y datos de paginación
require_once __DIR__ . '/../config/database.php';
$db = (new Database())->getConnection();

// Solo aplicar paginación si no hay búsqueda POST ni refuge_id específico
$isDefaultView = ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['refuge_id']));

if ($isDefaultView) {
    // Contar total de personas
    $total_people = $db->query("SELECT COUNT(*) as count FROM people p JOIN requests r ON p.refuge_id = r.id WHERE r.status = 'approved'")->fetch(PDO::FETCH_ASSOC)['count'];
    $totalPages = ceil($total_people / $limit);
    
    // Obtener personas para la página actual
    $offset = ($page - 1) * $limit;
    $stmt = $db->prepare("
        SELECT p.id, p.name, p.status, p.entry_date, p.entry_time, r.refuge_name, r.location
        FROM people p
        JOIN requests r ON p.refuge_id = r.id
        WHERE r.status = 'approved'
        ORDER BY p.entry_date DESC, p.name ASC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$limit, $offset]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Para búsquedas y refugios específicos, mantener los resultados originales
    $total_people = $db->query("SELECT COUNT(*) as count FROM people p JOIN requests r ON p.refuge_id = r.id WHERE r.status = 'approved'")->fetch(PDO::FETCH_ASSOC)['count'];
    $totalPages = 1; // No paginación para búsquedas
}

// Estadísticas por estatus
require_once __DIR__ . '/../models/SearchModel.php';
$searchModel = new SearchModel();
$status_stats = $searchModel->getStatusStats();

// Determinar si mostrar paginación
$showPagination = $isDefaultView && $totalPages > 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Refugios - Bienvenidos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="bg-blue-600 text-white py-16 text-center">
            <div class="max-w-6xl mx-auto px-4">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Sistema de Refugios</h1>
                <p class="text-lg md:text-xl mb-6">Conectando comunidades en tiempos de crisis</p>
                <a href="#search" class="bg-white text-blue-600 px-6 py-3 rounded-md font-semibold hover:bg-gray-100 transition duration-200">Buscar Personas Albergadas</a>
            </div>
        </section>

        <!-- About Section -->
        <section class="py-12 bg-white">
            <div class="max-w-6xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center text-blue-600 mb-8">¿Qué es el Sistema de Refugios?</h2>
                <p class="text-lg text-gray-700 mb-6 text-center">Nuestra plataforma ayuda a gestionar refugios durante desastres, permitiendo a los administradores registrar personas albergadas y a las familias encontrar a sus seres queridos.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="bg-gray-100 p-6 rounded-lg shadow-md text-center">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Gestión Eficiente</h3>
                        <p class="text-gray-600">Los refugios pueden subir listas de albergados en formato CSV, que son revisadas por administradores.</p>
                    </div>
                    <div class="bg-gray-100 p-6 rounded-lg shadow-md text-center">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Búsqueda Inteligente</h3>
                        <p class="text-gray-600">Sistema de filtro progresivo que muestra resultados en tiempo real mientras escribes.</p>
                    </div>
                    <div class="bg-gray-100 p-6 rounded-lg shadow-md text-center">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Seguridad Garantizada</h3>
                        <p class="text-gray-600">Datos protegidos con medidas avanzadas de seguridad y privacidad.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Search Section -->
        <section id="search" class="py-12 bg-gray-100">
            <div x-data="searchData()" x-init="init()" class="max-w-6xl mx-auto px-4">
                <h2 class="text-3xl font-bold text-center mb-6 text-blue-600">Buscar Personas Albergadas</h2>
                
                <!-- Estadísticas Generales -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow-md text-center">
                        <h3 class="text-lg font-semibold text-gray-700">Total Registrados</h3>
                        <p class="text-2xl font-bold text-blue-600"><?php echo $total_people; ?></p>
                    </div>
                    <?php foreach (array_slice($status_stats, 0, 3) as $stat): ?>
                        <div class="bg-white p-4 rounded-lg shadow-md text-center">
                            <h3 class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($stat['status']); ?></h3>
                            <p class="text-xl font-bold text-green-600"><?php echo $stat['count']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Navegación de Refugios -->
                <?php include __DIR__ . '/partials/nav_refuges.php'; ?>

                <!-- Explicación de Estados de Personas -->
                <div class="bg-green-50 border-l-4 border-green-400 p-6 mb-6 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6M9 16h6M9 8h6M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-green-800">Estados de las Personas</h3>
                            <div class="mt-2 text-green-700 grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="flex items-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">Albergado</span>
                                    <span class="text-sm">Persona actualmente en el refugio</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-2">Pendiente</span>
                                    <span class="text-sm">En proceso de registro o verificación</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">En tránsito</span>
                                    <span class="text-sm">Moviéndose entre refugios o ubicaciones</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2">Dado de alta</span>
                                    <span class="text-sm">Ya no está en el refugio (reunificado)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Búsqueda por Nombre -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Filtrar por Nombre</h3>
                    <div class="relative">
                        <input type="text" id="filterInput" placeholder="Escribe para filtrar nombres (ej: Mar, Jos, Ana...)" 
                               x-model="query" @input="filterResults" @keydown.escape="clearFilter"
                               class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-lg">
                        <div class="absolute right-3 top-3 text-gray-400">
                            <svg x-show="!query" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <button x-show="query" @click="clearFilter" class="text-gray-500 hover:text-gray-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div x-show="query" class="mt-2 text-sm text-gray-600">
                        <span x-text="`Filtrando nombres que empiecen con: '${query}'`"></span>
                    </div>
                </div>

                <!-- Resultados -->
                <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
                    <div x-show="error" class="mb-4 p-4 bg-red-100 text-red-700 rounded-md" x-text="error"></div>
                    
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">
                            <?php if ($refuge_name): ?>
                                Personas en <?php echo htmlspecialchars($refuge_name); ?>
                            <?php elseif ($isDefaultView): ?>
                                Personas Albergadas - Página <?php echo $page; ?> de <?php echo $totalPages; ?>
                            <?php else: ?>
                                <span x-show="!query">Resultados de Búsqueda</span>
                                <span x-show="query" x-text="`Resultados para: '${query}'`"></span>
                            <?php endif; ?>
                        </h3>
                        <div class="text-sm text-gray-600">
                            <span x-show="results.length > 0" x-text="`${results.length} ${results.length === 1 ? 'persona' : 'personas'}`"></span>
                            <?php if ($isDefaultView && count($results) > 0): ?>
                                <span x-show="!query"><?php echo count($results); ?> personas</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($results)): ?>
                        <div x-data="{ results: <?php echo json_encode($results); ?> }">
                            <template x-if="results.length === 0">
                                <p class="text-center text-gray-500 py-8">No se encontraron personas con ese filtro.</p>
                            </template>
                            <template x-if="results.length > 0">
                                <div class="overflow-x-auto">
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
                                            <template x-for="result in results" :key="result.id">
                                                <tr class="hover:bg-gray-50">
                                                    <td class="border p-2" x-text="result.name"></td>
                                                    <td class="border p-2">
                                                        <span class="px-2 py-1 rounded-full text-xs font-medium"
                                                              :class="{
                                                                  'bg-green-100 text-green-800': result.status.toLowerCase() === 'albergado',
                                                                  'bg-yellow-100 text-yellow-800': result.status.toLowerCase() === 'pendiente',
                                                                  'bg-blue-100 text-blue-800': result.status.toLowerCase() === 'en tránsito',
                                                                  'bg-gray-100 text-gray-800': result.status.toLowerCase() === 'dado de alta'
                                                              }"
                                                              x-text="result.status">
                                                        </span>
                                                    </td>
                                                    <td class="border p-2" x-text="result.entry_date"></td>
                                                    <td class="border p-2" x-text="result.entry_time"></td>
                                                    <td class="border p-2" x-text="result.refuge_name"></td>
                                                    <td class="border p-2" x-text="result.location"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-500 py-8">
                            <?php if ($refuge_name): ?>
                                No hay personas registradas en <?php echo htmlspecialchars($refuge_name); ?>.
                            <?php else: ?>
                                No hay personas albergadas registradas actualmente.
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <!-- Paginación -->
                    <?php if ($showPagination): ?>
                        <div x-show="!query" class="mt-6 flex justify-center items-center space-x-2">
                            <?php if ($page > 1): ?>
                                <a href="?page=1#search" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Primera</a>
                                <a href="?page=<?php echo $page - 1; ?>#search" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Anterior</a>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <a href="?page=<?php echo $i; ?>#search" 
                                   class="px-3 py-2 rounded <?php echo $i == $page ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>#search" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Siguiente</a>
                                <a href="?page=<?php echo $totalPages; ?>#search" class="px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Última</a>
                            <?php endif; ?>
                        </div>
                        
                        <div x-show="!query" class="mt-4 text-center text-sm text-gray-600">
                            Mostrando página <?php echo $page; ?> de <?php echo $totalPages; ?> 
                            (<?php echo $total_people; ?> personas en total)
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- FAQs Section -->
        <?php include __DIR__ . '/partials/faqs.php'; ?>
    </main>
    
    <footer class="bg-blue-600 text-white py-4 text-center">
        <p>© 2025 Sistema de Refugios. Todos los derechos reservados.</p>
    </footer>

    <script>
        function searchData() {
            return {
                query: '',
                results: <?php echo json_encode($results); ?>,
                error: '<?php echo htmlspecialchars($error ?? ''); ?>',
                isLoading: false,
                
                async filterResults() {
                    if (this.isLoading) return;
                    
                    this.isLoading = true;
                    this.error = '';
                    
                    try {
                        const response = await fetch('/ads/controllers/SearchController.php?action=suggest', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ name: this.query })
                        });
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const data = await response.json();
                        
                        if (data.error) {
                            this.error = data.error;
                            this.results = [];
                        } else {
                            // Para el filtro, necesitamos obtener los datos completos
                            if (this.query.length >= 1) {
                                const filterResponse = await fetch('/ads/controllers/SearchController.php?action=filter', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({ name: this.query })
                                });
                                const filterData = await filterResponse.json();
                                this.results = filterData.results || [];
                            } else {
                                // Si no hay query, recargar página para mostrar paginación
                                window.location.href = '?page=1#search';
                                return;
                            }
                            this.error = '';
                        }
                    } catch (error) {
                        this.error = 'Error al filtrar resultados: ' + error.message;
                        this.results = [];
                    } finally {
                        this.isLoading = false;
                    }
                },
                
                clearFilter() {
                    this.query = '';
                    // Recargar la página para mostrar la paginación
                    window.location.href = '?page=1#search';
                },
                
                init() {
                    // Configuración inicial si es necesario
                }
            };
        }
    </script>
</body>
</html>