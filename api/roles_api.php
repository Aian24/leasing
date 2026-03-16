<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? '';
$pdo = getPDO();

try {
    switch ($action) {
        case 'list':
            $stmt = $pdo->query("SELECT * FROM permissions ORDER BY id ASC");
            $rows = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        case 'toggle':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $role = $data['role'] ?? ''; // admin, manager, staff, viewer
            $value = ($data['value'] === true || $data['value'] == 1) ? 1 : 0;

            if (!$id || !in_array($role, ['admin', 'manager', 'staff', 'viewer'])) {
                throw new Exception("Invalid parameters");
            }

            $column = "{$role}_access";
            $stmt = $pdo->prepare("UPDATE permissions SET $column = ? WHERE id = ?");
            $stmt->execute([$value, $id]);

            echo json_encode(['success' => true, 'message' => 'Permission updated']);
            break;

        case 'add':
            $data = json_decode(file_get_contents('php://input'), true);
            $name = trim($data['name'] ?? '');
            $admin = ($data['admin'] ?? false) ? 1 : 0;
            $manager = ($data['manager'] ?? false) ? 1 : 0;
            $staff = ($data['staff'] ?? false) ? 1 : 0;
            $viewer = ($data['viewer'] ?? false) ? 1 : 0;

            if (!$name) throw new Exception("Name required");

            $stmt = $pdo->prepare("INSERT INTO permissions (permission_name, admin_access, manager_access, staff_access, viewer_access) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $admin, $manager, $staff, $viewer]);
            echo json_encode(['success' => true, 'message' => 'Permission created']);
            break;

        case 'update':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $name = trim($data['name'] ?? '');
            $admin = ($data['admin'] ?? false) ? 1 : 0;
            $manager = ($data['manager'] ?? false) ? 1 : 0;
            $staff = ($data['staff'] ?? false) ? 1 : 0;
            $viewer = ($data['viewer'] ?? false) ? 1 : 0;

            if (!$id || !$name) throw new Exception("Missing parameters");

            $stmt = $pdo->prepare("UPDATE permissions SET permission_name = ?, admin_access = ?, manager_access = ?, staff_access = ?, viewer_access = ? WHERE id = ?");
            $stmt->execute([$name, $admin, $manager, $staff, $viewer, $id]);
            echo json_encode(['success' => true, 'message' => 'Permission updated successfully']);
            break;

        case 'delete':
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? 0;
            $stmt = $pdo->prepare("DELETE FROM permissions WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Permission deleted']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
