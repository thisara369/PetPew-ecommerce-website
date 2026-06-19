<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

/* ---------- Guard clauses ---------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'User not logged in']);
    exit;
}
$user_id = (int)$_SESSION['user_id'];

/* ---------- Input ---------- */
$payment_method = $_POST['payment_method'] ?? '';
if (!$payment_method) {
    echo json_encode(['success'=>false,'error'=>'Payment method required']);
    exit;
}

/* Card fields */
$card_name   = trim($_POST['card_name']   ?? '');
$card_number = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
$card_expiry = $_POST['card_expiry'] ?? '';
$card_cvv    = $_POST['card_cvv']    ?? '';

if ($payment_method === 'Credit/Debit Card') {
    if (!preg_match('/^\d{13,19}$/', $card_number) ||
        !preg_match('/^\d{2}\/\d{2}$/', $card_expiry) ||
        !preg_match('/^\d{3,4}$/',   $card_cvv) ||
        $card_name === '') {
        echo json_encode(['success'=>false,'error'=>'Invalid card details']);
        exit;
    }
}

/* ---------- Load cart with full product data ---------- */
$stmt = $conn->prepare("
    SELECT c.product_id,
           c.quantity,
           p.price,
           p.name,
           p.image                -- make sure your products table has this column
    FROM   cart_items AS c
    JOIN   products   AS p ON p.id = c.product_id
    WHERE  c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart = $stmt->get_result();

if (!$cart || $cart->num_rows === 0) {
    echo json_encode(['success'=>false,'error'=>'Cart is empty']);
    exit;
}

/* ---------- Build $items & total ---------- */
$total = 0.0;
$items = [];

while ($row = $cart->fetch_assoc()) {
    $lineTotal = $row['price'] * $row['quantity'];
    $total += $lineTotal;

    $items[] = [
        'id'    => $row['product_id'],
        'name'  => $row['name'],
        'qty'   => $row['quantity'],
        'price' => $row['price'],
        'image' => $row['image']         // <‑‑ now returned to front‑end
    ];
}

/* ---------- Transaction: orders, order_items, payments ---------- */
$conn->begin_transaction();
try {
    /* Insert into orders */
    $orderIns = $conn->prepare("
        INSERT INTO orders (user_id, payment_method, total_amount, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $orderIns->bind_param("isd", $user_id, $payment_method, $total);
    $orderIns->execute();
    $order_id = $orderIns->insert_id;

    /* Insert each order item */
    $itemIns = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($items as $it) {
        $itemIns->bind_param(
            "iiid",
            $order_id,
            $it['id'],
            $it['qty'],
            $it['price']
        );
        $itemIns->execute();
    }

    /* Insert payment (mask card) */
    $last4 = $card_number ? substr($card_number, -4) : null;
    $exp_m = $exp_y = null;
    if ($card_expiry) [ $exp_m, $exp_y ] = explode('/', $card_expiry);

    $payIns = $conn->prepare("
        INSERT INTO payments (order_id, method, card_holder, card_last4, exp_month, exp_year)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $payIns->bind_param(
        "isssss",
        $order_id,
        $payment_method,
        $card_name,
        $last4,
        $exp_m,
        $exp_y
    );
    $payIns->execute();

    /* Clear cart */
    $clr = $conn->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $clr->bind_param("i", $user_id);
    $clr->execute();

    $conn->commit();

    echo json_encode([
        'success'  => true,
        'order_id' => $order_id,
        'items'    => $items,
        'message'  => 'Order placed successfully'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false,'error'=>'Checkout failed']);
}
