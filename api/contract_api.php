<?php
require_once __DIR__ . '/../database/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';
$pdo = getPDO();
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'User';

try {
    if ($action === 'submit') {
        $input = json_decode(file_get_contents('php://input'), true);
        $page_id = $input['page_id'] ?? null;
        $contract_data = $input['contract_data'] ?? null;
        
        if (!$page_id) {
            echo json_encode(['success' => false, 'message' => 'Missing document ref']);
            exit;
        }

        // No longer restricting to one submission per page, as users may need 
        // to submit multiple contracts (e.g. for different stalls).


        // Generate unique reference number
        $ref_no = 'CNT-' . strtoupper(substr(uniqid(), -8));

        $stmt = $pdo->prepare("INSERT INTO contract_submissions (ref_no, user_id, page_id, status, contract_data) VALUES (?, ?, ?, 'Pending', ?)");
        $stmt->execute([$ref_no, $user_id, $page_id, json_encode($contract_data)]);
        $submission_id = $pdo->lastInsertId();

        // Automatically mark the stall as active/occupied in the lessees record
        // This makes it show as 'Occupied' (Red) in the Browse Stall selection
        if (isset($contract_data['stall']['location'])) {
            $spaceCode = $contract_data['stall']['location'];
            $updStmt = $pdo->prepare("UPDATE lessees SET status = 'Active' WHERE space_code = ?");
            $updStmt->execute([$spaceCode]);
        }

        echo json_encode(['success' => true, 'message' => 'Contract submitted.', 'id' => $submission_id, 'ref_no' => $ref_no]);
        exit;
    }

    if ($action === 'list') {
        $stmt = $pdo->prepare("
            SELECT cs.*, p.page_name, p.slug 
            FROM contract_submissions cs
            JOIN pages p ON cs.page_id = p.id
            WHERE cs.user_id = ?
            ORDER BY cs.submitted_at DESC
        ");
        $stmt->execute([$user_id]);
        $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $contracts]);
        exit;
    }

    if ($action === 'list_all') {
        // Fetch all submissions for admin
        $stmt = $pdo->prepare("
            SELECT cs.*, p.page_name, p.slug, u.name as user_name
            FROM contract_submissions cs
            JOIN pages p ON cs.page_id = p.id
            LEFT JOIN users u ON cs.user_id = u.id
            ORDER BY cs.submitted_at DESC
        ");
        $stmt->execute();
        $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'data' => $contracts]);
        exit;
    }

    if ($action === 'update_status') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $status = $input['status'] ?? null;

        if (!$id || !$status) {
            echo json_encode(['success' => false, 'message' => 'Missing ID or status']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE contract_submissions SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);

        echo json_encode(['success' => true, 'message' => 'Contract status updated to ' . $status]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
