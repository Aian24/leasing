<?php
require_once 'database/config.php';
$pdo = getPDO();
$pdo->exec("UPDATE users SET name = 'Maria Santos' WHERE id = 1");
$stmt = $pdo->query("SELECT id, name FROM users WHERE id = 1");
echo json_encode($stmt->fetch());
unlink(__FILE__);
