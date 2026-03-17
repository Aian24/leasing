<?php require_once '../includes/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management — LeasePro Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-sections.css">
</head>
<body>
    <?php include '../includes/layout/sidebar.php'; ?>
    <div id="admin-main">
        <?php include '../includes/layout/header.php'; ?>
        <div id="admin-content">
             <section id="sec-users" class="admin-section active">
                <?php include '../includes/sections/management/users.php'; ?>
             </section>
        </div>
    </div>
    <?php include '../includes/layout/modals.php'; ?>
    <script src="../js/admin-core.js"></script>
    <script src="../js/users.js"></script>
</body>
</html>

