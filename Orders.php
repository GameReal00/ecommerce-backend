<?php
// Include database connection
include 'db_connection.php';
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

// Authenticate user using JWT
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

// Handle order operations
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'POST':
        $user_id = $user->id;
        $sql = "SELECT SUM(Products.price * Cart.quantity) AS total_price
                FROM Cart
                JOIN Products ON Cart.product_id = Products.id
                WHERE Cart.user_id = $user_id";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $total_price = $row['total_price'];
        $sql = "INSERT INTO Orders (user_id, total_price) VALUES ('$user_id', '$total_price')";
        if (mysqli_query($conn, $sql)) {
            $order_id = mysqli_insert_id($conn);
            $sql = "DELETE FROM Cart WHERE user_id = $user_id";
            mysqli_query($conn, $sql);
            echo json_encode(["message" => "Order placed successfully.", "order_id" => $order_id]);
        } else {
            echo json_encode(["message" => "Error: " . mysqli_error($conn)]);
        }
        break;
    case 'GET':
        if ($user->role == 'admin') {
            $sql = "SELECT * FROM Orders";
        } else {
            $user_id = $user->id;
            $sql = "SELECT * FROM Orders WHERE user_id = $user_id";
        }
        $result = mysqli_query($conn, $sql);
        $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($orders);
        break;
    case 'PUT':
        if ($user->role == 'admin') {
           parse_str(file_get_contents("php://input"), $put_vars);
           $order_id = isset($put_vars['id']) ? intval($put_vars['id']) : 0;
           $status = isset($put_vars['status']) ? trim($put_vars['status']) : '';

            $sql = "UPDATE Orders SET status = '$status' WHERE id = $order_id";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(["message" => "Order status updated successfully."]);
            } else {
                echo json_encode(["message" => "Error: " . mysqli_error($conn)]);
            }
        } else {
            http_response_code(403);
            echo json_encode(["message" => "Access denied. Admins only."]);
        }
        break;
    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        break;
}

mysqli_close($conn);
?>
