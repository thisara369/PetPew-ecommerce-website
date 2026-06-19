<?php
// file: api/orders.php
ini_set('display_errors',1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require 'db.php';
require_once __DIR__ . '/auth_helper.php';
checkRolePermission(['order_manager']);

$method = $_SERVER['REQUEST_METHOD'];

/* ---------- GET – list for admin ---------- */
if ($method === 'GET') {
    $rows = $conn->query("SELECT id,user_id,total_amount,status,tracking_number,
                                  created_at
                           FROM orders ORDER BY created_at DESC")
                 ->fetch_all(MYSQLI_ASSOC);
    echo json_encode($rows);
    exit;
}

/* ---------- PUT – update status / tracking ---------- */
if ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $d);
    $orderId = (int)($d['order_id'] ?? 0);
    $status  = $d['status']  ?? null;
    $note    = $d['note']    ?? null;
    $track   = $d['tracking_number'] ?? null;

    if (!$orderId || !$status) {
        http_response_code(400);
        echo json_encode(['error'=>'order_id & status required']); exit;
    }

    $sql = "UPDATE orders SET status=?, tracking_number=?, tracking_note=?, updated_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi",$status,$track,$note,$orderId);
    $stmt->execute();
    echo json_encode(['message'=>'Order updated ✔']);
    exit;
}

http_response_code(405);
echo json_encode(['error'=>'Method not allowed']);
