<?php
header('Content-Type: application/json');
require_once 'db.php'; 
session_start();

if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$uid = $_SESSION['user_id'];

// Get avatar file path
$res = $conn->query("SELECT avatar FROM users WHERE id = $uid");
if ($row = $res->fetch_assoc()) {
  if ($row['avatar'] !== 'uploads/avatars/default.png') {
    @unlink(__DIR__ . '/../' . $row['avatar']);
  }
}

// Delete user record
$conn->query("DELETE FROM users WHERE id = $uid");

$conn->close();
session_unset();
session_destroy();

echo json_encode(['message' => 'deleted']);
