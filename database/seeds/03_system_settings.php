<?php
// Seeder for System Settings and Audit Logs
require_once __DIR__ . '/../config.php';
$pdo = getPDO();

echo "Seeding System Settings & Initial Logs...\n";

// Settings Table
$sqlSettings = "CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$pdo->exec($sqlSettings);

// Ensure Audit Logs table is ready
$sqlLogs = "CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    username VARCHAR(80) NOT NULL,
    action VARCHAR(50) NOT NULL,
    detail TEXT,
    ip_address VARCHAR(45),
    level ENUM('info','success','warning','danger') DEFAULT 'info',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$pdo->exec($sqlLogs);

// Seed Settings
$countSettings = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
if ($countSettings == 0) {
    $defaults = [
        ['app_name', 'LeasePro', 'The title of the application'],
        ['app_tagline', 'Modern Daily Tenant Collections', 'Slogan shown on login and dashboard'],
        ['allow_registration', 'true', 'Enable/Disable new user signups'],
        ['maintenance_mode', 'false', 'Enable/Disable system-wide maintenance mode'],
        ['session_timeout', '24', 'Session duration in hours'],
        ['currency', 'PHP', 'Primary currency symbol/code'],
        ['theme_color', '#3b82f6', 'Primary brand color for the UI']
    ];
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
    foreach ($defaults as $row) {
        $stmt->execute($row);
    }
    echo "Inserted " . count($defaults) . " system settings.\n";
}

// Seed Initial Audit Logs
$countLogs = $pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
if ($countLogs == 0) {
    $user = $pdo->query("SELECT id, username FROM users LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $logs = [
            [$user['id'], $user['username'], 'Migration', 'Database schema refined and seeded', '127.0.0.1', 'success'],
            [$user['id'], $user['username'], 'Security', 'Role matrix initialized', '127.0.0.1', 'info'],
            [$user['id'], $user['username'], 'Config', 'System settings generated', '127.0.0.1', 'info']
        ];
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, username, action, detail, ip_address, level) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($logs as $l) $stmt->execute($l);
    }
    echo "Inserted initial audit logs.\n";
}
