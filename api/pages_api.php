<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';
$pdo = getPDO();

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT p.*, u.name as editor_name FROM pages p LEFT JOIN users u ON p.last_edited_by = u.id ORDER BY p.page_name ASC");
            $rows = $stmt->fetchAll();
            
            $live = 0; $hidden = 0;
            foreach($rows as $r) { if($r['is_visible']) $live++; else $hidden++; }

            echo json_encode([
                'success' => true, 
                'data' => $rows,
                'stats' => ['live' => $live, 'hidden' => $hidden, 'total' => count($rows)]
            ]);
            break;
        case 'toggle':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("UPDATE pages SET is_visible = !is_visible WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;
        case 'delete':
            $data = json_decode(file_get_contents('php://input'), true);
            $stmt = $pdo->prepare("DELETE FROM pages WHERE id = ?");
            $stmt->execute([$data['id']]);
            echo json_encode(['success' => true]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
