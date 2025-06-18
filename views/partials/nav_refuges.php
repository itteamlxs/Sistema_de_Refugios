<?php
require_once __DIR__ . '/../../config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT id, refuge_name, location FROM requests WHERE status = 'approved' ORDER BY refuge_name ASC");
$refuges = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h3 class="text-lg font-semibold text-gray-700 mb-4 text-center">Explorar Refugios</h3>
    <?php if (empty($refuges)): ?>
        <p class="text-gray-500 text-center">No hay refugios disponibles actualmente.</p>
    <?php else: ?>
        <ul class="space-y-2">
            <?php foreach ($refuges as $refuge): ?>
                <li class="flex items-center justify-between p-3 bg-gray-100 rounded-md">
                    <a href="/ads/views/index.php?refuge_id=<?php echo htmlspecialchars($refuge['id']); ?>" 
                       class="flex-grow text-gray-700 hover:text-blue-600 hover:underline">
                        <?php echo htmlspecialchars($refuge['refuge_name']); ?> (<?php echo htmlspecialchars($refuge['location']); ?>)
                    </a>
                    <div class="flex space-x-2">
                        <a href="/ads/controllers/SearchController.php?action=exportCsv&refuge_id=<?php echo htmlspecialchars($refuge['id']); ?>" 
                           class="bg-green-500 text-white px-2 py-1 rounded-md hover:bg-green-600 transition duration-200 text-sm" 
                           title="Descargar lista en CSV">
                            CSV
                        </a>
                        <a href="/ads/controllers/SearchController.php?action=exportPdf&refuge_id=<?php echo htmlspecialchars($refuge['id']); ?>" 
                           class="bg-red-500 text-white px-2 py-1 rounded-md hover:bg-red-600 transition duration-200 text-sm" 
                           title="Descargar lista en PDF">
                            PDF
                        </a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>