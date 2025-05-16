<?php
require_once '../../config/database.php'; // Adjust if needed

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if (!$name || !$email || !$subject || !$message) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields.']);
    exit();
  }

  $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("ssss", $name, $email, $subject, $message);

  if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
  } else {
    echo json_encode(['status' => 'error', 'message' => 'Database error. Please try again.']);
  }

  $stmt->close();
  $conn->close();
} else {
  echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
?>
