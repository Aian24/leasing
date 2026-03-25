<?php
require_once '../includes/auth.php';
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
    <script>
        window.ENABLE_ANNOUNCEMENTS = true;
    </script>
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
                 <?php 
                 $slug = 'dashboard.php';
                 $db = getPDO();
                 
                 // Check for preview mode
                 $id_stmt = $db->prepare("SELECT id FROM pages WHERE slug = ?");
                 $id_stmt->execute([$slug]);
                 $page_id = $id_stmt->fetchColumn();
                 
                 $dbContent = '';
                 if (isset($_GET['preview']) && $page_id) {
                     if (session_status() === PHP_SESSION_NONE) session_start();
                     $dbContent = $_SESSION['builder_preview'][$page_id] ?? '';
                 }

                 if (empty($dbContent)) {
                     $stmt = $db->prepare("SELECT content FROM pages WHERE slug = ?");
                     $stmt->execute([$slug]);
                     $dbContent = $stmt->fetchColumn();
                 }

                 if ($dbContent && trim($dbContent) !== '') {
                     echo $dbContent;
                 } else {
                     include '../includes/sections/overview/dashboard.php'; 
                 }
                 ?>
             </section>
        </div>
    </div>

    <!-- MODALS -->
    <?php include '../includes/layout/modals.php'; ?>

    <script src="../js/admin-core.js"></script>
    <script src="../js/dashboard.js"></script>
</body>
</html>
