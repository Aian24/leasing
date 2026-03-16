<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeasePro Admin — Control Panel</title>
    <meta name="description" content="LeasePro Admin Panel — User management, frontend monitoring, system health.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-sections.css">
</head>

<body>

    <!-- SIDEBAR -->
    <?php include '../includes/layout/sidebar.php'; ?>

    <!-- MAIN -->
    <div id="admin-main">

        <!-- Header -->
        <?php include '../includes/layout/header.php'; ?>

        <!-- Page Content -->
        <div id="admin-content">
             <section id="sec-dashboard" class="admin-section active">
                 <?php include '../includes/sections/overview/dashboard.php'; ?>
             </section>
        </div>
    </div>

    <!-- MODALS -->
    <?php include '../includes/layout/modals.php'; ?>

    <script src="../js/admin-core.js"></script>
    <script src="../js/dashboard.js"></script>
</body>
</html>
