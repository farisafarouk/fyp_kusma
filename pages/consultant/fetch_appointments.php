<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

// 1. Session check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// 2. Find consultant ID using current session's user_id
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id FROM consultants WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['error' => 'Consultant not found']);
    exit();
}
$consultant_id = $res->fetch_assoc()['id'];

// 3. Fetch appointments linked to this consultant
$sql = "
    SELECT 
        a.id,
        a.customer_id,
        a.scheduled_date,
        a.scheduled_time,
        a.duration,
        a.appointment_mode,
        a.status,
        a.reason_for_appointment,
        a.feedback,
        a.rating,
        u.name AS customer_name,
        u.email AS customer_email
    FROM appointments a
    JOIN users u ON a.customer_id = u.id
    WHERE a.consultant_id = ?
    ORDER BY a.scheduled_date, a.scheduled_time
";

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
