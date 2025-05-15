<?php
session_start();
require_once '../../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
  exit();
}

$input = json_decode(file_get_contents("php://input"), true);
$appointment_id = $input['id'] ?? null;
$rating = $input['rating'] ?? null;
$feedback = trim($input['feedback'] ?? '');
$customer_id = $_SESSION['user_id'];

if (!$appointment_id || !$rating || !$feedback) {
  echo json_encode(['success' => false, 'message' => 'Missing rating or feedback.']);
  exit();
}

// Validate ownership and completion status
$check = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND customer_id = ? AND status = 'completed'");
$check->bind_param("ii", $appointment_id, $customer_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'Appointment not found or not eligible for feedback.']);
  exit();
}

// Update with feedback and rating
$update = $conn->prepare("UPDATE appointments SET feedback = ?, rating = ? WHERE id = ?");
$update->bind_param("sii", $feedback, $rating, $appointment_id);

if ($update->execute()) {
  echo json_encode(['success' => true, 'message' => 'Thank you for your feedback!']);
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to save feedback.']);
}
