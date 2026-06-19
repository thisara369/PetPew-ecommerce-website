<?php
// file: api/get_order.php
ini_set('display_errors',1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) { http_response_code(401); echo json_encode(['error'=>'Not logged in']); exit; }

$orderId = (int)($_GET['id'] ?? 0);
$userId  = $_SESSION['user_id'];

$sql = "SELECT id,status,total_amount,tracking_number,tracking_note,created_at
        FROM orders WHERE id=? AND user_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii",$orderId,$userId);
$stmt->execute();
$res = $stmt->get_result();
if (!$row = $res->fetch_assoc()) { http_response_code(404); echo json_encode(['error'=>'Order not found']); exit; }

$items = $conn->query("
    SELECT p.name, oi.quantity, oi.price
    FROM order_items oi JOIN products p ON p.id=oi.product_id
    WHERE oi.order_id=$orderId
")->fetch_all(MYSQLI_ASSOC);

$row['items'] = $items;
echo json_encode($row);
