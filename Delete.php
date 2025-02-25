<?php
include 'db.php';

parse_str(file_get_contents("php://input"), $_DELETE);
$id = $_DELETE['id'];

$sql = "DELETE FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["message" => "Product deleted successfully"]);
} else {
    echo json_encode(["message" => "Error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
