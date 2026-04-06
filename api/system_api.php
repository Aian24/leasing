<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';


$action = $_GET['action'] ?? '';
$pdo = getPDO();

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    switch ($action) {
        case 'info':
            $dbVersion = $pdo->query("SELECT VERSION()")->fetchColumn();
            echo json_encode([
                'success' => true,
                'data' => [
                    'php_version' => PHP_VERSION,
                    'server_software' => $_SERVER['SERVER_SOFTWARE'],
                    'db_version' => $dbVersion,
                    'os' => PHP_OS,
                    'max_upload' => ini_get('upload_max_filesize'),
                    'max_execution' => ini_get('max_execution_time') . 's',
                    'memory_limit' => ini_get('memory_limit'),
                    'server_time' => date('Y-m-d H:i:s'),
                    'uptime' => (function() {
                        $out = shell_exec('wmic os get lastbootuptime 2>nul');
                        if (preg_match("/(\d{14})/", $out, $m)) {
                            $boot = date_create_from_format('YmdHis', $m[1]);
                            $now = new DateTime();
                            $diff = $now->diff($boot);
                            return $diff->format('%a d, %h h, %i m');
                        }
                        return 'Unknown';
                    })()
                ]
            ]);
            break;

        case 'logs':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $limit = max(1, min(100, (int)($_GET['limit'] ?? 25)));
            $offset = ($page - 1) * $limit;
            $search = $_GET['search'] ?? '';

            $where = "1=1";
            $params = [];
            if ($search) {
                $where .= " AND (username LIKE ? OR action LIKE ? OR detail LIKE ? OR ip_address LIKE ?)";
                $params = ["%$search%", "%$search%", "%$search%", "%$search%"];
            }

            // Get total count
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM audit_logs WHERE $where");
            $stmtCount->execute($params);
            $total = $stmtCount->fetchColumn();

            // Get data
            $stmt = $pdo->prepare("SELECT * FROM audit_logs WHERE $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => (int)$total,
                    'pages' => ceil($total / $limit)
                ]
            ]);
            break;

        case 'settings_list':
            $stmt = $pdo->query("SELECT * FROM settings ORDER BY setting_key ASC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;

        case 'settings_update':
            $data = json_decode(file_get_contents('php://input'), true);
            foreach ($data as $key => $val) {
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([$val, $key]);
                logAction('Updated Settings', "Changed {$key} to {$val}", 'info');
            }
            echo json_encode(['success' => true, 'message' => 'Settings updated']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
