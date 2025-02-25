<?php
require 'vendor/autoload.php';
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
