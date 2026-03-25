<?php
require_once __DIR__ . '/../database/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Submission ID required.");
}

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT cs.*, u.name as user_name FROM contract_submissions cs JOIN users u ON cs.user_id = u.id WHERE cs.id = ?");
$stmt->execute([$id]);
$submission = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$submission) {
    die("Submission not found.");
}

$data = json_decode($submission['contract_data'], true);
$lessee = $data['lessee'] ?? [];
$stall = $data['stall'] ?? [];
$terms = $data['terms'] ?? [];

$appName = getSetting('app_name', 'LeasePro');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contract - <?php echo $submission['ref_no']; ?></title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            line-height: 1.6;
            color: #000;
            margin: 40px;
            font-size: 12pt;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }
        .ref-no {
            float: right;
            font-family: monospace;
            font-size: 10pt;
            color: #555;
        }
        h1 { margin: 0; font-size: 24pt; text-transform: uppercase; }
        h2 { font-size: 16pt; margin-top: 30px; border-bottom: 1px solid #ccc; }
        
        .section { margin-bottom: 25px; }
        .row { display: flex; margin-bottom: 10px; }
        .label { font-weight: bold; width: 200px; }
        .value { border-bottom: 1px solid #000; flex: 1; min-height: 20px; padding-left: 10px; }
        
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .footer {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 250px;
            text-align: center;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 5px;
            font-weight: bold;
        }
        
        @media print {
            .no-print { display: none; }
            body { margin: 20px; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #f8fafc; padding: 15px; border-bottom: 1px solid #ddd; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-weight: bold; color: #334e9e;">Contract Preview: <?php echo $submission['ref_no']; ?></span>
        <button onclick="window.print()" style="padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Print Document</button>
    </div>

    <div class="ref-no">Reference No: <?php echo $submission['ref_no']; ?></div>
    
    <div class="header">
        <h1>LEASE CONTRACT</h1>
        <p><?php echo $appName; ?> - Official Lease Agreement</p>
    </div>

    <div class="section">
        <h2>I. LESSEE INFORMATION</h2>
        <div class="row">
            <div class="label">Account Name:</div>
            <div class="value"><?php echo htmlspecialchars($lessee['account_name'] ?? ''); ?></div>
        </div>
        <div class="row">
            <div class="label">Tradename/Store:</div>
            <div class="value"><?php echo htmlspecialchars($lessee['trade_name'] ?? ''); ?></div>
        </div>
        <div class="row">
            <div class="label">Business Address:</div>
            <div class="value"><?php echo htmlspecialchars($lessee['address'] ?? ''); ?></div>
        </div>
        <div class="grid">
            <div class="row">
                <div class="label">Nature of Biz:</div>
                <div class="value"><?php echo htmlspecialchars($lessee['nature_of_business'] ?? ''); ?></div>
            </div>
            <div class="row">
                <div class="label">TIN:</div>
                <div class="value"><?php echo htmlspecialchars($lessee['tin'] ?? ''); ?></div>
            </div>
        </div>
        <div class="grid">
            <div class="row">
                <div class="label">Email:</div>
                <div class="value"><?php echo htmlspecialchars($lessee['email'] ?? ''); ?></div>
            </div>
            <div class="row">
                <div class="label">Contact No:</div>
                <div class="value"><?php echo htmlspecialchars($lessee['mobile'] ?? ''); ?></div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>II. PREMISES & STALL DETAILS</h2>
        <div class="row">
            <div class="label">Location:</div>
            <div class="value"><?php echo htmlspecialchars($stall['location'] ?? ''); ?></div>
        </div>
        <div class="grid">
            <div class="row">
                <div class="label">Stall No:</div>
                <div class="value"><?php echo htmlspecialchars($stall['stall_no'] ?? ''); ?></div>
            </div>
            <div class="row">
                <div class="label">Total Area (sqm):</div>
                <div class="value"><?php echo htmlspecialchars($stall['area'] ?? ''); ?></div>
            </div>
        </div>
        <div class="grid">
            <div class="row">
                <div class="label">Rent Rate /sqm:</div>
                <div class="value">PHP <?php echo number_format((float)($stall['rate'] ?? 0), 2); ?></div>
            </div>
            <div class="row">
                <div class="label">Total Monthly Rent:</div>
                <div class="value">PHP <?php echo htmlspecialchars($stall['monthly_rent'] ?? ''); ?></div>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>III. LEASE PERIOD & TERMS</h2>
        <div class="grid">
            <div class="row">
                <div class="label">Commencement:</div>
                <div class="value"><?php echo htmlspecialchars($terms['start'] ?? ''); ?></div>
            </div>
            <div class="row">
                <div class="label">Expiration:</div>
                <div class="value"><?php echo htmlspecialchars($terms['end'] ?? ''); ?></div>
            </div>
        </div>
        <div class="row">
            <div class="label">Lease Duration:</div>
            <div class="value">
                <?php 
                echo ($terms['years'] ?? 0) . " Year(s), " . 
                     ($terms['months'] ?? 0) . " Month(s), " . 
                     ($terms['days'] ?? 0) . " Day(s)";
                ?>
            </div>
        </div>
    </div>

    <div class="section" style="margin-top: 40px;">
        <p style="font-style: italic; font-size: 10pt;">This document serves as a binding agreement upon approval. The lessee hereby declares that all information provided above is true and correct.</p>
    </div>

    <div class="footer">
        <div class="signature-box">
            <div class="sig-line">LESSEE SIGNATURE</div>
            <p>(<?php echo htmlspecialchars($lessee['account_name'] ?? ''); ?>)</p>
        </div>
        <div class="signature-box">
            <div class="sig-line">LESSOR REPRESENTATIVE</div>
            <p><?php echo $appName; ?> Management</p>
        </div>
    </div>

    <div style="margin-top: 30px; font-size: 8pt; text-align: center; color: #999;">
        Document Generated on <?php echo date('Y-m-d H:i:s'); ?> | Status: <?php echo $submission['status']; ?>
    </div>

    <script>
        // Auto-open print if needed
        window.onload = function() {
            // Optional: window.print();
        };
    </script>
</body>
</html>
