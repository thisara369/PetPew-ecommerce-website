<?php
// file: api/update_avatar.php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/db.php'; // Uses mysqli ($conn)

$uid = (int)$_SESSION['user_id'];

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo json_encode(['error' => 'Unsupported image format']);
        exit;
    }

    $new_name = uniqid('av_', true) . ".$ext";
    $dir = __DIR__ . '/../uploads/avatars/';
    
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $new_name)) {
        $avatar_path = "uploads/avatars/$new_name";
        
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param('si', $avatar_path, $uid);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
        echo json_encode(['message' => 'Avatar updated successfully', 'avatar' => $avatar_path]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to move uploaded file']);
        exit;
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error occurred']);
    exit;
}
