<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';
$pdo = getPDO();

try {
    session_start();
    
    switch ($action) {
        case 'list':
            $publicOnly = isset($_GET['public']) && $_GET['public'] === 'true';
            $where = $publicOnly ? "WHERE is_active = 1 " : "";
            $stmt = $pdo->query("SELECT a.*, u.name as creator_name FROM announcements a LEFT JOIN users u ON a.created_by = u.id $where ORDER BY a.created_at DESC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;
        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            $type = $data['type'] ?? 'info';
            $isActive = $data['is_active'] ?? 1;
            $userId = $_SESSION['user_id'] ?? 1;

            if ($id) {
                $stmt = $pdo->prepare("UPDATE announcements SET title=?, content=?, type=?, is_active=? WHERE id=?");
                $stmt->execute([$title, $content, $type, $isActive, $id]);
                logAction('Updated Announcement', "Edited broadcast: '{$title}'", 'info');
            } else {
                $stmt = $pdo->prepare("INSERT INTO announcements (title, content, type, is_active, created_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $content, $type, $isActive, $userId]);
                logAction('Created Announcement', "Published new broadcast: '{$title}'", 'success');
            }
            echo json_encode(['success' => true]);
            break;
        case 'delete':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Get title for logging
            $infoStmt = $pdo->prepare("SELECT title FROM announcements WHERE id = ?");
            $infoStmt->execute([$data['id']]);
            $title = $infoStmt->fetchColumn();

            $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
            $stmt->execute([$data['id']]);
            logAction('Deleted Announcement', "Removed broadcast: '{$title}'", 'danger');
            echo json_encode(['success' => true]);
            break;
        case 'toggle_status':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE announcements SET is_active = ? WHERE id = ?");
            $stmt->execute([$data['is_active'], $data['id']]);
            
            $statusStr = $data['is_active'] ? 'Enabled' : 'Disabled';
            logAction('Toggled Announcement', "{$statusStr} broadcast visibility for ID: {$data['id']}", 'warning');
            
            echo json_encode(['success' => true]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
