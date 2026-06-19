<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Not logged in']);
  exit;
}

if ($_SESSION['user_id'] === 0 || ($_SESSION['role'] ?? '') === 'admin') {
  http_response_code(403);
  echo json_encode(['error' => 'Admin accounts cannot add items to cart']);
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)($_POST['product_id'] ?? 0);
$qty = (int)($_POST['qty'] ?? 1);

if ($product_id <= 0 || $qty <= 0) {
  http_response_code(400);
  echo json_encode(["error" => "Invalid product ID or quantity"]);
  exit;
}

// Get product details
$stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
  http_response_code(404);
  echo json_encode(["error" => "Product not found"]);
  exit;
}

// Check if product already in cart
$check = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$res = $check->get_result();

if ($row = $res->fetch_assoc()) {
  // Update quantity
  $newQty = $row['quantity'] + $qty;
  $update = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
  $update->bind_param("ii", $newQty, $row['id']);
  $update->execute();
} else {
  // Insert new item
  $insert = $conn->prepare("INSERT INTO cart_items (user_id, product_id, product_name, price, quantity) VALUES (?, ?, ?, ?, ?)");
  $insert->bind_param("iisdi", $user_id, $product_id, $product['name'], $product['price'], $qty);
  $insert->execute();
}

// Return updated cart count
$res = $conn->prepare("SELECT SUM(quantity) AS count FROM cart_items WHERE user_id = ?");
$res->bind_param("i", $user_id);
$res->execute();
$resResult = $res->get_result();
$row = $resResult->fetch_assoc();

echo json_encode(["message" => "Added to cart", "count" => $row['count'] ?? 0]);
?>
