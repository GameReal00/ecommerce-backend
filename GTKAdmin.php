<?php
require 'vendor/autoload.php';
header("Access-Control-Allow-Origin: http://localhost");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

use \Firebase\JWT\JWT;

$key = "GameReal_game.00";
$payload = [
    "id" => 1,              // Admin user ID
    "role" => "admin",      // Set role as admin
    "exp" => time() + 3600  // Token expires in 1 hour
];

$jwt = JWT::encode($payload, $key, 'HS256');
echo json_encode([
    "message" => "JWT generated successfully.",
    "token" => $jwt
]);
?>
