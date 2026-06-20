<?php
header('Content-Type: application/json');
require_once __DIR__ . '/auth_helper.php';
checkShiftConstraint();

require_once __DIR__ . '/config.php';

try {
    // 1. Total Revenue (sum of total_amount of completed/non-cancelled orders)
    $revenueStmt = $pdo->query("SELECT SUM(total_amount) AS total FROM orders WHERE status != 'Cancelled'");
    $revenue = (float)($revenueStmt->fetchColumn() ?: 0.00);

    // 2. Total Orders count
    $ordersStmt = $pdo->query("SELECT COUNT(*) AS total FROM orders");
    $ordersCount = (int)$ordersStmt->fetchColumn();

    // 3. Total Products count
    $productsStmt = $pdo->query("SELECT COUNT(*) AS total FROM products");
    $productsCount = (int)$productsStmt->fetchColumn();

    // 4. Total Registered Customers count
    $usersStmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'");
    $usersCount = (int)$usersStmt->fetchColumn();

    // 5. Recent Orders (latest 5)
    $recentOrdersStmt = $pdo->query("
        SELECT o.id, o.user_id, o.total_amount, o.status, o.created_at, u.username 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        ORDER BY o.created_at DESC 
        LIMIT 5
    ");
    $recentOrders = $recentOrdersStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'stats' => [
            'revenue' => $revenue,
            'orders' => $ordersCount,
            'products' => $productsCount,
            'customers' => $usersCount
        ],
        'recentOrders' => $recentOrders
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'detail' => $e->getMessage()]);
}
?>
