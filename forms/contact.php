<?php
require_once '../config/database.php';

header('Content-Type: text/plain'); // Important for validate.js to parse properly

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $subject = $conn->real_escape_string($_POST['subject'] ?? '');
    $message = $conn->real_escape_string($_POST['message'] ?? '');

    if (!$name || !$email || !$subject || !$message) {
        echo "Missing required fields.";
        exit();
    }

    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Database error: " . $conn->error;
        exit();
    }

    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    if ($stmt->execute()) {
        echo "OK";
    } else {
        echo "Error saving message.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "Invalid request method.";
}
?>
