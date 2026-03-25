<?php
//
require_once '../../database/config.php';

$id = $_GET['id'] ?? 0;
$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$id]);
$page = $stmt->fetch();

if (!$page) {
    die("Page not found.");
}

// Logical import of hardcoded UI if DB content is empty
$initialContent = $page['content'] ?? '';
if (empty($initialContent) && $page['type'] === 'admin') {
    // Try to find the actual PHP file and extract HTML from #admin-content
    $possiblePaths = [
        __DIR__ . '/../' . $page['slug'],
        __DIR__ . '/../../' . $page['slug'], // In case slug is 'admin/...'
        __DIR__ . '/../management/' . $page['slug'],
        __DIR__ . '/../overview/' . $page['slug'],
        __DIR__ . '/../../admin/management/' . $page['slug'],
        __DIR__ . '/../../admin/overview/' . $page['slug']
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $fileContent = file_get_contents($path);
            
            // 1. Try to find the 'else' block inside #admin-content where the hardcoded UI lives
            // This is the pattern used in the recently modified admin files
            if (preg_match('/else\s*\{\s*\?>(.*?)<\?php\s*\}\s*\?>/is', $fileContent, $matches)) {
                $initialContent = trim($matches[1]);
                break;
            }

            // 2. Try to find a section/content include that isn't layout/sidebar/header
            if (preg_match('/<\?php\s+(?:include|require(?:_once)?)\s+[\'"](.*?(?:sections|management|overview).*?)[\'"];\s*\?>/is', $fileContent, $secMatches)) {
                $relPath = $secMatches[1];
                // Skip layout files
                if (strpos($relPath, 'layout/') === false) {
                    $sectionPath = dirname($path) . '/' . $relPath;
                    if (!file_exists($sectionPath)) {
                        $sectionPath = __DIR__ . '/../' . str_replace(['../', './'], '', $relPath);
                    }
                    if (file_exists($sectionPath)) {
                        $extracted = file_get_contents($sectionPath);
                        $extracted = preg_replace('/<\?php.*?\?>/is', '', $extracted);
                        $initialContent = trim($extracted);
                        break;
                    }
                }
            }

            // 3. Fallback: Extract between #admin-content and the next major UI closing tag
            $startTag = '<div id="admin-content">';
            $startPos = strpos($fileContent, $startTag);
            if ($startPos !== false) {
                $contentStart = $startPos + strlen($startTag);
                $extracted = substr($fileContent, $contentStart);
                
                // Truncate at the end of the content div, ignoring the very last closing tags of the page
                $endMarkers = ['</div><!-- /admin-content -->', '</div><!-- /admin-main -->', '</div> <!-- /admin-content -->'];
                foreach($endMarkers as $m) {
                    $endPos = strpos($extracted, $m);
                    if ($endPos !== false) {
                        $extracted = substr($extracted, 0, $endPos);
                        break;
                    }
                }
                
                $extracted = preg_replace('/<\?php.*?\?>/is', '', $extracted);
                $initialContent = trim($extracted);
                break;
            }
        }
    }
}

