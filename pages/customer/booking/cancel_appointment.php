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
$cancel_note = trim($input['reason'] ?? '');
$customer_id = $_SESSION['user_id'];

if (!$appointment_id || !$cancel_note) {
  echo json_encode(['success' => false, 'message' => 'Missing appointment ID or reason.']);
  exit();
}

// Verify ownership
$check = $conn->prepare("SELECT id FROM appointments WHERE id = ? AND customer_id = ? AND status IN ('pending', 'confirmed')");
$check->bind_param("ii", $appointment_id, $customer_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'Appointment not found or already finalized.']);
  exit();
}

// Cancel the appointment
$update = $conn->prepare("UPDATE appointments SET status = 'canceled', feedback = ?, updated_by = 'customer' WHERE id = ?");
$update->bind_param("si", $cancel_note, $appointment_id);

if ($update->execute()) {
  echo json_encode(['success' => true, 'message' => 'Appointment canceled.']);
} else {
  echo json_encode(['success' => false, 'message' => 'Error cancelling appointment.']);
}
