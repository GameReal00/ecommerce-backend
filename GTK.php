<?php
require 'vendor/autoload.php';

header("Access-Control-Allow-Origin: http://localhost");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

use \Firebase\JWT\JWT;

$key = "YOUR_SECRET_KEY";
$payload = [
    "id" => 1,
    "role" => "admin", // Change to 'user' for non-admin
    "exp" => time() + 3600 // Token expiry (1 hour)
];

$jwt = JWT::encode($payload, $key, 'HS256');
echo $jwt;
?>
