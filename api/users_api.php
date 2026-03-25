<?php
// =============================================================
//  Admin API — User Management Actions
// =============================================================
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $pdo = getPDO();

    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT id, name, username, email, role, status, last_login as lastLogin FROM users ORDER BY name ASC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $users]);
            break;

        case 'create':
            $body = json_decode(file_get_contents('php://input'), true);
            if (empty($body['name']) || empty($body['username']) || empty($body['password'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields']);
                break;
            }

            $passHash = password_hash($body['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $body['name'],
                $body['username'],
                $body['email'] ?? '',
                $passHash,
                $body['role'] ?? 'Staff',
                $body['status'] ?? 'Active'
            ]);
            echo json_encode(['success' => true, 'message' => 'User created successfully']);
            logAction('Created User', "Created new user account '{$body['username']}' with role '{$body['role']}'", 'success');
            break;

        case 'update':
            $body = json_decode(file_get_contents('php://input'), true);
            $id = (int)($body['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                break;
            }

            $sql = "UPDATE users SET name=?, username=?, email=?, role=?, status=?";
            $params = [$body['name'], $body['username'], $body['email'], $body['role'], $body['status']];

            if (!empty($body['password'])) {
                $sql .= ", password_hash=?";
                $params[] = password_hash($body['password'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id=?";
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            logAction('Updated User', "Updated details for user '{$body['username']}'", 'info');
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            break;

        case 'delete':
            $body = json_decode(file_get_contents('php://input'), true);
            $id = (int)($body['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                break;
            }


            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $username = $stmt->fetchColumn();

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            logAction('Deleted User', "Deleted user account '{$username}'", 'danger');
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
