<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    echo json_encode([]);
    exit;
}

// Step 1: Get consultant_id from session user_id
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id FROM consultants WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode([]);
    exit;
}
$consultant_id = $result->fetch_assoc()['id'];

// Step 2: Fetch appointments for this consultant
$sql = "SELECT a.id, a.customer_id, a.consultant_id, a.scheduled_date, a.scheduled_time,
               a.status, a.duration, a.appointment_mode, a.cancel_note,
               u.name AS customer_name, u.email AS customer_email
        FROM appointments a
        JOIN users u ON a.customer_id = u.id
        WHERE a.consultant_id = ?
        ORDER BY a.scheduled_date DESC, a.scheduled_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode($appointments);
?>