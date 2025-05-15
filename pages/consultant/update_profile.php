<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
  echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
  exit();
}

$user_id = $_SESSION['user_id'];

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$expertise = trim($_POST['expertise'] ?? '');
$rate = trim($_POST['rate_per_hour'] ?? '');

if (!$name || !$email || !$phone || !$expertise || !$rate) {
  echo json_encode(['success' => false, 'message' => 'All fields are required.']);
  exit();
}

$conn->begin_transaction();

try {
  $stmt1 = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
  $stmt1->bind_param("ssi", $name, $email, $user_id);
  $stmt1->execute();

$stmt2 = $conn->prepare("UPDATE consultants SET phone = ?, expertise = ?, rate_per_hour = ? WHERE user_id = ?");
$stmt2->bind_param("ssdi", $phone, $expertise, $rate, $user_id);
  $stmt2->execute();

  $conn->commit();
  echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
} catch (Exception $e) {
  $conn->rollback();
  echo json_encode(['success' => false, 'message' => 'Failed to update profile. Please try again.']);
}
