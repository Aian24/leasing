<?php
/**
 * Main Database Seeder / Migration Runner
 * Run this from the root: php database/run_seeds.php
 */

require_once __DIR__ . '/config.php';

echo "🚀 Starting database seeding process...\n";
echo "----------------------------------------\n";

$seeds = [
    '00_users_lessees.php',
    '01_roles_permissions.php',
    '02_announcements_pages.php',
    '03_system_settings.php',
    '04_notifications.php'
];

foreach ($seeds as $file) {
    $path = __DIR__ . '/seeds/' . $file;
    if (file_exists($path)) {
        include $path;
    } else {
        echo "⚠️ Warning: Seed file $file not found!\n";
    }
}

echo "----------------------------------------\n";
echo "✅ Seeding complete!\n";
