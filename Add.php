<?php
include 'db.php';

// Get input
$name = $_POST['name'];
$description = $_POST['description'];
$price = $_POST['price'];
$stock = $_POST['stock'];

$sql = "INSERT INTO products (name, description, price, stock, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssdi", $name, $description, $price, $stock);

if ($stmt->execute()) {
    echo json_encode(["message" => "Product added successfully"]);
} else {
    echo json_encode(["message" => "Error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
