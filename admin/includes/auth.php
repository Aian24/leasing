<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    // Determine path to root index.php
    $path = $_SERVER['PHP_SELF'];
    if (strpos($path, '/management/') !== false || strpos($path, '/overview/') !== false || strpos($path, '/system/') !== false || strpos($path, '/content/') !== false) {
        header('Location: ../../index.php');
    } else {
        header('Location: ../index.php');
    }
    exit;
}

// Maintenance Mode Check
require_once __DIR__ . '/../../database/config.php';
if (getSetting('maintenance_mode') === 'true' && !in_array($_SESSION['role'], ['Admin', 'Manager', 'Staff'])) {
    header('Location: ../../maintenance.php');
    exit;
}

// Role-based Access Control
$current_page = basename($_SERVER['PHP_SELF']);
if (!canAccess($current_page)) {
    // If not allowed, redirect to dashboard with a warning (optional logs)
    header('Location: ' . (strpos($_SERVER['PHP_SELF'], '/overview/') !== false ? 'dashboard.php' : '../overview/dashboard.php'));
    exit;
}
?>
