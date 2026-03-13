<?php
// =============================================================
//  Lessees CSV API
// =============================================================
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

require_once __DIR__ . '/../database/config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $pdo = getPDO();

    switch ($action) {

        // ── LIST ────────────────────────────────────────────────
        case 'list':
            $page     = max(1, (int)($_GET['page'] ?? 1));
            $limit    = min(10000, max(10, (int)($_GET['limit'] ?? 25)));
            $search   = trim($_GET['search'] ?? '');
            $offset   = ($page - 1) * $limit;

            $where = '';
            $params = [];
            if ($search !== '') {
                $where = "WHERE company_name LIKE :s OR trade_name LIKE :s2
                           OR space_code LIKE :s3 OR owner_lessee_name LIKE :s4
                           OR email_address LIKE :s5";
                $like = "%$search%";
                $params = [':s'=>$like,':s2'=>$like,':s3'=>$like,':s4'=>$like,':s5'=>$like];
            }

            $total = $pdo->prepare("SELECT COUNT(*) FROM lessees $where");
            $total->execute($params);
            $totalRows = (int)$total->fetchColumn();

            $stmt = $pdo->prepare("SELECT * FROM lessees $where ORDER BY id ASC LIMIT :lim OFFSET :off");
            foreach ($params as $k => &$v) { $stmt->bindValue($k, $v); }
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            // Get global lease stats
            $leaseStats = $pdo->query("
                SELECT
                    COUNT(CASE WHEN lease_period_end > DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as active,
                    COUNT(CASE WHEN lease_period_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as expiring,
                    COUNT(CASE WHEN lease_period_end < CURDATE() THEN 1 END) as expired
                FROM lessees
            ")->fetch(PDO::FETCH_ASSOC);

            // Active total = active + expiring
            $realActive = (int)$leaseStats['active'] + (int)$leaseStats['expiring'];

            echo json_encode([
                'success'  => true,
                'data'     => $rows,
                'total'    => $totalRows,
                'page'     => $page,
                'limit'    => $limit,
                'pages'    => (int)ceil($totalRows / $limit),
                'stats'    => [
                    'active'   => $realActive,
                    'expiring' => (int)$leaseStats['expiring'],
                    'expired'  => (int)$leaseStats['expired']
                ]
            ]);
            break;

        // ── UPLOAD CSV ─────────────────────────────────────────
        case 'upload':
            if (empty($_FILES['csv_file'])) {
                echo json_encode(['success'=>false,'message'=>'No file uploaded.']);
                break;
            }

            $file = $_FILES['csv_file'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'csv') {
                echo json_encode(['success'=>false,'message'=>'Only .csv files are accepted.']);
                break;
            }

            $handle = fopen($file['tmp_name'], 'r');
            if (!$handle) {
                echo json_encode(['success'=>false,'message'=>'Could not read file.']);
                break;
            }

            // Detect delimiter from first data line
            $delimChar = ',';
            $firstLine = fgets($handle);
            if (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                $delimChar = "\t";
                rewind($handle);
                $rawHeader = fgetcsv($handle, 0, "\t");
            } else {
                rewind($handle);
                $rawHeader = fgetcsv($handle);
            }

            if (!$rawHeader) {
                echo json_encode(['success'=>false,'message'=>'Empty CSV file.']);
                break;
            }

            $header = array_map(function($h) {
                $h = trim($h, " \t\n\r\0\x0B\"\'\xEF\xBB\xBF");
                $h = strtolower($h);
                $h = preg_replace('/[^a-z0-9\/\.\&\'\-]+/', '_', $h);
                $h = trim($h, '_');
                return $h;
            }, $rawHeader);

            $map = [
                'company_name'                          => 'company_name',
                'trade_name/store_name'                 => 'trade_name',
                'trade_name_store_name'                 => 'trade_name',
                'store_name'                            => 'trade_name',
                'trade_name'                            => 'trade_name',
                'nature_of_business'                    => 'nature_of_business',
                'nature'                                => 'nature_of_business',
                "owner's_name/_lessee_representative"  => 'owner_lessee_name',
                "owner's_name/lessee_representative"   => 'owner_lessee_name',
                'owners_name_lessee_representative'    => 'owner_lessee_name',
                "owner's_name"                         => 'owner_lessee_name',
                'lessee_representative'                => 'owner_lessee_name',
                'owner_lessee_name'                    => 'owner_lessee_name',
                'space_code'                           => 'space_code',
                'stall_code'                           => 'space_code',
                'unit_code'                            => 'space_code',
                'total_area'                           => 'total_area',
                'area'                                 => 'total_area',
                'rate_per_sqm'                         => 'rate_per_sqm',
                'rate'                                 => 'rate_per_sqm',
                'basic_rent'                           => 'basic_rent',
                'rent'                                 => 'basic_rent',
                'cusa'                                 => 'cusa',
                'aircon_charges'                       => 'aircon_charges',
                'aircon'                               => 'aircon_charges',
                'security_deposit'                     => 'security_deposit',
                'electricity_&_water_charges'          => 'electricity_water_charges',
                'electricity_water_charges'            => 'electricity_water_charges',
                'electricity_&_water'                  => 'electricity_water_charges',
                'utility_deposit'                      => 'utility_deposit',
                'construction_bond'                    => 'construction_bond',
                'lease_period'                         => '__lease_period_range',
                'lease_period_start'                   => 'lease_period_start',
                'lease_period_end'                     => 'lease_period_end',
                "valid_id's_presented/expiration_date" => 'valid_ids_presented',
                "valid_id's_presented"                 => 'valid_ids_presented',
                'valid_ids_presented'                  => 'valid_ids_presented',
                'valid_ids'                            => 'valid_ids_presented',
                'business_address'                     => 'business_address',
                "owner's_address"                      => 'owner_address',
                'owners_address'                       => 'owner_address',
                'owner_address'                        => 'owner_address',
                'contact_nos.'                         => 'contact_nos',
                'contact_nos'                          => 'contact_nos',
                'contact'                              => 'contact_nos',
                'contact_no'                           => 'contact_nos',
                'requirments_submitted'                => 'requirements_submitted',
                'requirements_submitted'               => 'requirements_submitted',
                'requirements'                         => 'requirements_submitted',
                'email_address'                        => 'email_address',
                'email'                                => 'email_address',
            ];

            $colMap = [];
            foreach ($header as $i => $h) {
                if (isset($map[$h])) {
                    $colMap[$i] = $map[$h];
                }
            }

            // Fallback to strict indexes if no headers match at all
            if (empty($colMap)) {
                $colMap = [
                    0  => 'company_name',
                    1  => 'trade_name',
                    2  => 'nature_of_business',
                    3  => 'owner_lessee_name',
                    4  => 'space_code',
                    5  => 'total_area',
                    6  => 'rate_per_sqm',
                    7  => 'basic_rent',
                    8  => 'cusa',
                    9  => 'aircon_charges',
                    10 => 'security_deposit',
                    11 => 'electricity_water_charges',
                    12 => 'utility_deposit',
                    13 => 'construction_bond',
                    14 => '__lease_period_range',
                ];
                rewind($handle);
            }

            $allowed = [
                'company_name','trade_name','nature_of_business','owner_lessee_name',
                'space_code','total_area','rate_per_sqm','basic_rent','cusa',
                'aircon_charges','security_deposit','electricity_water_charges',
                'utility_deposit','construction_bond','lease_period_start','lease_period_end',
                'valid_ids_presented','business_address','owner_address','contact_nos',
                'requirements_submitted','email_address'
            ];

            $parseDate = function(string $s): ?string {
                $s = trim($s);
                if ($s === '' || $s === '-') return null;
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;
                $ts = strtotime($s);
                if ($ts !== false) return date('Y-m-d', $ts);
                return $s; 
            };

            $inserted = 0; $skipped = 0; $errors = [];

            $pdo->beginTransaction();
            while (($row = fgetcsv($handle, 0, $delimChar)) !== false) {
                if (array_filter($row) === []) continue;

                $data = [];
                // Initialize company name just in case
                $data['company_name'] = null;

                foreach ($colMap as $idx => $dbCol) {
                    $rawVal = isset($row[$idx]) ? trim($row[$idx], " \t\n\r\0\x0B\"\'\xEF\xBB\xBF") : '';
                    if ($dbCol === '__lease_period_range') {
                        if (preg_match('/^(.+?)\s*[-–]\s*(.+)$/', $rawVal, $m)) {
                            $data['lease_period_start'] = $parseDate(trim($m[1]));
                            $data['lease_period_end']   = $parseDate(trim($m[2]));
                        } else {
                            $data['lease_period_start'] = $parseDate($rawVal);
                        }
                        continue;
                    }
                    if (!in_array($dbCol, $allowed)) continue;
                    if ($rawVal === '' || $rawVal === '-') {
                        $data[$dbCol] = null;
                        continue;
                    }
                    if ($dbCol === 'total_area') {
                        $data[$dbCol] = trim(preg_replace('/\s*(sq\.?m\.?|sqm|m2)\s*/i', '', $rawVal));
                        continue;
                    }
                    if (in_array($dbCol, ['lease_period_start','lease_period_end'])) {
                        $data[$dbCol] = $parseDate($rawVal);
                        continue;
                    }
                    $data[$dbCol] = $rawVal;
                }

                if (empty($data['company_name'])) { 
                    $errors[] = 'Row skipped: empty company_name. Dump: ' . json_encode($data);
                    $skipped++; 
                    continue; 
                }

                $cols = implode(',', array_map(fn($c) => "`$c`", array_keys($data)));
                $pholders = implode(',', array_map(fn($c) => ":$c", array_keys($data)));

                try {
                    $ins = $pdo->prepare("INSERT INTO lessees ($cols) VALUES ($pholders)");
                    $ins->execute($data);
                    $inserted++;
                } catch (PDOException $e) {
                    $errors[] = 'DB error: ' . $e->getMessage();
                    $skipped++;
                }
            }
            fclose($handle);
            $pdo->commit();

            $msg = "$inserted record(s) imported. $skipped skipped.";
            if ($skipped > 0 && !empty($errors)) {
                $msg .= " First error: " . $errors[0];
            }

            echo json_encode([
                'success'  => true,
                'inserted' => $inserted,
                'skipped'  => $skipped,
                'errors'   => array_slice($errors, 0, 10),
                'message'  => $msg,
            ]);
            break;

        case 'create':
            $body = json_decode(file_get_contents('php://input'), true);
            if (empty($body['company_name'])) {
                echo json_encode(['success'=>false,'message'=>'Company Name is required.']);
                break;
            }

            $allowed = [
                'company_name','trade_name','nature_of_business','owner_lessee_name',
                'space_code','total_area','rate_per_sqm','basic_rent','cusa',
                'aircon_charges','security_deposit','electricity_water_charges',
                'utility_deposit','construction_bond','lease_period_start','lease_period_end',
                'valid_ids_presented','business_address','owner_address','contact_nos',
                'requirements_submitted','email_address','status'
            ];

            $data = [];
            foreach ($allowed as $col) {
                if (isset($body[$col]) && trim((string)$body[$col]) !== '') {
                    $data[$col] = trim((string)$body[$col]);
                } else {
                    $data[$col] = null;
                }
            }

            $cols = implode(',', array_map(fn($c) => "`$c`", array_keys($data)));
            $pholders = implode(',', array_map(fn($c) => ":$c", array_keys($data)));

            try {
                $stmt = $pdo->prepare("INSERT INTO lessees ($cols) VALUES ($pholders)");
                $stmt->execute($data);
                echo json_encode(['success'=>true,'message'=>'Lessee created successfully.']);
            } catch (PDOException $e) {
                echo json_encode(['success'=>false,'message'=>'Failed to create record: ' . $e->getMessage()]);
            }
            break;

        case 'update':
            $body = json_decode(file_get_contents('php://input'), true);
            $id = (int)($body['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success'=>false,'message'=>'Invalid ID.']);
                break;
            }
            if (empty($body['company_name'])) {
                echo json_encode(['success'=>false,'message'=>'Company Name is required.']);
                break;
            }

            $allowed = [
                'company_name','trade_name','nature_of_business','owner_lessee_name',
                'space_code','total_area','rate_per_sqm','basic_rent','cusa',
                'aircon_charges','security_deposit','electricity_water_charges',
                'utility_deposit','construction_bond','lease_period_start','lease_period_end',
                'valid_ids_presented','business_address','owner_address','contact_nos',
                'requirements_submitted','email_address','status'
            ];

            $data = [':id' => $id];
            $updates = [];
            foreach ($allowed as $col) {
                if (isset($body[$col])) {
                    if (trim((string)$body[$col]) !== '') {
                        $data[":$col"] = trim((string)$body[$col]);
                    } else {
                        $data[":$col"] = null;
                    }
                    $updates[] = "`$col` = :$col";
                }
            }
            
            if (empty($updates)) {
                echo json_encode(['success'=>true,'message'=>'Nothing to update.']);
                break;
            }

            $setClause = implode(', ', $updates);
            try {
                $stmt = $pdo->prepare("UPDATE lessees SET $setClause WHERE id = :id");
                $stmt->execute($data);
                echo json_encode(['success'=>true,'message'=>'Lessee updated successfully.']);
            } catch (PDOException $e) {
                echo json_encode(['success'=>false,'message'=>'Failed to update record: ' . $e->getMessage()]);
            }
            break;

        case 'delete_one':
        case 'delete_all':
            if ($action === 'delete_all') {
                $pdo->exec("DELETE FROM lessees");
                $pdo->exec("ALTER TABLE lessees AUTO_INCREMENT = 1");
                echo json_encode(['success'=>true,'message'=>'All lessee records have been deleted.']);
            } else {
                $body = json_decode(file_get_contents('php://input'), true);
                $id   = (int)($body['id'] ?? 0);
                if ($id <= 0) {
                    echo json_encode(['success'=>false,'message'=>'Invalid ID.']);
                    break;
                }
                $stmt = $pdo->prepare("DELETE FROM lessees WHERE id = :id");
                $stmt->execute([':id' => $id]);
                echo json_encode(['success'=>true,'message'=>"Record #$id deleted."]);
            }
            break;

        case 'download_template':
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="lessees_template.csv"');
            $f = fopen('php://output', 'w');
            fputcsv($f, [
                'COMPANY NAME','TRADE NAME/STORE NAME','NATURE OF BUSINESS',
                "OWNER'S NAME/ LESSEE REPRESENTATIVE",'SPACE CODE','TOTAL AREA',
                'RATE PER SQM','BASIC RENT','CUSA','AIRCON CHARGES','SECURITY DEPOSIT',
                'ELECTRICITY & WATER CHARGES','UTILITY DEPOSIT','CONSTRUCTION BOND',
                'LEASE PERIOD START','LEASE PERIOD END',"VALID ID'S PRESENTED/EXPIRATION DATE",
                'BUSINESS ADDRESS',"OWNER'S ADDRESS",'CONTACT NOS.',
                'REQUIREMENTS SUBMITTED','EMAIL ADDRESS'
            ]);
            fclose($f);
            exit;

        default:
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>"Unknown action: $action"]);
    }

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
