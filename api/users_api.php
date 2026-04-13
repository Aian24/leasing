<?php
// =============================================================
//  Admin API — User Management Actions
// =============================================================
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';

session_start();
if (!isset($_SESSION['user_id']) || !canAccess('users.php')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access to user management']);
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
            $name = $body['name'] ?? '';
            $username = $body['username'] ?? '';
            $password = $body['password'] ?? '';
            $email = $body['email'] ?? '';
            $role = (!empty($body['role'])) ? $body['role'] : 'Staff';
            $status = $body['status'] ?? 'Active';

            if (empty($name) || empty($username) || empty($password) || empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Missing required fields (Name, Username, Password, Email)']);
                break;
            }

            try {
                $passHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password_hash, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $username, $email, $passHash, $role, $status]);
                
                echo json_encode(['success' => true, 'message' => 'User created successfully']);
                logAction('Created User', "Created new user account '{$username}' with role '{$role}'", 'success');
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    if (strpos($e->getMessage(), 'uq_username') !== false) {
                        echo json_encode(['success' => false, 'message' => "The username '{$username}' is already taken."]);
                    } else if (strpos($e->getMessage(), 'uq_email') !== false) {
                        echo json_encode(['success' => false, 'message' => "The email '{$email}' is already registered."]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Username or Email already exists.']);
                    }
                } else {
                    throw $e; // Re-throw to be caught by global handler
                }
            }
            break;

        case 'update':
            $body = json_decode(file_get_contents('php://input'), true);
            $id = (int)($body['id'] ?? 0);
            $name = $body['name'] ?? '';
            $username = $body['username'] ?? '';
            $email = $body['email'] ?? '';
            $role = (!empty($body['role'])) ? $body['role'] : 'Staff';
            $status = $body['status'] ?? 'Active';

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                break;
            }

            try {
                $sql = "UPDATE users SET name=?, username=?, email=?, role=?, status=?";
                $params = [$name, $username, $email, $role, $status];

                if (!empty($body['password'])) {
                    $sql .= ", password_hash=?";
                    $params[] = password_hash($body['password'], PASSWORD_DEFAULT);
                }

                $sql .= " WHERE id=?";
                $params[] = $id;

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                logAction('Updated User', "Updated details for user '{$username}'", 'info');
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    echo json_encode(['success' => false, 'message' => 'Username or Email already exists on another account.']);
                } else {
                    throw $e;
                }
            }
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
