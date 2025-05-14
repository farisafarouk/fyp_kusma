<?php
session_start();
require_once '../../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
  exit();
}

$input = json_decode(file_get_contents("php://input"), true);
$schedule_id = $input['slot_id'] ?? null;
$customer_id = $_SESSION['user_id'];

if (!$schedule_id || !is_numeric($schedule_id)) {
  echo json_encode(['success' => false, 'message' => 'Invalid schedule ID.']);
  exit();
}

// Check if schedule exists and fetch details
$schedule_stmt = $conn->prepare("SELECT id, consultant_id, date, start_time, end_time, appointment_mode FROM schedules WHERE id = ?");
$schedule_stmt->bind_param("i", $schedule_id);
$schedule_stmt->execute();
$schedule_result = $schedule_stmt->get_result();

if ($schedule_result->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'Schedule not found.']);
  exit();
}

$schedule = $schedule_result->fetch_assoc();

// Check if consultant already has an appointment at this schedule
$check_stmt = $conn->prepare("SELECT id FROM appointments WHERE consultant_id = ? AND scheduled_date = ? AND scheduled_time = ? AND status IN ('pending', 'confirmed')");
$check_stmt->bind_param("iss", $schedule['consultant_id'], $schedule['date'], $schedule['start_time']);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
  echo json_encode(['success' => false, 'message' => 'This slot is already booked.']);
  exit();
}

// Calculate duration in minutes
$start_timestamp = strtotime($schedule['start_time']);
$end_timestamp = strtotime($schedule['end_time']);
$duration = ($end_timestamp - $start_timestamp) / 60;

if ($duration <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid time range.']);
  exit();
}

// Insert into appointments
$insert_stmt = $conn->prepare("INSERT INTO appointments (consultant_id, customer_id, scheduled_date, scheduled_time, duration, appointment_mode, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
$insert_stmt->bind_param("iissis", $schedule['consultant_id'], $customer_id, $schedule['date'], $schedule['start_time'], $duration, $schedule['appointment_mode']);

if ($insert_stmt->execute()) {
  echo json_encode(['success' => true, 'message' => 'Appointment successfully booked.']);
} else {
  echo json_encode(['success' => false, 'message' => 'Database error while booking appointment.']);
}
