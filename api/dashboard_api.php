<?php
// =============================================================
//  Admin API — Dashboard Statistics
// =============================================================
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';

try {
    $pdo = getPDO();

    switch ($action) {
        case 'stats':
            // 1. User Stats
            $userStats = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'Active' THEN 1 END) as active
                FROM users
            ")->fetch(PDO::FETCH_ASSOC);

            // 2. Lessee Stats
            $lesseeStats = $pdo->query("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN lease_period_end >= CURDATE() THEN 1 END) as active_contracts
                FROM lessees
            ")->fetch(PDO::FETCH_ASSOC);

            // 3. Online Sessions
            $sessionStats = $pdo->query("
                SELECT COUNT(*) as online FROM user_sessions WHERE is_active = 1 AND expires_at > NOW()
            ")->fetch(PDO::FETCH_ASSOC);

            // 4. Audit Logs Count
            $logsCount = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();

            echo json_encode([
                'success' => true,
                'users' => [
                    'total' => (int)$userStats['total'],
                    'active' => (int)$userStats['active']
                ],
                'lessees' => [
                    'total' => (int)$lesseeStats['total'],
                    'active' => (int)$lesseeStats['active_contracts']
                ],
                'sessions' => [
                    'online' => (int)$sessionStats['online']
                ],
                'logs' => ['total' => (int)$logsCount],
                'pages' => ['total' => 8]
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
