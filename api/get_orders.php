<?php
// file: api/get_orders.php
declare(strict_types=1);
ini_set('display_errors', 0);          // hide warnings from client
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

/* ------ include DB connection ------ */
require_once __DIR__ . '/db.php';   // if db.php is inside api/
# require_once dirname(__DIR__) . '/db.php'; // ← use this instead if db.php is ONE level above

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId = (int) $_SESSION['user_id'];

/* ------ fetch orders ------ */
$stmt = $conn->prepare(
    "SELECT id, status, total_amount, created_at, tracking_number
     FROM orders
     WHERE user_id = ?
     ORDER BY created_at DESC"
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orderId = $row['id'];

    $itemStmt = $conn->prepare(
        "SELECT p.name, oi.quantity, oi.price
         FROM order_items oi
         JOIN products p ON oi.product_id = p.id
         WHERE oi.order_id = ?"
    );
    $itemStmt->bind_param('i', $orderId);
    $itemStmt->execute();
    $items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $row['items'] = $items;
    $orders[]     = $row;
}

echo json_encode($orders, JSON_UNESCAPED_UNICODE);
