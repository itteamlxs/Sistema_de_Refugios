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
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Búsqueda Pública</h3>
                        <p class="text-gray-600">Cualquier persona puede buscar a sus seres queridos en los refugios registrados.</p>
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
                <!-- Resumen -->
                <div class="bg-white p-4 rounded-lg shadow-md text-center mb-6">
                    <h3 class="text-lg font-semibold text-gray-700">Total de Personas Albergadas</h3>
                    <p class="text-2xl font-bold text-blue-600"><?php echo $total_people; ?></p>
                </div>
                <!-- Navegación de Refugios -->
                <?php include __DIR__ . '/partials/nav_refuges.php'; ?>
                <!-- Búsqueda por Nombre -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Buscar por Nombre</h3>
                    <form method="POST" x-ref="searchForm" @submit.prevent="submitSearch">
                        <div class="relative">
                            <input type="text" name="name" id="name" placeholder="Ingrese el nombre" required
                                   x-model.debounce.300="query" @input="fetchSuggestions" @keydown.enter.prevent="submitSearch"
                                   @keydown.arrow-down.prevent="highlightNext" @keydown.arrow-up.prevent="highlightPrev"
                                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                            <!-- Sugerencias -->
                            <ul x-show="suggestions.length && query" x-cloak
                                class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                <template x-for="(suggestion, index) in suggestions" :key="index">
                                    <li @click="selectSuggestion(suggestion)" @mouseenter="highlightedIndex = index"
                                        :class="{ 'bg-blue-100': index === highlightedIndex }"
                                        class="p-2 cursor-pointer hover:bg-blue-50">
                                        <span x-text="suggestion"></span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <button type="submit" class="mt-2 bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700 transition duration-200">Buscar</button>
                    </form>
                </div>
                <!-- Resultados -->
                <div x-show="showResults" x-cloak class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
                    <div x-show="error" class="mb-4 p-4 bg-red-100 text-red-700 rounded-md" x-text="error"></div>
                    <?php if (!empty($results)): ?>
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            <?php echo $refuge_name ? 'Personas en ' . htmlspecialchars($refuge_name) : 'Resultados de búsqueda'; ?>
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
                                        <td class="border p-2"><?php echo htmlspecialchars($result['status']); ?></td>
                                        <td class="border p-2"><?php echo htmlspecialchars($result['entry_date']); ?></td>
                                        <td class="border p-2"><?php echo htmlspecialchars($result['entry_time']); ?></td>
                                        <td class="border p-2"><?php echo htmlspecialchars($result['refuge_name']); ?></td>
                                        <td class="border p-2"><?php echo htmlspecialchars($result['location']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php elseif (!$error && (($_SERVER['REQUEST_METHOD'] === 'POST') || isset($_GET['refuge_id']))): ?>
                        <p class="text-center text-gray-500">No se encontraron resultados.</p>
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
                suggestions: [],
                highlightedIndex: -1,
                showResults: <?php echo !empty($results) || ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) || isset($_GET['refuge_id']) ? 'true' : 'false'; ?>,
                error: '<?php echo htmlspecialchars($error ?? ''); ?>',
                async fetchSuggestions() {
                    if (this.query.length < 2) {
                        this.suggestions = [];
                        this.error = '';
                        return;
                    }
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
                            this.suggestions = [];
                        } else {
                            this.suggestions = data.suggestions || [];
                            this.error = '';
                        }
                        this.highlightedIndex = -1;
                    } catch (error) {
                        this.error = 'Error al obtener sugerencias: ' + error.message;
                        this.suggestions = [];
                    }
                },
                selectSuggestion(suggestion) {
                    this.query = suggestion;
                    this.suggestions = [];
                    this.submitSearch();
                },
                highlightNext() {
                    if (this.highlightedIndex < this.suggestions.length - 1) {
                        this.highlightedIndex++;
                    }
                },
                highlightPrev() {
                    if (this.highlightedIndex > -1) {
                        this.highlightedIndex--;
                    }
                },
                submitSearch() {
                    this.$refs.searchForm.submit();
                },
                init() {
                    this.showResults = <?php echo !empty($results) || ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) || isset($_GET['refuge_id']) ? 'true' : 'false'; ?>;
                }
            };
        }
    </script>
</body>
</html>