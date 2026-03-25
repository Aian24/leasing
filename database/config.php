<?php
// =============================================================
//  LEASE MANAGEMENT SYSTEM — Database Configuration
//  File   : database/config.php
//  Usage  : require_once __DIR__ . '/../database/config.php';
// =============================================================

// ── Connection Settings ───────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'lease_db');
define('DB_USER',    'root');       // Change for production
define('DB_PASS',    '');           // Change for production
define('DB_CHARSET', 'utf8mb4');

// ── PDO DSN ───────────────────────────────────────────────────
define('DB_DSN', 'mysql:host=' . DB_HOST
               . ';port='     . DB_PORT
               . ';dbname='   . DB_NAME
               . ';charset='  . DB_CHARSET);

// ── PDO Options ───────────────────────────────────────────────
$pdo_options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Return assoc arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                    // Use real prepared statements
];

// ── Get PDO Connection (singleton) ───────────────────────────
function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $GLOBALS['pdo_options']);
        } catch (PDOException $e) {
            // In production, log the error and show a generic message
            error_log('[DB ERROR] ' . $e->getMessage());
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please contact the system administrator.'
            ]));
        }
    }
    return $pdo;
}

// ── MySQLi Connection (alternative / legacy support) ─────────
function getMySQLi(): mysqli {
    static $mysqli = null;
    if ($mysqli === null) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int) DB_PORT);
        if ($mysqli->connect_error) {
            error_log('[MySQLi ERROR] ' . $mysqli->connect_error);
            http_response_code(500);
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed. Please contact the system administrator.'
            ]));
        }
        $mysqli->set_charset(DB_CHARSET);
    }
    return $mysqli;
}

/**
 * Fetch a setting value from the database
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getSetting($key, $default = null) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}


// ── Quick alias ───────────────────────────────────────────────
// Use $pdo = getPDO(); anywhere after including this file

/**
 * Log an administrative action to the audit_logs table
 * @param string $action Example: 'Login', 'Update Settings'
 * @param string $detail Specific details of what was done
 * @param string $level 'info', 'warning', 'danger', 'success'
 */
function logAction($action, $detail, $level = 'info') {
    try {
        $pdo = getPDO();
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? $_SESSION['name'] ?? 'System';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, username, action, detail, ip_address, level) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $username, $action, $detail, $ip, $level]);
    } catch (Exception $e) {
        // Silently fail logging if DB is down rather than breaking the whole app
        error_log('[AUDIT LOG ERROR] ' . $e->getMessage());
    }
}
