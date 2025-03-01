<?php
include 'db.php';
header("Access-Control-Allow-Origin: http://localhost");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

parse_str(file_get_contents("php://input"), $_PUT);
$id = $_PUT['id'];
$name = $_PUT['name'];
$description = $_PUT['description'];
$price = $_PUT['price'];
$stock = $_PUT['stock'];

$sql = "UPDATE products SET name = ?, description = ?, price = ?, stock = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdii", $name, $description, $price, $stock, $id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Product updated successfully"]);
} else {
    echo json_encode(["message" => "Error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
