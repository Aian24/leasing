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
    <title>Contract Proposal - <?php echo $submission['ref_no']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800;900&display=swap');
        
        @page { size: A4; margin: 0.3in; }
        body {
            font-family: 'Inter', "Calibri", "Helvetica", sans-serif;
            font-size: 9.5pt;
            line-height: 1.4;
            color: #000000;
            background: #f1f5f9;
            margin: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .print-container {
            max-width: 850px;
            margin: 15px auto;
            padding: 25px 35px;
            background: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1);
            border-radius: 16px;
        }
        .header-block {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 5px;
        }
        .header-logo img {
            max-width: 500px;
            max-height: 150px;
        }
        .header-meta {
            text-align: right;
        }
        .proposal-title {
            color: #000000;
            font-weight: 900;
            font-size: 16pt;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin: 0;
        }
        .ref-text {
            font-size: 7.5pt;
            color: #000000;
            font-family: monospace;
            text-transform: uppercase;
            font-weight: bold;
        }

        .addressed-to {
            background: #f8fafc;
            padding: 10px 14px;
            border-radius: 10px;
            border-left: 4px solid #000000;
            width: 100%;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        .addressed-to p { margin: 0; line-height: 1.3; }
        
        .intro-paragraph {
            font-size: 9.5pt;
            color: #000000;
            text-align: justify;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .terms-box {
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
        }
        .terms-table {
            width: 100%;
            border-collapse: collapse;
        }
        .terms-table td {
            padding: 7px 12px;
            vertical-align: top;
            border-bottom: 1px solid #e2e8f0;
        }
        .terms-table tr:last-child td { border-bottom: none; }
        .col-label {
            width: 35%;
            font-weight: 800;
            color: #000000;
            background: #f8fafc;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 7.5pt;
            border-right: 1px solid #e2e8f0;
        }
        .col-value {
            width: 65%;
            color: #000000;
            font-size: 9.5pt;
            font-weight: 500;
        }

        .footer-note {
            font-size: 8pt;
            color: #000000;
            text-align: justify;
            background: #fffbeb;
            border: 1px solid #fde68a;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .signatures-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            text-align: center;
        }
        .sig-section-title {
            font-weight: normal;
            text-transform: uppercase;
            color: #000000;
            margin-bottom: 8px;
            font-size: 8pt;
            letter-spacing: 1px;
        }
        .sig-block {
            margin-bottom: 12px;
        }
        .sig-name {
            font-weight: 800;
            color: #000000;
            border-top: 2px solid #cbd5e1;
            padding-top: 4px;
            display: inline-block;
            min-width: 200px;
            font-size: 9pt;
            text-transform: uppercase;
        }
        .sig-title {
            font-size: 7.5pt;
            color: #000000;
            margin-top: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: normal;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .print-container { 
                box-shadow: none; 
                border-radius: 0;
                margin: 0; 
                padding: 0; 
                max-width: 100%;
            }
            .footer-note {
                background: transparent;
                border-color: #cbd5e1;
            }
        }

        /* Action Bar */
        .action-bar {
            background: #000000;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Inter', sans-serif;
            position: relative;
            z-index: 10;
        }
        .action-bar .title { font-weight: bold; color: #fff; font-size: 11pt; }
        .btn-print {
            padding: 10px 24px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.2s;
        }
        .btn-print:hover { background: #2563eb; }
    </style>
</head>
<body>
    <div class="no-print action-bar">
        <span class="title">Proposal Dashboard View // <?php echo $submission['ref_no']; ?></span>
        <button class="btn-print" onclick="window.print()"><i class="fa-solid fa-print"></i> Print Document</button>
    </div>

    <div class="print-container">
        <div class="header-block">
            <div class="header-logo">
                <img src="../images/logo.png" alt="Logo">
            </div>
            <div class="header-meta">
                <div class="proposal-title">Lease Proposal</div>
            </div>
        </div>
        
        <div class="addressed-to">
            <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                <div style="font-size: 8pt; color: #000000; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">Prepared On: <span style="color:#000000"><?php echo date('F j, Y'); ?></span></div>
                <div style="font-size: 11pt; text-transform: uppercase; font-weight: 900; color: #000000;"><?php echo htmlspecialchars($lessee['account_name'] ?? ''); ?></div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                <div style="text-transform: uppercase; font-weight: 800; color: #000000; font-size: 10pt;"><?php echo htmlspecialchars($lessee['trade_name'] ?? ''); ?></div>
                <div style="font-size: 8.5pt; color: #000000; font-weight: 500;">Owner / Official Representative</div>
            </div>
        </div>

        <div class="intro-paragraph">
            We are pleased to submit our formal Lease Proposal for your proposed business at <strong>The New Sanko Wet & Dry Market located at Sumulong Highway corner Munding Avenue in Marikina City</strong>. Hereunder are the stipulated terms and conditions for your reference and review:
        </div>

        <div class="terms-box">
            <table class="terms-table">
                <tr>
                    <td class="col-label">Nature of Business</td>
                    <td class="col-value"><?php echo htmlspecialchars($lessee['nature_of_business'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td class="col-label">Space Code</td>
                    <td class="col-value"><span style="background:#e0e7ff; color:#4338ca; padding:4px 10px; border-radius:6px; font-family:monospace; font-weight:bold; font-size: 9pt;"><?php echo htmlspecialchars($stall['stall_no'] ?? ''); ?></span></td>
                </tr>
                <tr>
                    <td class="col-label">Area Target</td>
                    <td class="col-value"><strong><?php echo htmlspecialchars($stall['area'] ?? ''); ?> sq.m.</strong> <span style="color:#000000; font-size:8.5pt; margin-left:8px">(subject for final measurement)</span></td>
                </tr>
                <tr>
                    <td class="col-label">Lease Term</td>
                    <td class="col-value" style="display: flex; align-items: center; justify-content: space-between;">
                        <span>
                        <?php 
                        $y = $terms['years'] ?? 0;
                        $m = $terms['months'] ?? 0;
                        $d = $terms['days'] ?? 0;
                        $parts = [];
                        if ($y) $parts[] = "$y year(s)";
                        if ($m) $parts[] = "$m month(s)";
                        if ($d) $parts[] = "$d day(s)";
                        echo implode(" and ", $parts);
                        ?>
                        </span>
                        <span style="font-size:8pt; font-weight:800; color:#059669; background:#d1fae5; padding:4px 8px; border-radius:6px; letter-spacing: 0.5px;">RENEWABLE</span>
                    </td>
                </tr>
                <tr>
                    <td class="col-label">Lease Period</td>
                    <td class="col-value"><span style="color:#000000; font-weight:bold;"><?php echo htmlspecialchars($terms['start'] ?? ''); ?></span> &nbsp;&mdash;&nbsp; <span style="color:#000000; font-weight:bold;"><?php echo htmlspecialchars($terms['end'] ?? ''); ?></span></td>
                </tr>
                <tr>
                    <td class="col-label">Date of Effectivity</td>
                    <td class="col-value" style="font-weight:bold; color:#000000;"><?php echo htmlspecialchars($terms['start'] ?? ''); ?></td>
                </tr>
                <tr>
                    <td class="col-label">Rental Charges</td>
                    <td class="col-value">
                        <div style="font-weight:900; color:#000000; margin-bottom:2px;">PHP <?php echo number_format((float)($stall['rate'] ?? 0), 2); ?> <span style="font-size:7.5pt; color:#000000; font-weight:bold">/sq.m/day</span></div>
                        <div style="font-weight:800; color:#3b82f6;">PHP <?php echo htmlspecialchars($stall['monthly_rent'] ?? ''); ?> <span style="font-size:7.5pt; color:#000000; font-weight:bold">/month</span></div>
                    </td>
                </tr>
                <tr>
                    <td class="col-label">Annual Escalation</td>
                    <td class="col-value">Ten (10%) percent annual escalation</td>
                </tr>
                <tr>
                    <td class="col-label">Security Deposit</td>
                    <td class="col-value"><strong>Two (2) months basic rent</strong> &nbsp;<span style="color:#000000; font-size:8.5pt;">(refundable at the end of the lease term)</span></td>
                </tr>
                <tr>
                    <td class="col-label">Electricity & Water</td>
                    <td class="col-value">Sub-metered plus 15% USF, or a minimum charge of <strong>PHP 500.00</strong>, whichever is higher.</td>
                </tr>
                <tr>
                    <td class="col-label">Target Turnover Date</td>
                    <td class="col-value"><span style="color:#059669; font-weight:800; background:#d1fae5; padding:4px 10px; border-radius:6px; font-size:9pt;"><i class="fa-solid fa-circle-check" style="margin-right:6px"></i>Ready for Turn over</span></td>
                </tr>
                <tr>
                    <td class="col-label">Remarks</td>
                    <td class="col-value">Gov't permits and licenses &mdash; <strong style="color: #000000">Lessee's Account</strong>.</td>
                </tr>
            </table>
        </div>

        <div class="footer-note">
            <strong style="color: #b45309">IMPORTANT NOTICE:</strong> If you agree to the exact terms and conditions stipulated in this formal lease proposal, kindly sign in the allocated space provided below and return the confirmed copy no later than <strong>five (5) days</strong> from the generation date. Should you require further clarification on any points, please contact the undersigned. <br>
            <span style="font-weight:bold; color:#000000; font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.5px;"><i class="fa-solid fa-circle-exclamation" style="margin-right: 5px;"></i> Note: This space is simultaneously offered to other prospective applicants.</span>
        </div>

        <div class="signatures-grid">
            <div>
                <div class="sig-section-title">Submitted By / Lessor</div>
                <div style="margin-bottom: 30px; font-weight: 900; text-transform: uppercase; color: #000000; font-size: 10pt; letter-spacing: 0.5px;">
                    LC LOPEZ RESOURCES, INC.
                </div>
                
                <div class="sig-block">
                    <div class="sig-name"><?php echo htmlspecialchars(getSetting('leasing_assistant_name', 'MS. SHEILA MARIE C. VALERIO')); ?></div>
                    <div class="sig-title"><?php echo htmlspecialchars(getSetting('leasing_assistant_title', 'Leasing Assistant')); ?></div>
                </div>

                <div class="sig-block" style="margin-bottom:0; margin-top: 35px;">
                    <div class="sig-name"><?php echo htmlspecialchars(getSetting('leasing_manager_name', 'MS. KRISTINA G. COMIA')); ?></div>
                    <div class="sig-title"><?php echo htmlspecialchars(getSetting('leasing_manager_title', 'Leasing Manager')); ?></div>
                </div>
            </div>

            <div>
                <div class="sig-section-title">Conforme / Lessee</div>
                <div style="margin-bottom: 30px; font-weight: 900; text-transform: uppercase; color: #000000; font-size: 10pt; letter-spacing: 0.5px;">
                    <?php echo htmlspecialchars($lessee['trade_name'] ?? 'Authorized Lessee'); ?>
                </div>

                <div class="sig-block" style="margin-top: 70px;">
                    <div class="sig-name"><?php echo htmlspecialchars($lessee['account_name'] ?? ''); ?></div>
                    <div class="sig-title">Owner / Operator</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
