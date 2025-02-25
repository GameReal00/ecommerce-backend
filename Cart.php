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

// Handle cart operations
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'POST': // Add to cart or update quantity
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        $user_id = $user->id;

        if ($quantity <= 0) {
            echo json_encode(["message" => "Quantity must be greater than 0."]);
            exit();
        }

        $sql = "INSERT INTO Cart (user_id, product_id, quantity) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE quantity = quantity + ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Product added to cart."]);
        } else {
            echo json_encode(["message" => "Error: " . $stmt->error]);
        }
        $stmt->close();
        break;

    case 'GET': // View cart items
        $user_id = $user->id;
        $sql = "SELECT Cart.id, Products.name, Products.description, Products.price, Cart.quantity
                FROM Cart
                JOIN Products ON Cart.product_id = Products.id
                WHERE Cart.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cartItems = $result->fetch_all(MYSQLI_ASSOC);

        echo json_encode($cartItems);
        $stmt->close();
        break;

    case 'DELETE': // Remove item from cart
        parse_str(file_get_contents("php://input"), $delete_vars);
        $product_id = intval($delete_vars['product_id']);
        $user_id = $user->id;

        $sql = "DELETE FROM Cart WHERE user_id = ? AND product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $product_id);

        if ($stmt->execute()) {
            echo json_encode(["message" => "Product removed from cart."]);
        } else {
            echo json_encode(["message" => "Error: " . $stmt->error]);
        }
        $stmt->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed."]);
        break;
}

mysqli_close($conn);
?>
