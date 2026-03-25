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

            // 4. Pending Contracts Count
            $pendingContracts = $pdo->query("SELECT COUNT(*) FROM contract_submissions WHERE status = 'Pending'")->fetchColumn();

            // 5. Audit Logs Count
            $logsCount = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();

            // 5. System Health
            $diskTotal = disk_total_space(__DIR__);
            $diskFree = disk_free_space(__DIR__);
            $diskUsage = $diskTotal > 0 ? round((($diskTotal - $diskFree) / $diskTotal) * 100) : 0;
            
            $cpuUsage = 0;
            $memUsage = 0;
            
            $wmicCpu = shell_exec('wmic cpu get loadpercentage /Value 2>nul');
            if (preg_match('/LoadPercentage=(\d+)/i', $wmicCpu, $matches)) {
                $cpuUsage = (int)$matches[1];
            }
            
            $wmicMem = shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /Value 2>nul');
            if (preg_match('/FreePhysicalMemory=(\d+)/i', $wmicMem, $free) && preg_match('/TotalVisibleMemorySize=(\d+)/i', $wmicMem, $total)) {
                $totalMem = (int)$total[1];
                $freeMem = (int)$free[1];
                $memUsage = $totalMem > 0 ? round((($totalMem - $freeMem) / $totalMem) * 100) : 0;
            }

            // 6. Monthly Activity (Past 12 Months)
            $monthsData = [];
            $labels = [];
            for ($i = 11; $i >= 0; $i--) {
                $time = strtotime("-$i months");
                $monthStr = date('Y-m', $time);
                $monthsData[$monthStr] = 0;
                $labels[] = date('M', $time);
            }

            // Sub query to prevent incomplete month dropping
            $limitDate = date('Y-m-01', strtotime('-11 months'));
            $activityStmt = $pdo->prepare("
                SELECT DATE_FORMAT(created_at, '%Y-%m') as mth, COUNT(*) as cnt 
                FROM audit_logs 
                WHERE created_at >= ?
                GROUP BY mth
            ");
            $activityStmt->execute([$limitDate]);
            while ($row = $activityStmt->fetch()) {
                if (isset($monthsData[$row['mth']])) {
                    $monthsData[$row['mth']] = (int)$row['cnt'];
                }
            }

            $maxActivity = max($monthsData) ?: 1;
            $activityHeights = array_values(array_map(function($val) use ($maxActivity) {
                // Minimum height of 2% just so a bar is visible for 0, scaled up to 100%
                return max(2, round(($val / $maxActivity) * 100)); 
            }, $monthsData));
            // 7. Recent Users
            $recentUsers = $pdo->query("
                SELECT name, role, avatar, last_login 
                FROM users 
                WHERE last_login IS NOT NULL 
                ORDER BY last_login DESC 
                LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            // 8. Recent Activity
            $recentActivity = $pdo->query("
                SELECT action, detail, username, created_at, level 
                FROM audit_logs 
                ORDER BY created_at DESC 
                LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

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
                'contracts' => [
                    'pending' => (int)$pendingContracts
                ],
                'logs' => ['total' => (int)$logsCount],
                'pages' => ['total' => 8],
                'health' => [
                    'disk' => $diskUsage,
                    'memory' => $memUsage,
                    'cpu' => $cpuUsage,
                ],
                'chart' => [
                    'labels' => $labels,
                    'heights' => $activityHeights
                ],
                'recent_users' => $recentUsers,
                'activity' => $recentActivity
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
