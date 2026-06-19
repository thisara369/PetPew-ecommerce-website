<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

function send($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    require_once __DIR__ . '/auth_helper.php';
    checkRolePermission(['product_manager']);
}

try {
    if ($method === 'GET') {
        $cat = $_GET['category'] ?? null;
        $stmt = $cat ?
            $pdo->prepare("SELECT * FROM products WHERE category = :cat ORDER BY created_at DESC") :
            $pdo->query("SELECT * FROM products ORDER BY created_at DESC");

        $cat ? $stmt->execute([':cat' => $cat]) : null;
        send($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    if ($method === 'POST') {
        foreach (['name', 'description', 'price', 'category'] as $f) {
            if (empty($_POST[$f])) send(['error' => "$f required"], 400);
        }

        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            send(['error' => 'Image upload failed'], 400);
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) send(['error' => 'Unsupported image type'], 400);

        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $newName = uniqid('prod_', true) . ".$ext";
        $destPath = $uploadDir . $newName;
        move_uploaded_file($_FILES['image']['tmp_name'], $destPath);

        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, category)
                               VALUES (:name, :description, :price, :image, :category)");
        $stmt->execute([
            ':name' => $_POST['name'],
            ':description' => $_POST['description'],
            ':price' => $_POST['price'],
            ':image' => 'uploads/' . $newName,
            ':category' => $_POST['category']
        ]);
        send(['message' => 'Product added', 'id' => $pdo->lastInsertId()], 201);
    }

    if ($method === 'PUT') {
        parse_str(file_get_contents('php://input'), $input);
        $id = (int)($input['id'] ?? 0);
        if (!$id) send(['error' => 'ID required'], 400);

        $fields = [];
        $params = [':id' => $id];
        if (!empty($input['price'])) {
            $fields[] = 'price = :price';
            $params[':price'] = $input['price'];
        }
        if (!empty($input['description'])) {
            $fields[] = 'description = :description';
            $params[':description'] = $input['description'];
        }

        if (!$fields) send(['error' => 'Nothing to update'], 400);
        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        send(['message' => 'Product updated']);
    }

    if ($method === 'DELETE') {
        parse_str(file_get_contents('php://input'), $input);
        $id = (int)($input['id'] ?? ($_GET['id'] ?? 0));
        if (!$id) send(['error' => 'id required'], 400);

        $row = $pdo->prepare("SELECT image FROM products WHERE id = :id");
        $row->execute([':id' => $id]);
        $img = $row->fetchColumn();
        if (!$img) send(['error' => 'Product not found'], 404);

        $pdo->prepare("DELETE FROM products WHERE id = :id")->execute([':id' => $id]);
        @unlink(__DIR__ . '/../' . $img);
        send(['message' => 'Product deleted']);
    }

    send(['error' => 'Method not allowed'], 405);

} catch (Throwable $e) {
    send(['error' => 'Server error', 'detail' => $e->getMessage()], 500);
}
