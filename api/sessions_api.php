<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';
$pdo = getPDO();

try {
    switch ($action) {
        case 'list':
            $sql = "SELECT s.*, u.name, u.username, u.email, u.avatar, u.role 
                    FROM user_sessions s
                    JOIN users u ON s.user_id = u.id
                    WHERE s.is_active = 1 AND s.expires_at > NOW()
                    ORDER BY s.last_activity DESC";
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            
            // Statistics
            $activeCount = count($rows);
            $todayLogins = $pdo->query("SELECT COUNT(*) FROM user_sessions WHERE login_time >= CURDATE()")->fetchColumn();
            
            echo json_encode([
                'success' => true, 
                'data' => $rows,
                'stats' => [
                    'active' => $activeCount,
                    'today' => $todayLogins,
                    'suspicious' => 0 // Placeholder
                ]
            ]);
            break;

        case 'terminate':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            if (!$id) throw new Exception("Session ID required");

            $stmt = $pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Session terminated']);
            break;

        case 'terminate_all':
            $pdo->exec("UPDATE user_sessions SET is_active = 0 WHERE is_active = 1");
            echo json_encode(['success' => true, 'message' => 'All active sessions terminated']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
