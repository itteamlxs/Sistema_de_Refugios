<?php
$role = isset($_SESSION['role']) ? $_SESSION['role'] : null;
?>
<nav class="bg-blue-600 text-white shadow-lg">
    <div class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center">
        <a href="/ads/views/index.php" class="text-xl font-bold">Sistema de Refugios</a>
        <div class="space-x-4">
            <?php if ($role): ?>
                <a href="/ads/views/search.php" class="hover:underline">Búsqueda</a>
            <?php else: ?>
                <a href="/ads/views/index.php#search" class="hover:underline">Búsqueda</a>
            <?php endif; ?>
            <a href="/ads/views/refugios.php" class="hover:underline">Refugios</a>
            <?php if ($role === 'admin'): ?>
                <a href="/ads/views/admin_dashboard.php" class="hover:underline">Dashboard</a>
            <?php elseif ($role === 'refuge_user'): ?>
                <a href="/ads/views/upload_csv.php" class="hover:underline">Subir CSV</a>
            <?php endif; ?>
            <?php if ($role): ?>
                <a href="/ads/views/logout.php" class="hover:underline">Cerrar Sesión</a>
            <?php else: ?>
                <a href="/ads/views/login.php" class="hover:underline">Iniciar Sesión</a>
            <?php endif; ?>
        </div>
    </div>
</nav>