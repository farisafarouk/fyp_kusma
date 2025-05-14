<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get consultant ID and name
$stmt = $conn->prepare("SELECT id, name FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$consultantName = $user['name'];

$stmt = $conn->prepare("SELECT id FROM consultants WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$consultant = $stmt->get_result()->fetch_assoc();
$consultant_id = $consultant['id'] ?? 0;

$todayDate = date('Y-m-d');
$currentMonth = date('Y-m');

$todayAppointments = 0;
$newFeedback = 0;
$monthlyRating = 0;
$monthlyAppointments = 0;
$completedAppointments = 0;
$averageRating = 0;
$ratingSum = 0;
$ratingCount = 0;
$monthlyRatingSum = 0;
$monthlyRatingCount = 0;

$upcomingAppointments = [];
$recentFeedback = [];

// Get today's appointments
$stmt = $conn->prepare("SELECT customer_id, scheduled_time, appointment_mode, reason_for_appointment FROM appointments WHERE consultant_id = ? AND scheduled_date = ? ORDER BY scheduled_time ASC");
$stmt->bind_param("is", $consultant_id, $todayDate);
$stmt->execute();
$result = $stmt->get_result();
$todayAppointments = $result->num_rows;

while ($row = $result->fetch_assoc()) {
    $stmt2 = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $stmt2->bind_param("i", $row['customer_id']);
    $stmt2->execute();
    $cust = $stmt2->get_result()->fetch_assoc();
    $upcomingAppointments[] = [
        'customer_name' => $cust['name'],
        'time' => substr($row['scheduled_time'], 0, 5),
        'mode' => $row['appointment_mode'],
        'reason' => $row['reason_for_appointment'] ?? '-'
    ];
}

// Get completed feedback & ratings
$stmt = $conn->prepare("SELECT a.rating, a.feedback, a.scheduled_date, u.name as customer_name FROM appointments a JOIN users u ON a.customer_id = u.id WHERE a.consultant_id = ? AND a.feedback IS NOT NULL ORDER BY a.scheduled_date DESC LIMIT 3");
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$result = $stmt->get_result();
$newFeedback = $result->num_rows;

while ($row = $result->fetch_assoc()) {
    $recentFeedback[] = [
        'customer_name' => $row['customer_name'],
        'feedback' => $row['feedback'],
        'date' => $row['scheduled_date']
    ];
    if ($row['rating']) {
        $ratingSum += $row['rating'];
        $ratingCount++;
    }
}

// Monthly stats
$stmt = $conn->prepare("SELECT rating, status, scheduled_date FROM appointments WHERE consultant_id = ?");
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'completed') $completedAppointments++;

    if ($row['scheduled_date'] && strpos($row['scheduled_date'], $currentMonth) === 0) {
        $monthlyAppointments++;
        if ($row['rating']) {
            $monthlyRatingSum += $row['rating'];
            $monthlyRatingCount++;
        }
    }
}

$averageRating = $ratingCount ? $ratingSum / $ratingCount : 0;
$monthlyRating = $monthlyRatingCount ? $monthlyRatingSum / $monthlyRatingCount : 0;

// Final output
$response = [
    'consultantName' => $consultantName,
    'todayAppointments' => $todayAppointments,
    'newFeedback' => $newFeedback,
    'monthlyRating' => $monthlyRating,
    'monthlyAppointments' => $monthlyAppointments,
    'completedAppointments' => $completedAppointments,
    'averageRating' => $averageRating,
    'upcomingAppointments' => $upcomingAppointments,
    'recentFeedback' => $recentFeedback
];

echo json_encode($response);
