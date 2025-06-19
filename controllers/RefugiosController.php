<?php
require_once __DIR__ . '/../models/RefugiosModel.php';

// Ruta absoluta a TCPDF
$tcpdf_path = '/opt/lampp/htdocs/ads/lib/tcpdf/tcpdf.php';
if (!file_exists($tcpdf_path)) {
    die('Error: No se encontró TCPDF en ' . htmlspecialchars($tcpdf_path) . '. Verifica que la carpeta /opt/lampp/htdocs/ads/lib/tcpdf/ exista y contenga tcpdf.php.');
}
require_once $tcpdf_path;

class RefugiosController {
    private $refugiosModel;

    public function __construct() {
        $this->refugiosModel = new RefugiosModel();
    }

    public function getCatalogo() {
        $refugios = [];
        $stats = [];
        $query = '';
        $error = null;

        try {
            // Obtener estadísticas generales
            $stats = $this->refugiosModel->getRefugiosStats();
            
            // Verificar si hay búsqueda
            if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                $query = trim($_GET['search']);
                $refugios = $this->refugiosModel->searchRefugios($query);
            } else {
                $refugios = $this->refugiosModel->getAllRefugios();
            }
            
        } catch (Exception $e) {
            $error = 'Error al cargar los refugios: ' . $e->getMessage();
        }

        return [
            'refugios' => $refugios,
            'stats' => $stats,
            'query' => $query,
            'error' => $error
        ];
    }

    public function exportCsv() {
        $refuge_id = filter_var($_GET['refuge_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        if (!$refuge_id) {
            header('HTTP/1.1 400 Bad Request');
            echo 'ID de refugio inválido.';
            exit;
        }

        $refugio = $this->refugiosModel->getRefugioById($refuge_id);
        if (!$refugio) {
            header('HTTP/1.1 404 Not Found');
            echo 'Refugio no encontrado.';
            exit;
        }

        $people = $this->refugiosModel->getPeopleByRefugio($refuge_id);
        if (empty($people)) {
            header('HTTP/1.1 404 Not Found');
            echo 'No hay personas registradas en este refugio.';
            exit;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $refugio['refuge_name']) . '_lista_albergados.csv"');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // BOM para UTF-8 en Excel
        fputcsv($output, ['Nombre', 'Estatus', 'Fecha de Ingreso', 'Hora de Ingreso', 'Refugio', 'Ubicación']);

        foreach ($people as $person) {
            fputcsv($output, [
                $person['name'],
                $person['status'],
                $person['entry_date'],
                $person['entry_time'],
                $person['refuge_name'],
                $person['location']
            ]);
        }

        fclose($output);
        exit;
    }

    public function exportPdf() {
        $refuge_id = filter_var($_GET['refuge_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        if (!$refuge_id) {
            header('HTTP/1.1 400 Bad Request');
            echo 'ID de refugio inválido.';
            exit;
        }

        $refugio = $this->refugiosModel->getRefugioById($refuge_id);
        if (!$refugio) {
            header('HTTP/1.1 404 Not Found');
            echo 'Refugio no encontrado.';
            exit;
        }

        $people = $this->refugiosModel->getPeopleByRefugio($refuge_id);
        if (empty($people)) {
            header('HTTP/1.1 404 Not Found');
            echo 'No hay personas registradas en este refugio.';
            exit;
        }

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema de Refugios');
        $pdf->SetAuthor('Sistema de Refugios');
        $pdf->SetTitle('Lista de Albergados - ' . $refugio['refuge_name']);
        $pdf->SetSubject('Lista de personas albergadas');
        $pdf->SetHeaderData('', 0, 'Sistema de Refugios', 'Lista de Albergados - ' . $refugio['refuge_name']);
        $pdf->setHeaderFont(['helvetica', '', 10]);
        $pdf->setFooterFont(['helvetica', '', 8]);
        $pdf->SetMargins(10, 20, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        $html = '
        <h1 style="text-align:center; color:#1e40af;">Lista de Albergados</h1>
        <h2 style="text-align:center; color:#374151;">' . htmlspecialchars($refugio['refuge_name']) . '</h2>
        <p style="text-align:center; color:#6b7280; margin-bottom:20px;">Ubicación: ' . htmlspecialchars($refugio['location']) . '</p>
        <p style="text-align:center; color:#6b7280; margin-bottom:20px;">Total de personas: ' . count($people) . ' | Generado el: ' . date('d/m/Y H:i') . '</p>
        
        <table border="1" cellpadding="8" style="font-size:10px; width:100%;">
            <thead>
                <tr style="background-color:#e5e7eb; font-weight:bold;">
                    <th style="width:25%;">Nombre</th>
                    <th style="width:15%;">Estatus</th>
                    <th style="width:15%;">Fecha de Ingreso</th>
                    <th style="width:15%;">Hora de Ingreso</th>
                    <th style="width:30%;">Ubicación</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($people as $person) {
            $statusColor = '';
            switch (strtolower($person['status'])) {
                case 'albergado':
                    $statusColor = 'background-color:#dcfce7; color:#166534;';
                    break;
                case 'pendiente':
                    $statusColor = 'background-color:#fef3c7; color:#92400e;';
                    break;
                case 'en tránsito':
                    $statusColor = 'background-color:#dbeafe; color:#1e40af;';
                    break;
                case 'dado de alta':
                    $statusColor = 'background-color:#f3f4f6; color:#374151;';
                    break;
            }

            $html .= '
                <tr>
                    <td>' . htmlspecialchars($person['name']) . '</td>
                    <td style="' . $statusColor . '">' . htmlspecialchars($person['status']) . '</td>
                    <td>' . htmlspecialchars($person['entry_date']) . '</td>
                    <td>' . htmlspecialchars($person['entry_time']) . '</td>
                    <td>' . htmlspecialchars($person['location']) . '</td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>
        
        <p style="margin-top:20px; font-size:8px; color:#6b7280;">
            Este documento fue generado automáticamente por el Sistema de Refugios.<br>
            Para más información visite: ' . $_SERVER['HTTP_HOST'] . '/ads/views/refugios.php
        </p>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output(str_replace(' ', '_', $refugio['refuge_name']) . '_lista_albergados.pdf', 'D');
        exit;
    }
}

// Manejo de acciones
if (isset($_GET['action'])) {
    $controller = new RefugiosController();
    switch ($_GET['action']) {
        case 'exportCsv':
            $controller->exportCsv();
            break;
        case 'exportPdf':
            $controller->exportPdf();
            break;
        default:
            header('HTTP/1.1 400 Bad Request');
            echo 'Acción inválida.';
            exit;
    }
}
?>