<?php
include 'db_connection.php';
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

function authenticate() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        try {
            $key = "YOUR_SECRET_KEY";
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($key, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["message" => "Access denied. Invalid token."]);
            exit();
        }
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Access denied. No token provided."]);
        exit();
    }
}

$user = authenticate();
$user_id = $user->id;

// Check if the user is an admin
$is_admin = $user->role === 'admin';

if ($is_admin) {
    // Admin: Fetch all orders
    $sql = "SELECT o.id, o.user_id, o.status, o.total_price, o.created_at, u.name AS user_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC";
    $stmt = $conn->prepare($sql);
} else {
    // Regular user: Fetch their orders
    $sql = "SELECT o.id, o.status, o.total_price, o.created_at
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($orders);

$stmt->close();
$conn->close();
?>
