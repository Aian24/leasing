<?php
// Seeder for Roles and Permissions
require_once __DIR__ . '/../config.php';
$pdo = getPDO();

echo "Seeding Roles & Permissions...\n";

$sql = "CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL UNIQUE,
    admin_access TINYINT(1) DEFAULT 1,
    manager_access TINYINT(1) DEFAULT 0,
    staff_access TINYINT(1) DEFAULT 0,
    viewer_access TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$pdo->exec($sql);

$count = $pdo->query("SELECT COUNT(*) FROM permissions")->fetchColumn();
if ($count == 0) {
    $defaults = [
        ['Manage Users', 1, 0, 0, 0],
        ['Manage Contracts', 1, 1, 1, 0],
        ['System Config', 1, 0, 0, 0],
        ['View Audit Logs', 1, 1, 0, 0],
        ['Manage Lessees', 1, 1, 1, 0],
        ['Generate Reports', 1, 1, 0, 1],
        ['Dashboard Overview', 1, 1, 1, 1],
        ['App Settings', 1, 0, 0, 0]
    ];
    $stmt = $pdo->prepare("INSERT INTO permissions (permission_name, admin_access, manager_access, staff_access, viewer_access) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaults as $row) {
        $stmt->execute($row);
    }
    echo "Inserted " . count($defaults) . " permissions.\n";
} else {
    echo "Permissions table already seeded.\n";
}
