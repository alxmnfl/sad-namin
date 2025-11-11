<?php
session_start();

// Check if admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "hardware_db");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = intval($_POST['product_id']);
    $new_stock = intval($_POST['new_stock']);
    
    if ($new_stock <= 0) {
        echo json_encode(['success' => false, 'message' => 'Stock quantity must be positive']);
        exit();
    }
    
    $query = "UPDATE products SET stock = stock + $new_stock WHERE id = $pid";
    
    if ($conn->query($query)) {
        // Get updated stock value
        $result = $conn->query("SELECT stock FROM products WHERE id = $pid");
        $product = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'Stock updated successfully',
            'new_stock' => $product['stock']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
