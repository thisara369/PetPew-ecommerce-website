<?php
// file: api/update_order_status.php

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require 'db.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['order_id'], $data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing order_id or status']);
    exit;
}

$order_id = (int)$data['order_id'];
$new_status = $data['status'];

// Verify order belongs to user
$stmt_check = $conn->prepare("SELECT user_id FROM orders WHERE id = ?");
$stmt_check->bind_param("i", $order_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Order not found']);
    exit;
}

$order_owner = $result_check->fetch_assoc()['user_id'];
if ($order_owner != $_SESSION['user_id']) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
$stmt->bind_param("si", $new_status, $order_id);

if ($stmt->execute()) {
    echo json_encode(['message' => 'Order status updated']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update order status']);
}
exit;
?>
