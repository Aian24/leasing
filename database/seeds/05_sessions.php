<?php
require_once __DIR__ . '/../config.php';
$pdo = getPDO();

echo "Seeding user_sessions table...\n";

// Clear existing
$pdo->exec("TRUNCATE TABLE user_sessions");

$sessions = [
    [
        'user_id' => 1, // Admin (msantos)
        'session_token' => bin2hex(random_bytes(32)),
        'ip_address' => '192.168.1.10',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
        'browser' => 'Chrome',
        'platform' => 'Windows 10',
        'login_time' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
        'last_activity' => date('Y-m-d H:i:s', strtotime('-2 minutes')),
        'expires_at' => date('Y-m-d H:i:s', strtotime('+8 hours')),
        'is_active' => 1
    ],
    [
        'user_id' => 2, // Juan
        'session_token' => bin2hex(random_bytes(32)),
        'ip_address' => '124.106.155.88',
        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3 Mobile/15E148 Safari/604.1',
        'browser' => 'Mobile Safari',
        'platform' => 'iOS 17.3',
        'login_time' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'last_activity' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
        'expires_at' => date('Y-m-d H:i:s', strtotime('+6 hours')),
        'is_active' => 1
    ],
    [
        'user_id' => 3, // Ana
        'session_token' => bin2hex(random_bytes(32)),
        'ip_address' => '49.145.22.10',
        'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36',
        'browser' => 'Chrome',
        'platform' => 'macOS',
        'login_time' => date('Y-m-d H:i:s', strtotime('-6 hours')),
        'last_activity' => date('Y-m-d H:i:s', strtotime('-5 hours')),
        'expires_at' => date('Y-m-d H:i:s', strtotime('+2 hours')),
        'is_active' => 1
    ]
];

$sql = "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, browser, platform, login_time, last_activity, expires_at, is_active) 
        VALUES (:user_id, :session_token, :ip_address, :user_agent, :browser, :platform, :login_time, :last_activity, :expires_at, :is_active)";
$stmt = $pdo->prepare($sql);

foreach ($sessions as $s) {
    try {
        $stmt->execute($s);
        echo "Created session for user_id: {$s['user_id']}\n";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

echo "Done seeding sessions.\n";
