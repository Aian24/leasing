<?php
require_once __DIR__ . '/../config.php';
$pdo = getPDO();

echo "Seeding Notifications table...\n";

$sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- NULL for global notifications
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$pdo->exec($sql);

$count = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
if ($count == 0) {
    $userId = $pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();
    $data = [
        [$userId, 'New Lessee Registered', 'ABC Corp has been successfully added to the system.', 'success', 0],
        [$userId, 'Contract Expiring', 'Lease for Space 2B010 expires in 30 days.', 'warning', 0],
        [null, 'System Maintenance', 'Panel will be offline for 30 minutes tonight.', 'info', 0]
    ];
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, is_read) VALUES (?, ?, ?, ?, ?)");
    foreach ($data as $row) {
        $stmt->execute($row);
    }
    echo "Inserted " . count($data) . " sample notifications.\n";
}
