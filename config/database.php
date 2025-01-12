<?php
// Database Configuration
$host = 'localhost'; // Server name or IP address
$username = 'root';  // Your MySQL username
$password = '';      // Your MySQL password (leave empty if no password is set)
$database = 'fyp_kusma'; // Name of the database you want to connect to

// Establishing Connection
$conn = new mysqli($host, $username, $password, $database);

// Check for Connection Errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: Log successful connection for debugging (remove in production)
// error_log("Database connection successful.");
?>
