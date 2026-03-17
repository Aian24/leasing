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
?>
