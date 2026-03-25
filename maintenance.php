<?php
require_once 'database/config.php';
$appName = getSetting('app_name', 'LeasePro');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance — <?php echo htmlspecialchars($appName); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');
        body { font-family: 'Outfit', sans-serif; background: #0f172a; color: #e2e8f0; }
        .glow { box-shadow: 0 0 50px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full text-center">
        <div class="mb-8 relative inline-block">
            <div class="w-24 h-24 bg-blue-600/20 rounded-3xl flex items-center justify-center text-blue-500 text-4xl glow animate-pulse">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div class="absolute -top-2 -right-2 w-6 h-6 bg-amber-500 rounded-full flex items-center justify-center text-[10px] text-white font-bold border-2 border-slate-900">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
        
        <h1 class="text-3xl font-extrabold text-white mb-4">Under Maintenance</h1>
        <p class="text-slate-400 mb-8 leading-relaxed">
            <?php echo htmlspecialchars($appName); ?> is currently undergoing scheduled improvements. 
            We'll be back online shortly. Thank you for your patience!
        </p>
        
        <div class="p-6 bg-slate-800/50 border border-slate-700/50 rounded-2xl mb-8">
            <div class="flex items-center gap-4 text-left">
                <div class="w-10 h-10 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400">
                    <i class="fa-solid fa-info-circle"></i>
                </div>
                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-slate-500">Estimated Time</div>
                    <div class="text-sm font-semibold text-white">~ 15 - 30 Minutes</div>
                </div>
            </div>
        </div>

        <p class="text-xs text-slate-500">
            © 2026 <?php echo htmlspecialchars($appName); ?> Security Team
        </p>
    </div>
</body>
</html>
