<?php
require_once __DIR__ . '/../models/SearchModel.php';

// Ruta absoluta a TCPDF
$tcpdf_path = '/opt/lampp/htdocs/ads/lib/tcpdf/tcpdf.php';
if (!file_exists($tcpdf_path)) {
    die('Error: No se encontró TCPDF en ' . htmlspecialchars($tcpdf_path) . '. Verifica que la carpeta /opt/lampp/htdocs/ads/lib/tcpdf/ exista y contenga tcpdf.php.');
}
require_once $tcpdf_path;

class SearchController {
    private $searchModel;

    public function __construct() {
        $this->searchModel = new SearchModel();
    }

    public function search() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /ads/views/index.php');
            exit;
        }

        $results = [];
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $error = 'Error de validación CSRF.';
            } else {
                $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
                if (strlen($name) < 2) {
                    $error = 'El nombre debe tener al menos 2 caracteres.';
                } else {
                    $results = $this->searchModel->searchPeople($name);
                }
            }
        }

        return ['results' => $results, 'error' => $error];
    }

    public function publicSearch() {
        $results = [];
        $error = null;
        $refuge_name = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
            if (strlen($name) < 2) {
                $error = 'El nombre debe tener al menos 2 caracteres.';
            } else {
                $results = $this->searchModel->searchPeople($name);
            }
        } elseif (isset($_GET['refuge_id'])) {
            $refuge_id = filter_var($_GET['refuge_id'], FILTER_SANITIZE_NUMBER_INT);
            if ($refuge_id) {
                $results = $this->searchModel->searchPeopleByRefuge($refuge_id);
                $refuge_data = $this->searchModel->getRefugeInfo($refuge_id);
                $refuge_name = $refuge_data['refuge_name'] ?? null;
                if (empty($results) && !$error) {
                    $error = 'No hay personas registradas en este refugio.';
                }
            } else {
                $error = 'ID de refugio inválido.';
            }
        }

        return ['results' => $results, 'error' => $error, 'refuge_name' => $refuge_name];
    }

    public function publicSuggest() {
        header('Content-Type: application/json');
        $response = ['suggestions' => [], 'error' => null];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $response['error'] = 'Método no permitido.';
            echo json_encode($response);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $response['error'] = 'Formato JSON inválido.';
            echo json_encode($response);
            exit;
        }

        $name = filter_var($input['name'] ?? '', FILTER_SANITIZE_STRING);
        if (strlen($name) < 2) {
            $response['error'] = 'El nombre debe tener al menos 2 caracteres.';
            echo json_encode($response);
            exit;
        }

        try {
            $response['suggestions'] = $this->searchModel->suggestNames($name);
        } catch (Exception $e) {
            $response['error'] = 'Error en la base de datos.';
            http_response_code(500);
        }

        echo json_encode($response);
        exit;
    }

    public function exportCsv() {
        $refuge_id = filter_var($_GET['refuge_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
        if (!$refuge_id) {
            header('HTTP/1.1 400 Bad Request');
            echo 'ID de refugio inválido.';
            exit;
        }

        $people = $this->searchModel->searchPeopleByRefuge($refuge_id);
        $refuge_data = $this->searchModel->getRefugeInfo($refuge_id);
        $refuge_name = $refuge_data['refuge_name'] ?? 'Refugio';

        if (empty($people)) {
            header('HTTP/1.1 404 Not Found');
            echo 'No hay personas registradas en este refugio.';
            exit;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $refuge_name) . '_lista_albergados.csv"');

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

        $people = $this->searchModel->searchPeopleByRefuge($refuge_id);
        $refuge_data = $this->searchModel->getRefugeInfo($refuge_id);
        $refuge_name = $refuge_data['refuge_name'] ?? 'Refugio';

        if (empty($people)) {
            header('HTTP/1.1 404 Not Found');
            echo 'No hay personas registradas en este refugio.';
            exit;
        }

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema de Refugios');
        $pdf->SetAuthor('Sistema de Refugios');
        $pdf->SetTitle('Lista de Albergados - ' . $refuge_name);
        $pdf->SetSubject('Lista de personas albergadas');
        $pdf->SetHeaderData('', 0, 'Sistema de Refugios', 'Lista de Albergados - ' . $refuge_name);
        $pdf->setHeaderFont(['helvetica', '', 10]);
        $pdf->setFooterFont(['helvetica', '', 8]);
        $pdf->SetMargins(10, 20, 10);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        $html = '
        <h1 style="text-align:center;">Lista de Albergados - ' . htmlspecialchars($refuge_name) . '</h1>
        <table border="1" cellpadding="5" style="font-size:10px;">
            <thead>
                <tr style="background-color:#e5e7eb;">
                    <th>Nombre</th>
                    <th>Estatus</th>
                    <th>Fecha de Ingreso</th>
                    <th>Hora de Ingreso</th>
                    <th>Refugio</th>
                    <th>Ubicación</th>
                </tr>
            </thead>
            <tbody>';
        foreach ($people as $person) {
            $html .= '
                <tr>
                    <td>' . htmlspecialchars($person['name']) . '</td>
                    <td>' . htmlspecialchars($person['status']) . '</td>
                    <td>' . htmlspecialchars($person['entry_date']) . '</td>
                    <td>' . htmlspecialchars($person['entry_time']) . '</td>
                    <td>' . htmlspecialchars($person['refuge_name']) . '</td>
                    <td>' . htmlspecialchars($person['location']) . '</td>
                </tr>';
        }
        $html .= '
            </tbody>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output(str_replace(' ', '_', $refuge_name) . '_lista_albergados.pdf', 'D');
        exit;
    }

    public function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

// Manejo de acciones
if (isset($_GET['action'])) {
    $controller = new SearchController();
    switch ($_GET['action']) {
        case 'suggest':
            $controller->publicSuggest();
            break;
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