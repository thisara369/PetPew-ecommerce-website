<?php
header('Content-Type: application/json');
require_once 'db.php'; // use db.php instead of config.php
session_start();

if (empty($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$uid   = $_SESSION['user_id'];
$full  = $_POST['full_name'] ?? null;
$age   = $_POST['age'] ?? null;

// Handle avatar upload
$avatarSql = '';
$params    = [$full, $age, $uid];
$types     = 'sii';

if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
  $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
  $new = uniqid('av_', true) . ".$ext";
  $dir = __DIR__ . '/../uploads/avatars/';
  if (!is_dir($dir)) mkdir($dir, 0777, true);
  move_uploaded_file($_FILES['avatar']['tmp_name'], $dir . $new);
  $avatarPath = "uploads/avatars/$new";
  $avatarSql  = ', avatar = ?';
  $params     = [$full, $age, $avatarPath, $uid];
  $types      = 'sisi';
}

$sql = "UPDATE users SET full_name=?, age=?$avatarSql WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['message' => 'updated']);
