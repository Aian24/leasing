<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';
$pdo = getPDO();

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
                    'uptime' => 'Calculating...' // Mocking for now
                ]
            ]);
            break;

        case 'logs':
            $stmt = $pdo->query("SELECT * FROM audit_logs ORDER BY created_at DESC LIMIT 100");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
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
            }
            echo json_encode(['success' => true, 'message' => 'Settings updated']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
