<?php
session_start();
require_once 'config.php';

// ✅ Database Connection
$conn = getDatabaseConnection();

// ✅ Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Escape user inputs to prevent SQL injection
    $fname     = $conn->real_escape_string($_POST['fname']);
    $lname     = $conn->real_escape_string($_POST['lname']);
    $address   = $conn->real_escape_string($_POST['address']);
    $email     = $conn->real_escape_string($_POST['email']);
    $username  = $conn->real_escape_string($_POST['username']);
    $password  = $conn->real_escape_string($_POST['password']);
    $role      = 'customer'; // Default role for new users

    // ✅ Check if username already exists
    $check = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($check->num_rows > 0) {
        echo "<script>
                alert('Username already exists.');
                window.location='index.php';
              </script>";
        exit();
    }

    // ✅ Insert new user into database
    $sql = "INSERT INTO users (fname, lname, address, email, username, password, role) 
            VALUES ('$fname', '$lname', '$address', '$email', '$username', '$password', '$role')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Registration successful! Please log in.');
                window.location='index.php';
              </script>";
        exit();
    } else {
        echo "<script>
                alert('Registration failed. Please try again.');
                window.location='index.php';
              </script>";
    }
}

// ✅ Close connection
$conn->close();
?>
