<?php
// Seeder for Announcements and Pages
require_once __DIR__ . '/../config.php';
$pdo = getPDO();

echo "Seeding Announcements & Pages...\n";

// Announcements Table
$sqlAnn = "CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'danger') DEFAULT 'info',
    created_by INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$pdo->exec($sqlAnn);

// Pages Table
$sqlPages = "CREATE TABLE IF NOT EXISTS pages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    is_visible TINYINT(1) DEFAULT 1,
    last_edited_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (last_edited_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$pdo->exec($sqlPages);

// Seed Announcements
$countAnn = $pdo->query("SELECT COUNT(*) FROM announcements")->fetchColumn();
if ($countAnn == 0) {
    $userId = $pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();
    if ($userId) {
        $pdo->prepare("INSERT INTO announcements (title, content, type, created_by) VALUES (?, ?, ?, ?)")
            ->execute(['System Update', 'We are upgrading the servers tonight at 10 PM. Please save your work.', 'warning', $userId]);
        $pdo->prepare("INSERT INTO announcements (title, content, type, created_by) VALUES (?, ?, ?, ?)")
            ->execute(['Welcome to LeasePro', 'Final version of the admin panel is now live.', 'success', $userId]);
    }
    echo "Inserted default announcements.\n";
}

// Seed Pages
$countPages = $pdo->query("SELECT COUNT(*) FROM pages")->fetchColumn();
if ($countPages == 0) {
    $userId = $pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();
    $pages = [
        ['Dashboard', 'dashboard.php', 1, $userId],
        ['Lessee Management', 'lessees.php', 1, $userId],
        ['Users', 'users.php', 1, $userId],
        ['Roles & Permissions', 'roles.php', 1, $userId],
        ['Active Sessions', 'sessions.php', 1, $userId],
        ['Announcements', 'announcements.php', 1, $userId],
        ['Audit Logs', 'logs.php', 1, $userId],
        ['App Settings', 'settings.php', 1, $userId]
    ];
    $stmt = $pdo->prepare("INSERT INTO pages (page_name, slug, is_visible, last_edited_by) VALUES (?, ?, ?, ?)");
    foreach ($pages as $p) $stmt->execute($p);
    echo "Inserted " . count($pages) . " pages to registry.\n";
}
