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
            $type = $data['type'] ?? 'frontend';
            $isVisible = isset($data['is_visible']) ? (int)$data['is_visible'] : 0;
            $editorId = 1; // Default to admin

            if ($id) {
                $stmt = $pdo->prepare("UPDATE pages SET page_name = ?, slug = ?, type = ?, is_visible = ?, last_edited_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$name, $slug, $type, $isVisible, $editorId, $id]);
            } else {
                $content = '';
                $stmt = $pdo->prepare("INSERT INTO pages (page_name, slug, content, type, is_visible, last_edited_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $content, $type, $isVisible, $editorId]);
            }
            echo json_encode(['success' => true, 'message' => 'Page details saved successfully']);
            break;

        case 'save_content':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            $content = $data['content'] ?? '';
            $editorId = 1;
            
            if ($id) {
                // Clear any preview sessions after publishing
                if (session_status() === PHP_SESSION_NONE) session_start();
                unset($_SESSION['builder_preview'][$id]);

                $stmt = $pdo->prepare("UPDATE pages SET content = ?, last_edited_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$content, $editorId, $id]);
                echo json_encode(['success' => true, 'message' => 'Page content published successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid page ID']);
            }
            break;

        case 'save_preview':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            $content = $data['content'] ?? '';
            
            if ($id) {
                if (session_status() === PHP_SESSION_NONE) session_start();
                $_SESSION['builder_preview'][$id] = $content;
                echo json_encode(['success' => true, 'message' => 'Preview generated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid page ID']);
            }
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
