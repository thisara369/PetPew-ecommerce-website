<?php
// file: api/users.php
header('Content-Type: application/json');

require_once __DIR__ . '/auth_helper.php';
checkRolePermission([]); // Restrict to super_admin only!

require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    // 1. GET - List users
    if ($method === 'GET') {
        $stmt = $pdo->query("SELECT id, username, email, role, admin_role, shift_start, shift_end, avatar, created_at FROM users ORDER BY role ASC, created_at DESC");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // 2. POST - Create new user or admin
    if ($method === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $passRaw  = $_POST['password'] ?? '';
        $role     = trim($_POST['role'] ?? 'user');
        $admin_role  = trim($_POST['admin_role'] ?? '');
        $shift_start = trim($_POST['shift_start'] ?? '');
        $shift_end   = trim($_POST['shift_end'] ?? '');

        if (!$username || !$email || !$passRaw) {
            http_response_code(400);
            echo json_encode(['error' => 'Username, email, and password are required']);
            exit;
        }

        if (strtolower($username) === 'admin') {
            http_response_code(409);
            echo json_encode(['error' => 'That username is reserved']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid email address']);
            exit;
        }

        if (strlen($passRaw) < 6) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be at least 6 characters']);
            exit;
        }

        if (!in_array($role, ['user', 'admin'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid role']);
            exit;
        }

        // Check duplicates
        $check = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
        $check->execute([':u' => $username, ':e' => $email]);
        if ($check->fetch()) {
            http_response_code(409);
            echo json_encode(['error' => 'Username or email already exists']);
            exit;
        }

        $hash = password_hash($passRaw, PASSWORD_DEFAULT);

        // Sub-roles and shifts are null for normal users
        $db_admin_role = ($role === 'admin') ? $admin_role : null;
        $db_shift_start = ($role === 'admin' && $shift_start !== '') ? $shift_start : null;
        $db_shift_end = ($role === 'admin' && $shift_end !== '') ? $shift_end : null;

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, admin_role, shift_start, shift_end) 
                               VALUES (:username, :email, :password, :role, :admin_role, :shift_start, :shift_end)");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hash,
            ':role' => $role,
            ':admin_role' => $db_admin_role,
            ':shift_start' => $db_shift_start,
            ':shift_end' => $db_shift_end
        ]);

        echo json_encode(['message' => 'User created successfully']);
        exit;
    }

    // 3. PUT - Update user role & shift parameters
    if ($method === 'PUT') {
        parse_str(file_get_contents("php://input"), $input);
        $id = (int)($input['id'] ?? 0);
        $role = trim($input['role'] ?? '');
        $admin_role = trim($input['admin_role'] ?? '');
        $shift_start = trim($input['shift_start'] ?? '');
        $shift_end = trim($input['shift_end'] ?? '');

        if (!$id || !in_array($role, ['user', 'admin'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid parameters']);
            exit;
        }

        // Fetch user from DB to verify constraints
        $stmt_user = $pdo->prepare("SELECT username FROM users WHERE id = :id");
        $stmt_user->execute([':id' => $id]);
        $target_user = $stmt_user->fetch();

        if (!$target_user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        // Prevent self-demotion (changing system role from admin to user)
        if ($id === (int)$_SESSION['user_id'] && $role !== 'admin') {
            http_response_code(400);
            echo json_encode(['error' => 'You cannot demote your own admin account to a standard user']);
            exit;
        }

        // Sub-roles and shifts are null for normal users
        $db_admin_role = ($role === 'admin') ? $admin_role : null;
        $db_shift_start = ($role === 'admin' && $shift_start !== '') ? $shift_start : null;
        $db_shift_end = ($role === 'admin' && $shift_end !== '') ? $shift_end : null;

        $stmt = $pdo->prepare("UPDATE users SET role = :role, admin_role = :admin_role, shift_start = :shift_start, shift_end = :shift_end WHERE id = :id");
        $stmt->execute([
            ':role' => $role,
            ':admin_role' => $db_admin_role,
            ':shift_start' => $db_shift_start,
            ':shift_end' => $db_shift_end,
            ':id' => $id
        ]);

        echo json_encode(['message' => 'User updated successfully']);
        exit;
    }

    // 4. DELETE - Delete user
    if ($method === 'DELETE') {
        parse_str(file_get_contents("php://input"), $input);
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));

        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID required']);
            exit;
        }

        // Fetch user from DB to verify constraints
        $stmt_user = $pdo->prepare("SELECT username FROM users WHERE id = :id");
        $stmt_user->execute([':id' => $id]);
        $target_user = $stmt_user->fetch();

        if (!$target_user) {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
            exit;
        }

        // Prevent self-deletion
        if ($id === (int)$_SESSION['user_id']) {
            http_response_code(400);
            echo json_encode(['error' => 'You cannot delete your own account']);
            exit;
        }

        // Prevent deleting primary 'admin' account
        if ($target_user['username'] === 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'The root admin account cannot be deleted']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['message' => 'User deleted successfully']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'detail' => $e->getMessage()]);
}
?>
