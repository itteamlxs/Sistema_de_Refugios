<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/../controllers/LoginController.php';
$controller = new LoginController();
$csrf_token = $controller->generateCsrfToken();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->login();
    if (isset($result['error'])) {
        $error = $result['error'];
    } elseif (isset($result['success'])) {
        header('Location: ' . ($result['role'] === 'admin' ? '/ads/views/admin_dashboard.php' : '/ads/views/search.php'));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sistema de Refugios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <?php include __DIR__ . '/partials/nav.php'; ?>
    <main class="flex-grow flex items-center justify-center p-4">
        <div x-data="{ error: '<?php echo htmlspecialchars($error ?? ''); ?>' }" class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
            <h2 class="text-2xl font-bold mb-6 text-center text-blue-600">Iniciar Sesión</h2>
            <div x-show="error" x-cloak class="mb-4 p-4 bg-red-100 text-red-700 rounded-md" x-text="error"></div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico</label>
                    <input type="email" name="email" id="email" required
                           class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                    <input type="password" name="password" id="password" required
                           class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit"
                        class="w-full bg-blue-600 text-white p-2 rounded-md hover:bg-blue-700 transition duration-200">Iniciar Sesión</button>
            </form>
        </div>
    </main>
</body>
</html>