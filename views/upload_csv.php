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
    <main class="flex-grow p-4">
        <div class="max-w-4xl mx-auto">
            <div x-data="{ message: '<?php echo htmlspecialchars($result['success'] ?? $result['error'] ?? ''); ?>', isError: <?php echo isset($result['error']) ? 'true' : 'false'; ?> }" 
                 class="bg-white p-8 rounded-lg shadow-xl">
                <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">Subir Lista de Albergados</h2>
                
                <div x-show="message" x-cloak class="mb-6 p-4 rounded-md" 
                     :class="isError ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'" x-text="message"></div>

                <!-- Instrucciones Paso a Paso -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-6 mb-8 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-blue-800 mb-3">Instrucciones para Subir su Lista</h3>
                            <div class="text-blue-700 space-y-3">
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">1</span>
                                    <div>
                                        <p class="font-semibold">Descargue la plantilla CSV</p>
                                        <p class="text-sm">Haga clic en el enlace de abajo para descargar la plantilla con el formato correcto.</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">2</span>
                                    <div>
                                        <p class="font-semibold">Abra la plantilla en Excel o similar</p>
                                        <p class="text-sm">Puede usar Excel, Google Sheets, LibreOffice Calc o cualquier editor de hojas de cálculo.</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">3</span>
                                    <div>
                                        <p class="font-semibold">Complete los datos de las personas</p>
                                        <p class="text-sm">Llene cada fila con: nombre completo, estatus, fecha (AAAA-MM-DD), hora (HH:MM).</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">4</span>
                                    <div>
                                        <p class="font-semibold">Guarde como archivo CSV</p>
                                        <p class="text-sm">Importante: Debe guardar el archivo en formato CSV (separado por comas).</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <span class="bg-blue-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm font-semibold mr-3 mt-0.5">5</span>
                                    <div>
                                        <p class="font-semibold">Suba el archivo aquí</p>
                                        <p class="text-sm">Use el formulario de abajo para subir su archivo CSV completado.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formato y Ejemplo -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-8 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-lg font-medium text-yellow-800 mb-3">Formato Requerido y Ejemplos</h3>
                            <div class="text-yellow-700 space-y-2">
                                <p><strong>Columnas requeridas:</strong> nombre, estatus, fecha, hora</p>
                                <p><strong>Estados permitidos:</strong> Albergado, Pendiente, En tránsito, Dado de alta</p>
                                <p><strong>Formato de fecha:</strong> 2025-06-19 (AAAA-MM-DD)</p>
                                <p><strong>Formato de hora:</strong> 14:30 (24 horas, HH:MM)</p>
                                <div class="mt-4 bg-white p-3 rounded border">
                                    <p class="font-semibold text-gray-800 mb-2">Ejemplo de fila correcta:</p>
                                    <code class="text-sm text-gray-600">María García López,Albergado,2025-06-19,14:30</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enlace de Descarga -->
                <div class="text-center mb-8">
                    <a href="?action=download_template" 
                       class="inline-flex items-center px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-200 shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Descargar Plantilla CSV
                    </a>
                </div>

                <!-- Formulario de Subida -->
                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="refuge_name" class="block text-sm font-medium text-gray-700 mb-2">Nombre del Refugio</label>
                            <input type="text" name="refuge_name" id="refuge_name" required
                                   placeholder="Ej: Centro Comunitario Las Flores"
                                   class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Ubicación del Refugio</label>
                        <div class="space-y-2">
                            <div class="flex space-x-2">
                                <input type="text" name="location" id="location" required
                                       placeholder="Ej: Colonia Centro, Ciudad"
                                       class="flex-1 p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                <button type="button" id="getLocationBtn" 
                                        class="px-4 py-3 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-200 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    GPS
                                </button>
                            </div>
                            <div id="locationStatus" class="text-sm text-gray-500 hidden"></div>
                            <div id="coordinatesInfo" class="text-xs text-gray-400 hidden"></div>
                            <p class="text-sm text-gray-500">💡 Usa el botón GPS para obtener automáticamente tu ubicación actual</p>
                        </div>
                    </div>
                    </div>
                    
                    <div>
                        <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">Archivo CSV Completado</label>
                        <input type="file" name="csv_file" id="csv_file" accept=".csv" required
                               class="w-full p-3 border border-gray-300 rounded-md file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="text-sm text-gray-500 mt-2">Solo archivos CSV (máximo 2MB)</p>
                    </div>
                    
                    <button type="submit"
                            class="w-full bg-blue-600 text-white p-3 rounded-md hover:bg-blue-700 transition duration-200 font-semibold text-lg">
                        Subir Lista de Albergados
                    </button>
                </form>

                <!-- Información Adicional -->
                <div class="mt-8 bg-gray-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">Información Importante</h3>
                    <ul class="text-sm text-gray-600 space-y-2">
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            Su solicitud será revisada por un administrador antes de ser publicada.
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            Asegúrese de que todos los datos sean correctos antes de subir el archivo.
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            Use el botón GPS para obtener automáticamente la ubicación exacta de su refugio.
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            El archivo no debe exceder 2MB de tamaño.
                        </li>
                        <li class="flex items-start">
                            <span class="text-blue-500 mr-2">•</span>
                            Una vez aprobada, la información estará disponible públicamente para búsquedas.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const getLocationBtn = document.getElementById('getLocationBtn');
            const locationInput = document.getElementById('location');
            const locationStatus = document.getElementById('locationStatus');
            const coordinatesInfo = document.getElementById('coordinatesInfo');

            getLocationBtn.addEventListener('click', function() {
                if (!navigator.geolocation) {
                    showStatus('Tu navegador no soporta geolocalización', 'error');
                    return;
                }

                // Cambiar estado del botón
                getLocationBtn.disabled = true;
                getLocationBtn.innerHTML = `
                    <svg class="w-4 h-4 mr-1 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Obteniendo...
                `;

                showStatus('Obteniendo tu ubicación...', 'loading');

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        // Mostrar coordenadas
                        coordinatesInfo.textContent = `Coordenadas: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                        coordinatesInfo.classList.remove('hidden');

                        // Obtener dirección usando geocodificación reversa
                        reverseGeocode(lat, lng);
                    },
                    function(error) {
                        let errorMessage = 'Error al obtener ubicación: ';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage += 'Permiso denegado. Permite el acceso a la ubicación.';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage += 'Ubicación no disponible.';
                                break;
                            case error.TIMEOUT:
                                errorMessage += 'Tiempo de espera agotado.';
                                break;
                            default:
                                errorMessage += 'Error desconocido.';
                                break;
                        }
                        showStatus(errorMessage, 'error');
                        resetButton();
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            });

            function reverseGeocode(lat, lng) {
                showStatus('Obteniendo dirección...', 'loading');

                // Usar Nominatim (OpenStreetMap) para geocodificación reversa
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=es`;
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.display_name) {
                            // Formatear la dirección de manera más legible
                            let address = data.display_name;
                            
                            // Intentar extraer componentes más relevantes
                            if (data.address) {
                                const parts = [];
                                if (data.address.road) parts.push(data.address.road);
                                if (data.address.neighbourhood) parts.push(data.address.neighbourhood);
                                if (data.address.suburb) parts.push(data.address.suburb);
                                if (data.address.city || data.address.town || data.address.village) {
                                    parts.push(data.address.city || data.address.town || data.address.village);
                                }
                                if (data.address.state) parts.push(data.address.state);
                                
                                if (parts.length > 0) {
                                    address = parts.join(', ');
                                }
                            }

                            locationInput.value = address;
                            showStatus('✓ Ubicación obtenida correctamente', 'success');
                        } else {
                            showStatus('No se pudo obtener la dirección', 'error');
                        }
                        resetButton();
                    })
                    .catch(error => {
                        console.error('Error en geocodificación:', error);
                        showStatus('Error al obtener la dirección. Intenta escribirla manualmente.', 'error');
                        resetButton();
                    });
            }

            function showStatus(message, type) {
                locationStatus.textContent = message;
                locationStatus.classList.remove('hidden', 'text-blue-600', 'text-green-600', 'text-red-600');
                
                switch(type) {
                    case 'loading':
                        locationStatus.classList.add('text-blue-600');
                        break;
                    case 'success':
                        locationStatus.classList.add('text-green-600');
                        break;
                    case 'error':
                        locationStatus.classList.add('text-red-600');
                        break;
                }
                locationStatus.classList.remove('hidden');

                // Ocultar mensaje de éxito después de 3 segundos
                if (type === 'success') {
                    setTimeout(() => {
                        locationStatus.classList.add('hidden');
                    }, 3000);
                }
            }

            function resetButton() {
                getLocationBtn.disabled = false;
                getLocationBtn.innerHTML = `
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    GPS
                `;
            }
        });
    </script>
</body>
</html>