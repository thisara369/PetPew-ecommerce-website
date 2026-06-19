<?php
// file: api/place_order.php

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

if (
    !isset($data['payment_method'], $data['total_amount'], $data['items']) ||
    !is_array($data['items']) || count($data['items']) === 0
) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid order data']);
    exit;
}

$user_id = $_SESSION['user_id'];
$payment_method = $conn->real_escape_string($data['payment_method']);
$total_amount = floatval($data['total_amount']);
$items = $data['items'];

$stmt = $conn->prepare("INSERT INTO orders (user_id, payment_method, total_amount, status, created_at) VALUES (?, ?, ?, 'Pending', NOW())");
$stmt->bind_param("isd", $user_id, $payment_method, $total_amount);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to place order']);
    exit;
}

$order_id = $conn->insert_id;

$stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

foreach ($items as $item) {
    $product_id = (int)$item['id'];
    $quantity = (int)$item['quantity'];

    $product_stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
    $product_stmt->bind_param("i", $product_id);
    $product_stmt->execute();
    $product_result = $product_stmt->get_result();
    if ($product_result->num_rows === 0) continue;

    $price = $product_result->fetch_assoc()['price'];

    $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price);
    $stmt_item->execute();
}

echo json_encode(['message' => 'Order placed successfully', 'order_id' => $order_id]);
exit;
?>
