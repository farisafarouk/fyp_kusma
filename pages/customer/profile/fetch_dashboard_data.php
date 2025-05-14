<?php
session_start();
require_once '../../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}

$user_id = $_SESSION['user_id'];

$response = [
  'recommendation_count' => '-',
  'upcoming_date' => '-',
  'has_personal' => false,
  'has_business' => false,
  'has_education' => false
];

// --- Recommendation Count ---
// Count logic simulated from recommendations.php (e.g., all forms completed)
$rec_check = $conn->prepare("SELECT form_status FROM users WHERE id = ?");
$rec_check->bind_param("i", $user_id);
$rec_check->execute();
$rec_check->bind_result($form_status);
if ($rec_check->fetch() && $form_status === 'completed') {
  $response['recommendation_count'] = 'Available';
} else {
  $response['recommendation_count'] = 'Incomplete';
}
$rec_check->close();

// --- Upcoming Appointment ---
$appt = $conn->prepare("SELECT scheduled_date FROM appointments WHERE customer_id = ? AND status IN ('pending','confirmed') AND scheduled_date >= CURDATE() ORDER BY scheduled_date ASC LIMIT 1");
$appt->bind_param("i", $user_id);
$appt->execute();
$appt->bind_result($next_date);
if ($appt->fetch()) {
  $response['upcoming_date'] = $next_date;
}
$appt->close();

// --- Profile Checks ---
$check_personal = $conn->prepare("SELECT id FROM personal_details WHERE user_id = ? LIMIT 1");
$check_personal->bind_param("i", $user_id);
$check_personal->execute();
$check_personal->store_result();
$response['has_personal'] = $check_personal->num_rows > 0;
$check_personal->close();

$check_business = $conn->prepare("SELECT id FROM business_details WHERE user_id = ? LIMIT 1");
$check_business->bind_param("i", $user_id);
$check_business->execute();
$check_business->store_result();
$response['has_business'] = $check_business->num_rows > 0;
$check_business->close();

$check_edu = $conn->prepare("SELECT id FROM edu_resources WHERE user_id = ? LIMIT 1");
$check_edu->bind_param("i", $user_id);
$check_edu->execute();
$check_edu->store_result();
$response['has_education'] = $check_edu->num_rows > 0;
$check_edu->close();

echo json_encode($response);
