<?php
// file: api/get_current_admin.php

header('Content-Type: application/json');
require_once __DIR__ . '/auth_helper.php';

// Verify that the user has a valid admin session
verifyAdminSession();

require_once __DIR__ . '/config.php';

try {
    $uid = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT id, username, email, role, admin_role, shift_start, shift_end, avatar FROM users WHERE id = :id");
    $stmt->execute([':id' => $uid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Admin profile not found.']);
        exit;
    }

    echo json_encode($user);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error fetching profile', 'detail' => $e->getMessage()]);
}
