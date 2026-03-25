<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$pdo = getPDO();
$userId = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'search':
            $query = $_GET['q'] ?? '';
            if (strlen($query) < 2) {
                echo json_encode(['success' => true, 'data' => []]);
                break;
            }

            // Search only in Frontend Pages as requested
            $stmt = $pdo->prepare("SELECT page_name as title, slug, 'Page' as type FROM pages WHERE page_name LIKE ? AND type = 'frontend' AND is_visible = 1 LIMIT 5");
            $searchTerm = '%' . $query . '%';
            $stmt->execute([$searchTerm]);
            $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $pages]);
            break;

        case 'get_lessee':
            $id = $_GET['id'] ?? 0;
            $stmt = $pdo->prepare("SELECT * FROM lessees WHERE id = ?");
            $stmt->execute([$id]);
            $lessee = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => !!$lessee, 'data' => $lessee]);
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
            $stmt = $pdo->prepare("SELECT name, username, email, role, phone, position, department, avatar FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            echo json_encode(['success' => true, 'data' => $stmt->fetch()]);
            break;

        case 'update_profile':
            $data = json_decode(file_get_contents('php://input'), true);

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
                    logAction('Changed Password', 'User changed their own password', 'warning');
                } else {
                    echo json_encode(['success' => false, 'message' => 'Current password incorrect']);
                    exit;
                }
            }
            
            logAction('Updated Profile', 'User updated their personal information', 'info');
            echo json_encode(['success' => true, 'message' => 'Profile updated']);
            break;

        case 'upload_avatar':
            if (!isset($_FILES['avatar'])) {
                echo json_encode(['success' => false, 'message' => 'No file uploaded']);
                exit;
            }
            
            $file = $_FILES['avatar'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/avatars/';
            
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
                $avatarPath = 'uploads/avatars/' . $newName;
                $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                $stmt->execute([$avatarPath, $userId]);
                logAction('Updated Avatar', 'User uploaded a new profile picture', 'info');
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
