<?php
require_once '../../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}

$user_id = $_SESSION['user_id'];

$response = [
  'subscription_status' => '-',
  'expiry_text' => '-',
  'recommendation_count' => 0,
  'upcoming_date' => '-',
  'consultant_name' => '-',
  'has_personal' => false,
  'has_business' => false,
  'has_education' => false
];

// Subscription
$stmt = $conn->prepare("SELECT subscription_status, subscription_expiry FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
  $response['subscription_status'] = ucfirst($row['subscription_status']);
  if ($row['subscription_expiry']) {
    $exp_date = new DateTime($row['subscription_expiry']);
    $now = new DateTime();
    $days = $now->diff($exp_date)->format('%r%a');
    $response['expiry_text'] = $days >= 0 ? "$days days left" : "Expired " . abs($days) . " days ago";
  }
}

// Recommendations (basic logic placeholder based on profile form)
$form_stmt = $conn->prepare("SELECT form_status FROM users WHERE id = ?");
$form_stmt->bind_param("i", $user_id);
$form_stmt->execute();
$form_stmt->bind_result($form_status);
if ($form_stmt->fetch() && $form_status === 'completed') {
  $response['recommendation_count'] = rand(3, 8); // Example placeholder count
}
$form_stmt->close();

// Next appointment
$stmt = $conn->prepare("SELECT a.scheduled_date, a.scheduled_time, u.name AS consultant FROM appointments a JOIN consultants c ON a.consultant_id = c.id JOIN users u ON c.user_id = u.id WHERE a.customer_id = ? AND a.status IN ('confirmed', 'pending') AND a.scheduled_date >= CURDATE() ORDER BY a.scheduled_date ASC, a.scheduled_time ASC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
  $response['upcoming_date'] = $row['scheduled_date'] . ' at ' . substr($row['scheduled_time'], 0, 5);
  $response['consultant_name'] = $row['consultant'];
}

// Completion checks
foreach ([
  'personal_details' => 'has_personal',
  'business_details' => 'has_business',
  'education_resources' => 'has_education'
] as $table => $key) {
  $stmt = $conn->prepare("SELECT id FROM $table WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->store_result();
  $response[$key] = $stmt->num_rows > 0;
  $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($response);
