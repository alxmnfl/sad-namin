<?php
session_start();
$conn = new mysqli("localhost", "root", "", "hardware_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION['username']; // or change to numeric id if needed
$total_amount = $data['total'];
$items = $data['items'];

$conn->query("INSERT INTO transactions (user_id, total_amount, transaction_date) VALUES ('$user_id', '$total_amount', NOW())");
$transaction_id = $conn->insert_id;

// Optional: Save transaction items (recommended)
foreach ($items as $item) {
    $pid = $item['id'];
    $qty = $item['qty'];
    $price = $item['price'];
    $conn->query("INSERT INTO transaction_items (transaction_id, product_id, quantity, price) VALUES ('$transaction_id', '$pid', '$qty', '$price')");
    // Deduct from stock
    $conn->query("UPDATE products_ko SET stock = stock - $qty WHERE id = $pid");
}

echo json_encode(["status" => "success"]);
