<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';
$pdo = getPDO();

try {
    switch ($action) {
        case 'search':
            $query = $_GET['q'] ?? '';
            if (strlen($query) < 2) {
                echo json_encode(['success' => true, 'data' => []]);
                break;
            }

            // Search in Pages
            $stmt = $pdo->prepare("SELECT page_name as title, slug, 'Page' as type FROM pages WHERE page_name LIKE ? AND is_visible = 1 LIMIT 5");
            $stmt->execute(['%' . $query . '%']);
            $pages = $stmt->fetchAll();

            // Search in Lessees
            $stmt = $pdo->prepare("SELECT company_name as title, id, 'Lessee' as type FROM lessees WHERE company_name LIKE ? OR trade_name LIKE ? LIMIT 5");
            $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
            $lessees = $stmt->fetchAll();

            echo json_encode(['success' => true, 'data' => array_merge($pages, $lessees)]);
            break;

        case 'get_notifications':
            $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10");
            $data = $stmt->fetchAll();
            $unread = array_filter($data, function($n) { return !$n['is_read']; });
            echo json_encode(['success' => true, 'data' => $data, 'unread_count' => count($unread)]);
            break;

        case 'mark_as_read':
            $pdo->exec("UPDATE notifications SET is_read = 1");
            echo json_encode(['success' => true]);
            break;

        case 'get_profile':
            // Mocking current user (id 1) for now
            $stmt = $pdo->prepare("SELECT name, username, email, role, phone, position, department, avatar FROM users WHERE id = 1");
            $stmt->execute();
            echo json_encode(['success' => true, 'data' => $stmt->fetch()]);
            break;

        case 'update_profile':
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = 1;

            // 1. Basic Info Update
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
            $stmt->execute([$data['name'], $data['email'], $data['phone'], $userId]);

            // 2. Password Change (if provided)
            if (!empty($data['new_password'])) {
                // Verify current password
                $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
                if (password_verify($data['curr_password'], $user['password_hash'])) {
                    $newHash = password_hash($data['new_password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$newHash, $userId]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Current password incorrect']);
                    exit;
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Profile updated']);
            break;

        case 'upload_avatar':
            if (!isset($_FILES['avatar'])) {
                echo json_encode(['success' => false, 'message' => 'No file uploaded']);
                exit;
            }
            
            $file = $_FILES['avatar'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = 'avatar_' . 1 . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/avatars/';
            
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                $avatarPath = 'uploads/avatars/' . $newName;
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = 1");
                $stmt->execute([$avatarPath]);
                echo json_encode(['success' => true, 'avatar' => $avatarPath]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload failed']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
