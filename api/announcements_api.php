<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';
$pdo = getPDO();

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT a.*, u.name as creator_name FROM announcements a LEFT JOIN users u ON a.created_by = u.id ORDER BY a.created_at DESC");
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
            break;
        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            $title = $data['title'] ?? '';
            $content = $data['content'] ?? '';
            $type = $data['type'] ?? 'info';
            $userId = 1; // Default admin until proper session integration

            if ($id) {
                $stmt = $pdo->prepare("UPDATE announcements SET title=?, content=?, type=? WHERE id=?");
                $stmt->execute([$title, $content, $type, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO announcements (title, content, type, created_by) VALUES (?, ?, ?, ?)");
                $stmt->execute([$title, $content, $type, $userId]);
            }
            echo json_encode(['success' => true]);
            break;
        case 'delete':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
