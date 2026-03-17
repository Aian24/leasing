<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';
$pdo = getPDO();

try {
    switch ($action) {
        case 'list':
            $type = $_GET['type'] ?? '';
            $sql = "SELECT p.*, u.name as editor_name FROM pages p LEFT JOIN users u ON p.last_edited_by = u.id";
            if ($type) $sql .= " WHERE p.type = " . $pdo->quote($type);
            $sql .= " ORDER BY p.type ASC, p.page_name ASC";
            
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();
            
            $live = 0; $hidden = 0;
            foreach($rows as $r) { if($r['is_visible']) $live++; else $hidden++; }

            echo json_encode([
                'success' => true, 
                'data' => $rows,
                'stats' => ['live' => $live, 'hidden' => $hidden, 'total' => count($rows)]
            ]);
            break;

        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            $name = $data['page_name'] ?? '';
            $slug = $data['slug'] ?? '';
            $content = $data['content'] ?? '';
            $type = $data['type'] ?? 'frontend';
            $isVisible = isset($data['is_visible']) ? (int)$data['is_visible'] : 0;
            $editorId = 1; // Default to admin

            if ($id) {
                $stmt = $pdo->prepare("UPDATE pages SET page_name = ?, slug = ?, content = ?, type = ?, is_visible = ?, last_edited_by = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $content, $type, $isVisible, $editorId, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO pages (page_name, slug, content, type, is_visible, last_edited_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $content, $type, $isVisible, $editorId]);
            }
            echo json_encode(['success' => true, 'message' => 'Page saved successfully']);
            break;

        case 'toggle':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $stmt = $pdo->prepare("UPDATE pages SET is_visible = 1 - is_visible WHERE id = ?");
            $stmt->execute([$id]);
            
            // Fetch new status to return it
            $stmt = $pdo->prepare("SELECT is_visible FROM pages WHERE id = ?");
            $stmt->execute([$id]);
            $newStatus = $stmt->fetchColumn();
            
            echo json_encode(['success' => true, 'new_status' => (int)$newStatus]);
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
