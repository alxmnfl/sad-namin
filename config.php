<?php
/**
 * Database Configuration File
 * 
 * SETUP INSTRUCTIONS:
 * 1. Make sure XAMPP Apache and MySQL are running
 * 2. Import database.sql through phpMyAdmin
 * 3. Update the values below if your MySQL setup differs
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hardware_db');

// Create database connection
function getDatabaseConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// For backward compatibility with existing code
$conn = getDatabaseConnection();
?>
