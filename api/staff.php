<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = "localhost";
$db   = "gms_db";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// ── GET — fetch all staff ────────────────────
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, username, fullname, address, phone, level, created_at FROM users");
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($staff);
}

// ── PUT — update staff ───────────────────────
elseif ($method === 'PUT') {
    $id   = $_GET['id'] ?? null;
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$id || !$data) {
        echo json_encode(["error" => "Invalid data"]);
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET 
        username = :username,
        fullname = :fullname,
        address  = :address,
        phone    = :phone,
        level    = :level
        WHERE id = :id");

    $stmt->execute([
        ':username' => $data['username'],
        ':fullname' => $data['fullname'],
        ':address'  => $data['address'],
        ':phone'    => $data['phone'],
        ':level'    => $data['level'],
        ':id'       => $id
    ]);

    echo json_encode(["message" => "Staff updated successfully"]);
}

// ── DELETE — delete staff ────────────────────
elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;

    if (!$id) {
        echo json_encode(["error" => "ID required"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(["message" => "Staff deleted successfully"]);
}

else {
    echo json_encode(["error" => "Method not allowed"]);
}
?>