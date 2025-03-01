<?php

require 'vendor/autoload.php';
require 'db_connection.php';
use \Stripe\Stripe;
use \Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: http://localhost");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

// Define a consistent secret key
$secret_key = "GameReal_game.00";

// Authentication function
function authenticate($secret_key) {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        try {
            return JWT::decode($token, new \Firebase\JWT\Key($secret_key, 'HS256'));
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["message" => "Access denied. Invalid token.", "error" => $e->getMessage()]);
            exit();
        }
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Access denied. No token provided."]);
        exit();
    }
}

// Authenticate user
$user = authenticate($secret_key);
$user_id = $user->id;

// Get input data
$data = json_decode(file_get_contents("php://input"), true);
$order_id = isset($data['order_id']) ? intval($data['order_id']) : 0;

// Validate order ID
if ($order_id <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input. Order ID is required."]);
    exit();
}

// Get order details
$sql = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

// Check if order exists
if (!$order) {
    http_response_code(404);
    echo json_encode(["message" => "Order not found."]);
    exit();
}

// Validate order total
$total_price = $order['total_price'] ?? 0;
if ($total_price <= 0) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid order total."]);
    exit();
}

// Check Stripe's minimum amount (50 cents)
if ($total_price < 0.50) {
    http_response_code(400);
    echo json_encode(["message" => "Order total must be at least $0.50."]);
    exit();
}

// Stripe integration
Stripe::setApiKey("sk_test_51QtDbX4cI1Ws4HrcPEa7OHbnGlcEthFSWM2pFAhkxHk6i9cHttmq1z7tYEfQkynAY5pikoyHFRoEJQNBFdDB9DCk005dQMolIh");

try {
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => intval($total_price * 100),  // Convert to cents
        'currency' => 'usd',
        'metadata' => ['order_id' => $order_id]
    ]);

    echo json_encode([
        "message" => "Payment intent created successfully.",
        "client_secret" => $paymentIntent->client_secret
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "message" => "Payment failed",
        "error" => $e->getMessage()
    ]);
}
?>