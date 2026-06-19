<?php
header('Content-Type: application/json');
session_start();
require_once 'db.php';

$username = trim($_POST['username'] ?? '');
$passRaw  = $_POST['password'] ?? '';

if (!$username || !$passRaw) {
    http_response_code(400);
    echo json_encode(['error' => 'Username and password required']);
    exit;
}

// Normal user/admin SELECT
$stmt = $conn->prepare("SELECT id, username, password, role, admin_role, shift_start, shift_end FROM users WHERE username = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();

if ($user && password_verify($passRaw, $user['password'])) {
    if ($user['role'] === 'admin') {
        $admin_role = $user['admin_role'] ?? '';
        $shift_start = $user['shift_start'] ?? null;
        $shift_end = $user['shift_end'] ?? null;

        // Verify shift hours for non-super_admin accounts (excluding username = 'admin')
        if (strtolower($user['username']) !== 'admin' && $admin_role !== 'super_admin' && !empty($shift_start) && !empty($shift_end)) {
            date_default_timezone_set('Asia/Colombo');
            $current = date('H:i:s');
            $is_valid = false;
            if ($shift_start <= $shift_end) {
                $is_valid = ($current >= $shift_start && $current <= $shift_end);
            } else {
                $is_valid = ($current >= $shift_start || $current <= $shift_end);
            }

            if (!$is_valid) {
                http_response_code(403);
                echo json_encode(['error' => "Access denied: Outside shift hours ($shift_start - $shift_end)."]);
                exit;
            }
        }

        $_SESSION['admin_role'] = $admin_role;
        $_SESSION['shift_start'] = $shift_start;
        $_SESSION['shift_end'] = $shift_end;
    } else {
        // Reset admin subrole/shift if normal user logs in
        $_SESSION['admin_role'] = null;
        $_SESSION['shift_start'] = null;
        $_SESSION['shift_end'] = null;
    }

    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];
    
    // Log the successful login
    $logStmt = $conn->prepare("INSERT INTO logins (username) VALUES (?)");
    if ($logStmt) {
        $logStmt->bind_param('s', $username);
        $logStmt->execute();
        $logStmt->close();
    }
    
    echo json_encode(['role' => $user['role']]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid username or password']);
}

$stmt->close();
$conn->close();