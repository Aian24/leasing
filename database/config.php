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

// ── Quick alias ───────────────────────────────────────────────
// Use $pdo = getPDO(); anywhere after including this file
