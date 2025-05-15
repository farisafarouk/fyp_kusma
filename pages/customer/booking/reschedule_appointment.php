<?php
session_start();
require_once '../../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
  exit();
}

$input = json_decode(file_get_contents("php://input"), true);
$original_id = $input['original_id'] ?? null;
$new_schedule_id = $input['new_schedule_id'] ?? null;
$customer_id = $_SESSION['user_id'];

if (!is_numeric($original_id) || !is_numeric($new_schedule_id)) {
  echo json_encode(['success' => false, 'message' => 'Missing appointment or schedule ID.']);
  exit();
}

// Validate original appointment
$check = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND customer_id = ? AND status = 'confirmed'");
$check->bind_param("ii", $original_id, $customer_id);
$check->execute();
$original_result = $check->get_result();
if ($original_result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'Original appointment not found or not confirmed.']);
  exit();
}
$original = $original_result->fetch_assoc();

// Validate new schedule
$schedule = $conn->prepare("SELECT * FROM schedules WHERE id = ?");
$schedule->bind_param("i", $new_schedule_id);
$schedule->execute();
$schedule_result = $schedule->get_result();
if ($schedule_result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'New schedule not found.']);
  exit();
}
$new = $schedule_result->fetch_assoc();

// Check conflict
$conflict = $conn->prepare("SELECT id FROM appointments WHERE consultant_id = ? AND scheduled_date = ? AND scheduled_time = ? AND status IN ('pending', 'confirmed')");
$conflict->bind_param("iss", $new['consultant_id'], $new['date'], $new['start_time']);
$conflict->execute();
if ($conflict->get_result()->num_rows > 0) {
  echo json_encode(['success' => false, 'message' => 'This new slot is already booked.']);
  exit();
}

// Calculate new duration
$duration = (strtotime($new['end_time']) - strtotime($new['start_time'])) / 60;

// Insert new appointment
$insert = $conn->prepare("INSERT INTO appointments (consultant_id, customer_id, scheduled_date, scheduled_time, duration, appointment_mode, status, created_at, rescheduled_from) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)");
$insert->bind_param("iissisi", $new['consultant_id'], $customer_id, $new['date'], $new['start_time'], $duration, $new['appointment_mode'], $original_id);

if ($insert->execute()) {
  // Cancel original appointment
  $cancel = $conn->prepare("UPDATE appointments SET status = 'canceled', updated_by = 'customer' WHERE id = ?");
  $cancel->bind_param("i", $original_id);
  $cancel->execute();

  echo json_encode(['success' => true, 'message' => 'Appointment rescheduled successfully.']);
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to reschedule appointment.']);
}
