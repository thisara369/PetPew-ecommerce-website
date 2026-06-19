<?php
$host = 'localhost';
$db   = 'petpew';
$user = 'root';
$pass = ''; // your MySQL password
$charset = 'utf8mb4';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(['error' => 'Database connection failed']);
  exit;
}
