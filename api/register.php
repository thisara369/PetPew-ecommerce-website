<?php
header('Content-Type: application/json');
require_once 'db.php';

$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email']    ?? '');
$passRaw  = $_POST['password']      ?? '';

// Validate required fields
if (!$username || !$email || !$passRaw) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

// Block reserved username
if (strtolower($username) === 'admin') {
    http_response_code(409);
    echo json_encode(['error' => 'That username is reserved']);
    exit;
}

// Basic email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid email address']);
    exit;
}

// Password minimum length
if (strlen($passRaw) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit;
}

// Check if username or email already exists
$check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$check->bind_param('ss', $username, $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    http_response_code(409);
    echo json_encode(['error' => 'Username or email already exists']);
    $check->close();
    $conn->close();
    exit;
}
$check->close();

// Hash password and insert
$hash = password_hash($passRaw, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param('sss', $username, $email, $hash);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['message' => 'Registration successful']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Registration failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();