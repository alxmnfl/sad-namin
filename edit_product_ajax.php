<?php
session_start();
require_once 'config.php';

// Check if admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$conn = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pid = intval($_POST['product_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $category = $conn->real_escape_string($_POST['category']);
    $price = floatval($_POST['price']);
    $description = $conn->real_escape_string($_POST['description']);
    
    // Handle optional image update
    $image_update_sql = "";
    $new_image_path = "";
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $img_name = basename($_FILES['image']['name']);
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir);
        $target_file = $target_dir . time() . "_" . $img_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_update_sql = ", image='" . $conn->real_escape_string($target_file) . "'";
            $new_image_path = $target_file;
        }
    }
    
    $query = "UPDATE products SET name='$name', category='$category', price='$price', description='$description' $image_update_sql WHERE id=$pid";
    
    if ($conn->query($query)) {
        // Get updated product data
        $result = $conn->query("SELECT * FROM products WHERE id=$pid");
        $product = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'message' => 'Product updated successfully',
            'product' => $product
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>
