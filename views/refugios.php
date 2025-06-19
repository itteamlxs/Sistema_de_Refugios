<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../controllers/RefugiosController.php';
$controller = new RefugiosController();
$data = $controller->getCatalogo();
$refugios = $data['refugios'];
$stats = $data['stats'];
$query = $data['query'];
$error = $data['error'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Refugios - Sistema de Refugios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    
    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-blue-600 to-blue-800 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">Catálogo de Refugios</h1>
                <p class="text-lg md:text-xl mb-6">Todos los refugios activos y sus personas albergadas</p>
                
                <!-- Estadísticas Generales -->
                <?php if ($stats): ?>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-8 max-w-4xl mx-auto">
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm p-4 rounded-lg">
                        <h3 class="text-2xl font-bold"><?php echo $stats['total_refugios'] ?? 0; ?></h3>
                        <p class="text-blue-100">Refugios Activos</p>
                    </div>
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm p-4 rounded-lg">
                        <h3 class="text-2xl font-bold"><?php echo $stats['total_personas'] ?? 0; ?></h3>
                        <p class="text-blue-100">Personas Albergadas</p>
                    </div>
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm p-4 rounded-lg">
                        <h3 class="text-2xl font-bold"><?php echo $stats['personas_activas'] ?? 0; ?></h3>
                        <p class="text-blue-100">Actualmente Activos</p>
                    </div>
                    <div class="bg-white bg-opacity-20 backdrop-blur-sm p-4 rounded-lg">
                        <h3 class="text-2xl font-bold"><?php echo round($stats['promedio_por_refugio'] ?? 0); ?></h3>
                        <p class="text-blue-100">Promedio por Refugio</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Filtros y Búsqueda -->
        <section class="py-8 bg-white border-b">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex-1 max-w-md">
                        <form method="GET" class="relative">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($query); ?>" 
                                   placeholder="Buscar refugios por nombre o ubicación..."
                                   class="w-full p-3 pl-10 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <button type="submit" class="absolute right-2 top-2 bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700">
                                Buscar
                            </button>
                        </form>
                    </div>
                    
                    <?php if ($query): ?>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-600">Resultados para: "<?php echo htmlspecialchars($query); ?>"</span>
                        <a href="/ads/views/refugios.php" class="text-blue-600 hover:underline">Limpiar filtro</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Catálogo de Refugios -->
        <section class="py-12">
            <div class="max-w-7xl mx-auto px-4">
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($refugios)): ?>
                    <div class="text-center py-12">
                        <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No se encontraron refugios</h3>
                        <p class="text-gray-500">
                            <?php echo $query ? 'Intenta con otros términos de búsqueda.' : 'No hay refugios registrados actualmente.'; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php foreach ($refugios as $refugio): ?>
                            <div class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden">
                                <!-- Header de la tarjeta -->
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4">
                                    <h3 class="font-bold text-lg mb-2 line-clamp-2"><?php echo htmlspecialchars($refugio['refuge_name']); ?></h3>
                                    <p class="text-blue-100 text-sm flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <?php echo htmlspecialchars($refugio['location']); ?>
                                    </p>
                                </div>

                                <!-- Estadísticas del refugio -->
                                <div class="p-4">
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-blue-600"><?php echo $refugio['total_albergados']; ?></div>
                                            <div class="text-xs text-gray-500">Total Albergados</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-green-600"><?php echo $refugio['activos']; ?></div>
                                            <div class="text-xs text-gray-500">Activos</div>
                                        </div>
                                    </div>

                                    <!-- Estados detallados -->
                                    <?php if ($refugio['total_albergados'] > 0): ?>
                                    <div class="grid grid-cols-2 gap-2 mb-4 text-xs">
                                        <?php if ($refugio['en_transito'] > 0): ?>
                                        <div class="bg-blue-50 p-2 rounded text-center">
                                            <span class="font-semibold text-blue-800"><?php echo $refugio['en_transito']; ?></span>
                                            <span class="text-blue-600">En tránsito</span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($refugio['pendientes'] > 0): ?>
                                        <div class="bg-yellow-50 p-2 rounded text-center">
                                            <span class="font-semibold text-yellow-800"><?php echo $refugio['pendientes']; ?></span>
                                            <span class="text-yellow-600">Pendientes</span>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($refugio['dados_alta'] > 0): ?>
                                        <div class="bg-gray-50 p-2 rounded text-center col-span-2">
                                            <span class="font-semibold text-gray-800"><?php echo $refugio['dados_alta']; ?></span>
                                            <span class="text-gray-600">Dados de alta</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Botones de acción -->
                                    <div class="space-y-2">
                                        <!-- Botones de descarga -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="/ads/controllers/RefugiosController.php?action=exportCsv&refuge_id=<?php echo $refugio['id']; ?>" 
                                               class="bg-green-600 text-white px-3 py-2 rounded text-center text-sm hover:bg-green-700 transition-colors flex items-center justify-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                CSV
                                            </a>
                                            <a href="/ads/controllers/RefugiosController.php?action=exportPdf&refuge_id=<?php echo $refugio['id']; ?>" 
                                               class="bg-red-600 text-white px-3 py-2 rounded text-center text-sm hover:bg-red-700 transition-colors flex items-center justify-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                PDF
                                            </a>
                                        </div>
                                        
                                        <!-- Botón de mapa -->
                                        <a href="https://maps.google.com/maps?q=<?php echo urlencode($refugio['location']); ?>" 
                                           target="_blank" rel="noopener noreferrer"
                                           class="w-full bg-blue-600 text-white px-3 py-2 rounded text-center text-sm hover:bg-blue-700 transition-colors flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                            </svg>
                                            Ver en Google Maps
                                        </a>

                                        <!-- Botón ver personas -->
                                        <a href="/ads/views/index.php?refuge_id=<?php echo $refugio['id']; ?>#search" 
                                           class="w-full bg-gray-600 text-white px-3 py-2 rounded text-center text-sm hover:bg-gray-700 transition-colors flex items-center justify-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Ver Personas
                                        </a>
                                    </div>

                                    <!-- Fecha de registro -->
                                    <div class="mt-3 pt-3 border-t border-gray-200">
                                        <p class="text-xs text-gray-500 text-center">
                                            Registrado: <?php echo date('d/m/Y', strtotime($refugio['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Resumen de resultados -->
                    <div class="mt-8 text-center text-gray-600">
                        <p>Mostrando <?php echo count($refugios); ?> refugio<?php echo count($refugios) !== 1 ? 's' : ''; ?>
                        <?php if ($query): ?>
                            para la búsqueda "<?php echo htmlspecialchars($query); ?>"
                        <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <footer class="bg-blue-600 text-white py-6">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p>© 2025 Sistema de Refugios. Todos los derechos reservados.</p>
            <p class="text-blue-200 text-sm mt-2">Conectando comunidades en tiempos de crisis</p>
        </div>
    </footer>
</body>
</html>