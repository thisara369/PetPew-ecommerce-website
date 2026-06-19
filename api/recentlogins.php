<?php
// file: api/recentlogins.php
header('Content-Type: application/json');
require_once __DIR__ . '/auth_helper.php';
checkRolePermission([]); // Only super_admin

require_once __DIR__ . '/config.php';

$stmt = $pdo->query("SELECT username, login_time as time FROM logins ORDER BY login_time DESC LIMIT 10");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
