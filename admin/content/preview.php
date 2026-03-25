<?php
/**
 * Live Preview Page for Page Builder
 * Renders draft content inside the FULL admin shell (sidebar + header).
 * Nothing is saved to the database — this is a pure visual preview.
 */
require_once '../includes/auth.php';
require_once '../../database/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['html'])) {
    die('<p style="font-family:sans-serif;padding:40px;color:#ef4444">No preview content received. Please use the Preview button in the builder.</p>');
}

$draftHtml = $_POST['html'];
$draftCss  = $_POST['css'] ?? '';
$pageName  = htmlspecialchars($_POST['page_name'] ?? 'Page');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview — <?php echo $pageName; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/admin-sections.css">
    <style>
        /* Sticky preview banner sitting above everything */
        #preview-banner {
            position: fixed;
            top: 0; left: 0; right: 0;
            background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%);
            color: #1c1917;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 13px;
            text-align: center;
            padding: 7px 20px;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 2px 16px rgba(245,158,11,0.45);
        }
        #preview-banner .badge {
            background: rgba(0,0,0,0.15);
            padding: 2px 10px;
            border-radius: 100px;
            font-size: 11px;
            letter-spacing: 0.05em;
        }
        #preview-banner .close-btn {
            background: rgba(0,0,0,0.15);
            border: none;
            color: #1c1917;
            padding: 3px 14px;
            border-radius: 100px;
            font-weight: 800;
            cursor: pointer;
            font-size: 12px;
            margin-left: 16px;
            transition: background 0.2s;
        }
        #preview-banner .close-btn:hover { background: rgba(0,0,0,0.25); }

        /* Push everything below the banner */
        body { padding-top: 38px; margin: 0; }

        /* Override pointer-events on sidebar links so user can't navigate away accidentally */
        #admin-sidebar .nav-item { pointer-events: none; opacity: 0.85; }
        #admin-sidebar .nav-item.active { pointer-events: none; }

        /* Injected draft CSS */
        <?php if ($draftCss): ?>
        <?php echo $draftCss; ?>
        <?php endif; ?>
    </style>
</head>
<body>

    <!-- ── Preview Banner ── -->
    <div id="preview-banner">
        <i class="fa-solid fa-eye"></i>
        <span class="badge">PREVIEW MODE</span>
        You are viewing a draft of <strong><?php echo $pageName; ?></strong> — Changes have NOT been published yet.
        <button class="close-btn" onclick="window.close()">✕ Close Preview</button>
    </div>

    <!-- ── Real Admin Sidebar ── -->
    <?php include '../includes/layout/sidebar.php'; ?>

    <!-- ── Real Admin Main Wrapper ── -->
    <div id="admin-main">

        <!-- ── Real Admin Header ── -->
        <?php include '../includes/layout/header.php'; ?>

        <!-- ── Draft Content injected here ── -->
        <div id="admin-content">
            <?php echo $draftHtml; ?>
        </div>

    </div><!-- /admin-main -->

</body>
</html>
