<?php
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

$key = "YOUR_SECRET_KEY";
$payload = [
    "id" => 1,              // Admin user ID
    "role" => "admin",      // Set role as admin
    "exp" => time() + 3600  // Token expires in 1 hour
];

$jwt = JWT::encode($payload, $key, 'HS256');
echo $jwt;
?>