// Logic to determine the live preview URL
$liveUrl = '#';
if ($page['type'] === 'admin') {
    if (file_exists(__DIR__ . '/../management/' . $page['slug'])) {
        $liveUrl = '../management/' . $page['slug'];
    } else if (file_exists(__DIR__ . '/../overview/' . $page['slug'])) {
        $liveUrl = '../overview/' . $page['slug'];
    } else if (file_exists(__DIR__ . '/../' . $page['slug'])) {
        $liveUrl = '../' . $page['slug'];
    }
} else if ($page['type'] === 'frontend') {
    $liveUrl = '../../' . $page['slug'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Builder - <?php echo htmlspecialchars($page['page_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- GrapesJS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.2/css/grapes.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.2/grapes.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/grapesjs-preset-webpage@1.0.2/dist/grapesjs-preset-webpage.min.css">
    <script src="https://unpkg.com/grapesjs-preset-webpage@1.0.2/dist/grapesjs-preset-webpage.min.js"></script>
    <script src="https://unpkg.com/grapesjs-blocks-basic@1.0.1/dist/grapesjs-blocks-basic.min.js"></script>
    
    <style>
        body, html { height: 100%; margin: 0; padding: 0; overflow: hidden; background: #0f172a; color: #fff; font-family: 'Outfit', sans-serif; }
        #gjs { height: 100%; overflow: hidden; border: none; }

        /* ─── Component Panel (slide-out) ─────────────────── */
        #comp-panel {
            position: fixed;
            top: 66px; right: 0;
            width: 300px;
            height: calc(100vh - 66px);
            background: #0f172a;
            border-left: 1px solid rgba(59,130,246,0.15);
            z-index: 500;
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
            box-shadow: -8px 0 40px rgba(0,0,0,0.4);
        }
        #comp-panel.open { transform: translateX(0); }
        #comp-panel-header {
            padding: 16px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
        }
        #comp-panel-header .title { font-weight: 800; font-size: 14px; color: #fff; }
        #comp-panel-search {
            padding: 10px 14px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 10px;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            width: 100%;
            outline: none;
            margin: 12px 16px;
            width: calc(100% - 32px);
            box-sizing: border-box;
        }
        #comp-panel-search:focus { border-color: rgba(59,130,246,0.4); }
        #comp-panel-list {
            overflow-y: auto;
            padding: 0 12px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .comp-category-label {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #475569;
            text-transform: uppercase;
            padding: 14px 6px 6px;
        }
        .comp-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.15s;
            background: rgba(255,255,255,0.02);
        }
        .comp-item:hover {
            background: rgba(59,130,246,0.1);
            border-color: rgba(59,130,246,0.25);
        }
        .comp-item:active { transform: scale(0.98); }
        .comp-item-icon {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: rgba(59,130,246,0.1);
            display: flex; align-items: center; justify-content: center;
            color: #60a5fa;
            font-size: 14px;
            flex-shrink: 0;
        }
        .comp-item-text .name { font-size: 13px; font-weight: 700; color: #e2e8f0; }
        .comp-item-text .desc { font-size: 11px; color: #64748b; margin-top: 1px; }
        .comp-inserted {
            background: rgba(34,197,94,0.1) !important;
            border-color: rgba(34,197,94,0.3) !important;
        }

        /* ─── Toggle button (fab) ────────────────────────── */
        #comp-toggle {
            position: fixed;
            bottom: 28px; right: 28px;
            width: 52px; height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border: none;
            color: #fff;
            font-size: 20px;
            cursor: pointer;
            z-index: 600;
            box-shadow: 0 8px 24px rgba(59,130,246,0.45);
            display: flex; align-items: center; justify-content: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        #comp-toggle:hover { transform: scale(1.1); box-shadow: 0 12px 32px rgba(59,130,246,0.55); }
        #comp-toggle.active { background: linear-gradient(135deg, #ef4444, #dc2626); box-shadow: 0 8px 24px rgba(239,68,68,0.4); }

        .builder-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            height: 66px;
            background: #0f172a;
            border-bottom: 1px solid rgba(59, 130, 246, 0.15);
            position: relative;
            z-index: 100;
        }
        .btn-save {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.35);
            font-family: 'Outfit', sans-serif;
            font-size: 0.85rem;
        }
        .btn-save:hover { opacity: 0.9; transform: translateY(-1px); }
        .btn-back {
            color: #94a3b8;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Outfit', sans-serif;
        }
        .btn-back:hover { color: #fff; }

        /* Custom GrapesJS Overrides to match theme */
        .gjs-cv-canvas { background-color: #0f172a !important; }
        .gjs-one-bg { background-color: #1e293b !important; }
        .gjs-two-color { color: #94a3b8 !important; }
        .gjs-three-bg { background-color: #3b82f6 !important; box-shadow: none !important; }
        .gjs-four-color, .gjs-four-color-h:hover { color: #3b82f6 !important; }
        .gjs-pn-panels { background-color: #1e293b !important; border-color: rgba(59, 130, 246, 0.15) !important; }
        .btn-preview {
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            font-family: 'Outfit', sans-serif;
            font-size: 0.825rem;
            text-decoration: none;
        }
        .btn-preview:hover { background: rgba(255, 255, 255, 0.1); transform: translateY(-1px); }
        
        .btn-discard {
            background: rgba(239, 68, 68, 0.05);
            color: #fca5a5;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid rgba(239, 68, 68, 0.2);
            transition: all 0.2s;
            font-family: 'Outfit', sans-serif;
            font-size: 0.825rem;
        }
        .btn-discard:hover { background: rgba(239, 68, 68, 0.15); color: #fff; }

        /* Custom Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            transition: all 0.3s;
        }
        .custom-modal {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            width: 450px;
            padding: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transform: scale(0.95);
            transition: all 0.3s;
        }
        .modal-overlay.active { display: flex; }
        .modal-overlay.active .custom-modal { transform: scale(1); }
        .modal-title { font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 12px; display: flex; align-items: center; gap: 12px; }
        .modal-text { color: #94a3b8; font-size: 14px; line-height: 1.6; margin-bottom: 24px; }
        .modal-actions { display: flex; gap: 12px; justify-content: flex-end; }
        .btn-confirm { background: #3b82f6; color: #fff; border: none; padding: 10px 24px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s; }
        .btn-confirm:hover { background: #2563eb; }
        .btn-danger-modal { background: #ef4444; color: #fff; border: none; padding: 10px 24px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s; }
        .btn-danger-modal:hover { background: #dc2626; }
        .btn-cancel { background: transparent; color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.2); padding: 10px 24px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: 0.2s; }
        .btn-cancel:hover { color: #fff; background: rgba(255,255,255,0.05); }

        /* ── Full-screen Loader ── */
        #builder-loader {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.82);
            backdrop-filter: blur(10px);
            z-index: 5000;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }
        #builder-loader.show { display: flex; }
        .loader-spinner {
            width: 52px; height: 52px;
            border: 4px solid rgba(59,130,246,0.15);
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loader-text {
            color: #94a3b8;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Outfit', sans-serif;
            letter-spacing: 0.03em;
        }

        /* ── Toast Notifications ── */
        #toast-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 6000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            pointer-events: none;
        }
        .toast {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            border-radius: 14px;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            min-width: 280px;
            max-width: 380px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            pointer-events: auto;
            transform: translateX(120%);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s;
        }
        .toast.show { transform: translateX(0); opacity: 1; }
        .toast.toast-success { background: linear-gradient(135deg, #10b981, #059669); }
        .toast.toast-error   { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .toast.toast-info    { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .toast-icon { font-size: 18px; flex-shrink: 0; }
        .toast-body { flex: 1; }
        .toast-title { font-weight: 800; font-size: 14px; margin-bottom: 2px; }
        .toast-msg   { font-size: 12px; opacity: 0.85; font-weight: 500; }
    </style>
</head>
<body>
    <div class="builder-header">
        <div style="display:flex; align-items:center; gap:20px;">
            <a href="pages.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Pages</a>
            <div style="height: 24px; width: 1px; background: rgba(255,255,255,0.1);"></div>
            <div style="font-weight:700; font-size:16px;">Editing: <span style="color:var(--primary);"><?php echo htmlspecialchars($page['page_name']); ?></span></div>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <button onclick="confirmDiscard()" class="btn-discard">
                <i class="fa-solid fa-trash-can"></i> Discard
            </button>
            <button onclick="previewLive()" class="btn-preview">
                <i class="fa-solid fa-eye"></i> Preview
            </button>
            <button class="btn-save" id="save-btn" onclick="confirmAndSave()"><i class="fa-solid fa-cloud-arrow-up"></i> Publish Changes</button>
        </div>
    </div>
    
    <div style="height: calc(100vh - 66px); margin-right: 0; transition: margin-right 0.3s;" id="builder-canvas-wrap">
        <div id="gjs"></div>
    </div>

    <!-- ─── Floating + Button ─────────────────────── -->
    <button id="comp-toggle" onclick="toggleCompPanel()" title="Add Component">
        <i class="fa-solid fa-plus" id="comp-toggle-icon"></i>
    </button>

    <!-- ─── Component Insert Panel ───────────────── -->
    <div id="comp-panel">
        <div id="comp-panel-header">
            <div class="title"><i class="fa-solid fa-puzzle-piece" style="color:#60a5fa;margin-right:8px"></i>Insert Component</div>
            <button onclick="toggleCompPanel()" style="background:none;border:none;color:#64748b;cursor:pointer;font-size:16px;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <input id="comp-panel-search" type="text" placeholder="Search components…" oninput="filterComps(this.value)">
        <div id="comp-panel-list"></div>
    </div>

    <!-- Full-screen Loader -->
    <div id="builder-loader">
        <div class="loader-spinner"></div>
        <div class="loader-text" id="loader-text">Processing…</div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Confirm Modal -->
    <div id="saveModal" class="modal-overlay">
        <div class="custom-modal">
            <div class="modal-title">
                <i class="fa-solid fa-cloud-arrow-up" style="color:#3b82f6"></i> Confirm Publication
            </div>
            <div class="modal-text">
                Your changes will be pushed to the live website. This action will immediately update the page content for all active users.
            </div>
            <div class="modal-actions">
                <button onclick="closeSaveModal()" class="btn-cancel">Cancel</button>
                <button onclick="executeSave()" class="btn-confirm">Publish Now</button>
            </div>
        </div>
    </div>

    <!-- Discard Modal -->
    <div id="discardModal" class="modal-overlay">
        <div class="custom-modal" style="border-color:rgba(239, 68, 68, 0.2)">
            <div class="modal-title">
                <i class="fa-solid fa-circle-exclamation" style="color:#ef4444"></i> Discard Changes?
            </div>
            <div class="modal-text">
                Are you sure you want to discard your current edits? All unsaved work will be lost and reverted to the last published version.
            </div>
            <div class="modal-actions">
                <button onclick="closeDiscardModal()" class="btn-cancel">Keep Editing</button>
                <button onclick="executeDiscard()" class="btn-danger-modal">Discard Everything</button>
            </div>
        </div>
    </div>

    <script>
        const initialContent = <?php echo json_encode($initialContent); ?>;
        
        const editor = grapesjs.init({
            container: '#gjs',
            height: '100%',
            fromElement: true,
            storageManager: false,
            plugins: ['gjs-preset-webpage', 'gjs-blocks-basic'],
            pluginsOpts: {
                'gjs-preset-webpage': {
                    blocksBasicOpts: { flexGrid: true }
                }
            },
            canvas: {
                styles: [
                    '../css/admin.css',
                    '../css/admin-sections.css',
                    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap',
                    'data:text/css;charset=UTF-8,' + encodeURIComponent(`
                        body { display: block !important; height: auto !important; width: 100% !important; margin: 0 !important; padding: 20px !important; background: #0f172a !important; }
                        #admin-main { margin-left: 0 !important; width: 100% !important; display: block !important; }
                        #admin-content { padding: 0 !important; width: 100% !important; display: block !important; }
                        .admin-section { display: block !important; width: 100% !important; }
                    `)
                ],
                scripts: ['https://cdn.tailwindcss.com']
            }
        });

        // ═══════════════════════════════════════════════════
        //  CLICK-TO-INSERT COMPONENT PANEL
        //  No drag needed — click any item to add it to canvas
        // ═══════════════════════════════════════════════════

        const ADMIN_COMPONENTS = [
            {
                cat: 'Layout',
                items: [
                    { icon: 'fa-square', name: 'Panel / Card', desc: 'Generic card with header & body', html: `<div class="panel"><div class="panel-header"><div><div class="panel-title"><i class="fa-solid fa-layer-group" style="color:var(--primary);margin-right:8px"></i>Panel Title</div><div class="panel-subtitle">Subtitle here</div></div><button class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Action</button></div><div class="panel-body"><p style="color:var(--muted)">Add your content here.</p></div></div>` },
                    { icon: 'fa-columns', name: '2-Column Grid', desc: 'Two side-by-side panels', html: `<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px"><div class="panel" style="margin-bottom:0"><div class="panel-body"><p style="color:var(--muted)">Left column</p></div></div><div class="panel" style="margin-bottom:0"><div class="panel-body"><p style="color:var(--muted)">Right column</p></div></div></div>` },
                    { icon: 'fa-table-columns', name: '3-Column Grid', desc: 'Three side-by-side panels', html: `<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px"><div class="panel" style="margin-bottom:0"><div class="panel-body"><p style="color:var(--muted)">Col 1</p></div></div><div class="panel" style="margin-bottom:0"><div class="panel-body"><p style="color:var(--muted)">Col 2</p></div></div><div class="panel" style="margin-bottom:0"><div class="panel-body"><p style="color:var(--muted)">Col 3</p></div></div></div>` },
                    { icon: 'fa-minus', name: 'Section Divider', desc: 'Labelled horizontal rule', html: `<div style="display:flex;align-items:center;gap:16px;margin:24px 0"><div style="flex:1;height:1px;background:var(--border)"></div><span style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.08em">Section</span><div style="flex:1;height:1px;background:var(--border)"></div></div>` },
                ]
            },
            {
                cat: 'Statistics',
                items: [
                    { icon: 'fa-chart-bar', name: 'Stat Card Row ×4', desc: 'Four metric cards in a grid', html: `<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:24px"><div class="ls-card"><div class="glow" style="background:#3b82f6"></div><div class="ls-label">Total Records</div><div class="ls-val" style="color:#60a5fa">128</div><div style="font-size:11px;color:var(--muted);margin-top:6px"><i class="fa-solid fa-arrow-up" style="color:#22c55e"></i> 12% this month</div></div><div class="ls-card"><div class="glow" style="background:#22c55e"></div><div class="ls-label">Active</div><div class="ls-val" style="color:#4ade80">94</div><div style="font-size:11px;color:var(--muted);margin-top:6px"><i class="fa-solid fa-circle" style="color:#22c55e;font-size:8px"></i> Live</div></div><div class="ls-card"><div class="glow" style="background:#f59e0b"></div><div class="ls-label">Pending</div><div class="ls-val" style="color:#fbbf24">16</div><div style="font-size:11px;color:var(--muted);margin-top:6px">Awaiting review</div></div><div class="ls-card"><div class="glow" style="background:#ef4444"></div><div class="ls-label">Overdue</div><div class="ls-val" style="color:#f87171">8</div><div style="font-size:11px;color:var(--muted);margin-top:6px">Needs attention</div></div></div>` },
                    { icon: 'fa-hashtag', name: 'Single Stat Card', desc: 'One standalone metric', html: `<div class="ls-card" style="max-width:260px"><div class="glow" style="background:#3b82f6"></div><div class="ls-label">Metric Label</div><div class="ls-val" style="color:#60a5fa">0</div><div style="font-size:11px;color:var(--muted);margin-top:6px">Description here</div></div>` },
                ]
            },
            {
                cat: 'Tables',
                items: [
                    { icon: 'fa-table', name: 'Data Table', desc: 'Full table with search & pagination', html: `<div class="panel"><div class="panel-header"><div><div class="panel-title"><i class="fa-solid fa-table-list" style="color:var(--primary);margin-right:8px"></i>Data Table</div></div><div class="tbar" style="margin:0"><input type="text" class="form-control" style="max-width:220px" placeholder="Search…"><button class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add</button></div></div><div class="panel-body"><div style="overflow-x:auto"><table class="data-table"><thead><tr><th>#</th><th>Name</th><th>Status</th><th>Date</th><th style="text-align:right">Actions</th></tr></thead><tbody><tr><td>1</td><td>John Doe</td><td><span class="badge badge-success">Active</span></td><td>2025-01-01</td><td style="text-align:right"><button class="btn btn-sm btn-ghost"><i class="fa-solid fa-pen"></i></button><button class="btn btn-sm btn-ghost" style="color:#ef4444"><i class="fa-solid fa-trash"></i></button></td></tr><tr><td>2</td><td>Jane Smith</td><td><span class="badge badge-warning">Pending</span></td><td>2025-01-02</td><td style="text-align:right"><button class="btn btn-sm btn-ghost"><i class="fa-solid fa-pen"></i></button><button class="btn btn-sm btn-ghost" style="color:#ef4444"><i class="fa-solid fa-trash"></i></button></td></tr></tbody></table></div><div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;flex-wrap:wrap;gap:12px"><span style="font-size:13px;color:var(--muted)">Showing 1–2 of 100 entries</span><div class="pagination"><button class="page-btn" disabled><i class="fa-solid fa-chevron-left"></i></button><button class="page-btn active">1</button><button class="page-btn">2</button><button class="page-btn">3</button><button class="page-btn"><i class="fa-solid fa-chevron-right"></i></button></div></div></div></div>` },
                    { icon: 'fa-list-ul', name: 'Summary List', desc: 'Key-value row list with totals', html: `<div class="panel"><div class="panel-header"><div class="panel-title">Summary</div></div><div class="panel-body" style="padding:0"><div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid var(--border)"><span style="color:var(--muted);font-size:13px">Item Label</span><span style="font-weight:700;font-size:13px;color:#fff">Value</span></div><div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-bottom:1px solid var(--border)"><span style="color:var(--muted);font-size:13px">Another Label</span><span style="font-weight:700;font-size:13px;color:#60a5fa">₱ 0.00</span></div><div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px"><span style="color:var(--muted);font-size:13px">Total</span><span style="font-weight:800;font-size:15px;color:#4ade80">₱ 0.00</span></div></div></div>` },
                ]
            },
            {
                cat: 'Alerts',
                items: [
                    { icon: 'fa-circle-info', name: 'Alert — Info', desc: 'Blue information notice', html: `<div class="ann-card info" style="margin-bottom:14px"><div><div class="ann-card-title">ℹ️ Information Notice</div><div class="ann-card-body">This is an informational alert message.</div><div class="ann-card-meta">Posted just now</div></div></div>` },
                    { icon: 'fa-triangle-exclamation', name: 'Alert — Warning', desc: 'Yellow caution notice', html: `<div class="ann-card warning" style="margin-bottom:14px"><div><div class="ann-card-title">⚠️ Warning</div><div class="ann-card-body">Please review the situation carefully.</div><div class="ann-card-meta">Posted just now</div></div></div>` },
                    { icon: 'fa-circle-xmark', name: 'Alert — Danger', desc: 'Red critical alert', html: `<div class="ann-card danger" style="margin-bottom:14px"><div><div class="ann-card-title">🚨 Critical Alert</div><div class="ann-card-body">Immediate action required.</div><div class="ann-card-meta">Posted just now</div></div></div>` },
                    { icon: 'fa-circle-check', name: 'Alert — Success', desc: 'Green success confirmation', html: `<div class="ann-card success" style="margin-bottom:14px"><div><div class="ann-card-title">✅ Success</div><div class="ann-card-body">The operation completed successfully.</div><div class="ann-card-meta">Posted just now</div></div></div>` },
                ]
            },
            {
                cat: 'Content',
                items: [
                    { icon: 'fa-bolt', name: 'Quick Actions', desc: '4-button icon action grid', html: `<div class="panel"><div class="panel-header"><div class="panel-title"><i class="fa-solid fa-bolt" style="color:var(--primary);margin-right:8px"></i>Quick Actions</div></div><div class="panel-body"><div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px"><button class="quick-action-btn"><i class="fa-solid fa-user-plus"></i>Add User</button><button class="quick-action-btn"><i class="fa-solid fa-file-export"></i>Export</button><button class="quick-action-btn"><i class="fa-solid fa-print"></i>Print</button><button class="quick-action-btn"><i class="fa-solid fa-gear"></i>Settings</button></div></div></div>` },
                    { icon: 'fa-id-card', name: 'User Card', desc: 'Avatar with name, role & badge', html: `<div class="panel" style="max-width:320px;margin-bottom:0"><div class="panel-body"><div style="display:flex;align-items:center;gap:16px"><div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:#fff;font-weight:800;flex-shrink:0">JD</div><div><div style="font-weight:800;font-size:0.9375rem;color:#fff">John Doe</div><div style="font-size:0.8125rem;color:var(--muted)">Administrator</div><div style="margin-top:4px"><span class="badge badge-success">Online</span></div></div></div></div></div>` },
                    { icon: 'fa-inbox', name: 'Empty State', desc: 'Centered empty state with CTA', html: `<div class="empty-state"><i class="fa-solid fa-box-open"></i><div style="font-size:1rem;font-weight:700;margin-bottom:8px">No records found</div><div style="font-size:0.8125rem;color:var(--muted);margin-bottom:20px">There's nothing here yet.</div><button class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> Add First Record</button></div>` },
                    { icon: 'fa-hand-pointer', name: 'Button Group', desc: 'Primary, ghost & styled buttons', html: `<div style="display:flex;gap:12px;flex-wrap:wrap;align-items:center"><button class="btn btn-primary"><i class="fa-solid fa-plus"></i> Primary</button><button class="btn btn-ghost">Secondary</button><button class="btn btn-sm btn-primary">Small</button><button class="btn" style="background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.2)">Danger</button></div>` },
                    { icon: 'fa-tags', name: 'Badge Pills', desc: 'Status indicator badges', html: `<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center"><span class="badge badge-success">Active</span><span class="badge badge-warning">Pending</span><span class="badge badge-danger">Overdue</span><span class="badge" style="background:rgba(99,102,241,0.15);color:#a5b4fc">Draft</span><span class="badge" style="background:rgba(148,163,184,0.1);color:var(--muted)">Archived</span></div>` },
                ]
            }
        ];

        let panelOpen = false;

        function toggleCompPanel() {
            panelOpen = !panelOpen;
            const panel = document.getElementById('comp-panel');
            const toggle = document.getElementById('comp-toggle');
            const icon = document.getElementById('comp-toggle-icon');
            const wrap = document.getElementById('builder-canvas-wrap');
            panel.classList.toggle('open', panelOpen);
            toggle.classList.toggle('active', panelOpen);
            icon.className = panelOpen ? 'fa-solid fa-xmark' : 'fa-solid fa-plus';
            wrap.style.marginRight = panelOpen ? '300px' : '0';
        }

        function buildCompPanel() {
            const list = document.getElementById('comp-panel-list');
            list.innerHTML = '';
            ADMIN_COMPONENTS.forEach(group => {
                const label = document.createElement('div');
                label.className = 'comp-category-label';
                label.textContent = group.cat;
                list.appendChild(label);
                group.items.forEach(item => {
                    const el = document.createElement('div');
                    el.className = 'comp-item';
                    el.dataset.name = item.name.toLowerCase();
                    el.title = `Click to insert: ${item.name}`;
                    el.innerHTML = `
                        <div class="comp-item-icon"><i class="fa-solid ${item.icon}"></i></div>
                        <div class="comp-item-text">
                            <div class="name">${item.name}</div>
                            <div class="desc">${item.desc}</div>
                        </div>
                    `;
                    el.addEventListener('click', () => insertComp(el, item.html, item.name));
                    list.appendChild(el);
                });
            });
        }

        function insertComp(el, html, name) {
            editor.addComponents(html);
            // Flash green feedback
            el.classList.add('comp-inserted');
            const orig = el.querySelector('.name').textContent;
            el.querySelector('.name').textContent = '✓ Inserted!';
            setTimeout(() => {
                el.classList.remove('comp-inserted');
                el.querySelector('.name').textContent = orig;
            }, 1200);
            showToast('info', name + ' added', 'Component inserted at bottom of canvas.');
        }

        function filterComps(query) {
            const q = query.toLowerCase();
            document.querySelectorAll('.comp-item').forEach(el => {
                el.style.display = el.dataset.name.includes(q) ? '' : 'none';
            });
            document.querySelectorAll('.comp-category-label').forEach(label => {
                // Hide category labels when all items under them are hidden
                let next = label.nextElementSibling;
                let anyVisible = false;
                while (next && !next.classList.contains('comp-category-label')) {
                    if (next.style.display !== 'none') anyVisible = true;
                    next = next.nextElementSibling;
                }
                label.style.display = anyVisible ? '' : 'none';
            });
        }

        // Build the panel once editor is ready
        editor.on('load', () => {
            buildCompPanel();
        });

        if (initialContent && initialContent.trim() !== '') {
            editor.setComponents(initialContent);
        } else {
            editor.setComponents(`
                <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100vh; background-color:#f8fafc; color:#64748b; font-family:system-ui, sans-serif; text-align:center; padding:20px;">
                    <svg style="width:64px; height:64px; color:#cbd5e1; margin-bottom:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <h2 style="font-size:24px; font-weight:700; color:#475569; margin-bottom:8px;">Welcome to the Page Builder</h2>
                    <p style="font-size:16px;">The canvas is currently empty.</p>
                    <p style="font-size:14px; margin-top:8px;">Click the <b>4-squares icon</b> on the top right panel menu to open the Blocks Manager, then drag and drop elements here to start building!</p>
                </div>
            `);
        }

        async function previewLive() {
            // Create a hidden form and POST the current builder content directly to preview.php
            // This requires NO database saving — it's a pure draft preview
            const previewForm = document.createElement('form');
            previewForm.method = 'POST';
            previewForm.action = 'preview.php';
            previewForm.target = '_blank';
            previewForm.style.display = 'none';

            const addField = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                previewForm.appendChild(input);
            };

            addField('html', editor.getHtml());
            addField('css',  editor.getCss());
            addField('page_name', '<?php echo addslashes($page['page_name']); ?>');

            document.body.appendChild(previewForm);
            previewForm.submit();
            document.body.removeChild(previewForm);
        }

        function confirmDiscard() { document.getElementById('discardModal').classList.add('active'); }
        function closeDiscardModal() { document.getElementById('discardModal').classList.remove('active'); }
        function executeDiscard() {
            closeDiscardModal();
            showLoader('Discarding changes…');
            setTimeout(() => { location.reload(); }, 900);
        }

        /* ── Loader helpers ── */
        function showLoader(text = 'Processing…') {
            document.getElementById('loader-text').textContent = text;
            document.getElementById('builder-loader').classList.add('show');
        }
        function hideLoader() {
            document.getElementById('builder-loader').classList.remove('show');
        }

        /* ── Toast helper ── */
        function showToast(type, title, message, duration = 4000) {
            const icons = { success: 'fa-circle-check', error: 'fa-circle-xmark', info: 'fa-circle-info' };
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <i class="toast-icon fa-solid ${icons[type] || icons.info}"></i>
                <div class="toast-body">
                    <div class="toast-title">${title}</div>
                    ${message ? `<div class="toast-msg">${message}</div>` : ''}
                </div>
            `;
            container.appendChild(toast);
            requestAnimationFrame(() => { requestAnimationFrame(() => { toast.classList.add('show'); }); });
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, duration);
        }

        function confirmAndSave() {
            document.getElementById('saveModal').classList.add('active');
        }

        function closeSaveModal() {
            document.getElementById('saveModal').classList.remove('active');
        }

        async function executeSave() {
            closeSaveModal();
            await saveContent();
        }

        async function saveContent(isSilent = false) {
            const btn = document.getElementById('save-btn');
            const originalHTML = btn.innerHTML;

            if (!isSilent) {
                showLoader('Publishing changes…');
            }

            const html = editor.getHtml();
            const css = editor.getCss();
            const fullContent = html + (css ? '<style>' + css + '</style>' : '');

            const data = {
                action: 'save_content',
                id: <?php echo $id; ?>,
                content: fullContent
            };

            try {
                const res = await fetch('../../api/pages_api.php?action=save_content', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const json = await res.json();
                hideLoader();
                if (json.success) {
                    if (!isSilent) {
                        showToast('success', 'Changes Published!', 'Your page is now live for all users.');
                        btn.innerHTML = '<i class="fa-solid fa-check"></i> Published!';
                        btn.disabled = false;
                        setTimeout(() => {
                            btn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Publish Changes';
                        }, 2500);
                    }
                    return true;
                } else {
                    showToast('error', 'Publish Failed', json.message || 'Something went wrong.');
                    if (!isSilent) { btn.innerHTML = originalHTML; btn.disabled = false; }
                    return false;
                }
            } catch (err) {
                hideLoader();
                showToast('error', 'Connection Error', 'Could not reach the server. Please try again.');
                if (!isSilent) { btn.innerHTML = originalHTML; btn.disabled = false; }
                return false;
            }
        }
    </script>
</body>
</html>
