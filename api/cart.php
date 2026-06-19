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
  echo json_encode(['error' => 'Admin accounts do not have a cart']);
  exit;
}

$user_id = (int)$_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case 'GET':
    // Get cart items with product details
    $stmt = $conn->prepare("
      SELECT ci.id, ci.product_id, ci.quantity, p.name AS product_name, p.price, p.image
      FROM cart_items ci
      JOIN products p ON ci.product_id = p.id
      WHERE ci.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
      $items[] = [
        'id' => $row['id'],
        'product_id' => $row['product_id'],
        'product_name' => $row['product_name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'image' => $row['image']
      ];
    }

    header('Content-Type: application/json');
    echo json_encode($items);
    break;

  case 'POST':
    // Add product to cart (expects product_id and qty in POST body)
    $product_id = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));

    if ($product_id <= 0) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid product ID']);
      exit;
    }

    // Check if product exists
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
      http_response_code(404);
      echo json_encode(['error' => 'Product not found']);
      exit;
    }

    // Check if product already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
      // Update quantity
      $newQty = $row['quantity'] + $qty;
      $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
      $stmt->bind_param("ii", $newQty, $row['id']);
      $stmt->execute();
    } else {
      // Insert new cart item
      $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
      $stmt->bind_param("iii", $user_id, $product_id, $qty);
      $stmt->execute();
    }

    echo json_encode(['message' => 'Added to cart']);
    break;

  case 'PUT':
    // Update quantity (expects id and qty in URL-encoded body)
    parse_str(file_get_contents("php://input"), $put_vars);
    $id = (int)($put_vars['id'] ?? 0);
    $qty = max(1, (int)($put_vars['qty'] ?? 1));

    if ($id <= 0) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid cart item ID']);
      exit;
    }

    // Update quantity of cart item only if it belongs to this user
    $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $qty, $id, $user_id);
    $stmt->execute();

    echo json_encode(['message' => 'Quantity updated']);
    break;

  case 'DELETE':
    // Delete cart item (expects id in URL-encoded body)
    parse_str(file_get_contents("php://input"), $delete_vars);
    $id = (int)($delete_vars['id'] ?? 0);

    if ($id <= 0) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid cart item ID']);
      exit;
    }

    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();

    echo json_encode(['message' => 'Item removed']);
    break;

  default:
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    break;
}
?>
