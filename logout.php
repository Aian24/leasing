<?php

session_start();
require_once 'database/config.php';
if (isset($_SESSION['user_id'])) {
    logAction('Logged Out', "User '{$_SESSION['username']}' manually logged out.", 'info');
}
session_unset();
session_destroy();
header('Location: index.php');
exit;
?>
