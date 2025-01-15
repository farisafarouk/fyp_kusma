<?php
require_once '../config/database.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form inputs
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $subject = $conn->real_escape_string($_POST['subject']);
    $message = $conn->real_escape_string($_POST['message']);

    // Insert form data into the database
    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";

    if ($conn->query($sql) === TRUE) {
        echo "OK";
    } else {
        echo "Error: " . $conn->error;
    }
    

    // Close the database connection
    $conn->close();
}
?>
