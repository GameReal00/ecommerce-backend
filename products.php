<?php
// Include database connection
include 'db_connection.php';
require 'vendor/autoload.php';
use \Firebase\JWT\JWT;

//Authenticate user using JWT
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

// Handle CRUD operations
$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case 'GET':
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $sql = "SELECT * FROM Products WHERE id = $id";
            $result = mysqli_query($conn, $sql);
            $product = mysqli_fetch_assoc($result);
            echo json_encode($product);
        } else {
            $sql = "SELECT * FROM Products";
            $result = mysqli_query($conn, $sql);
            $products = mysqli_fetch_all($result, MYSQLI_ASSOC);
            echo json_encode($products);
        }
        break;
    case 'POST':
        if ($user->role == 'admin') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $sql = "INSERT INTO Products (name, description, price, stock) VALUES ('$name', '$description', '$price', '$stock')";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(["message" => "Product added successfully."]);
            } else {
                echo json_encode(["message" => "Error: " . mysqli_error($conn)]);
            }
        } else {
            http_response_code(403);
            echo json_encode(["message" => "Access denied. Admins only."]);
        }
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $put_vars);
        if ($user->role == 'admin' && isset($put_vars['id'])) {
            $id = $put_vars['id'];
            $name = $put_vars['name'];
            $description = $put_vars['description'];
            $price = $put_vars['price'];
            $stock = $put_vars['stock'];
            $sql = "UPDATE Products SET name = '$name', description = '$description', price = '$price', stock = '$stock' WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(["message" => "Product updated successfully."]);
            } else {
                echo json_encode(["message" => "Error: " . mysqli_error($conn)]);
            }
        } else {
            http_response_code(403);
            echo json_encode(["message" => "Access denied. Admins only."]);
        }
        break;
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $delete_vars);
        if ($user->role == 'admin' && isset($delete_vars['id'])) {
            $id = $delete_vars['id'];
            $sql = "DELETE FROM Products WHERE id = $id";
            if (mysqli_query($conn, $sql)) {
                echo json_encode(["message" => "Product deleted successfully."]);
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
