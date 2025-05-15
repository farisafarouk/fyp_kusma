<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get consultant ID from user ID
$stmt = $conn->prepare("SELECT id FROM consultants WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$consultant = $result->fetch_assoc();
$consultant_id = $consultant['id'] ?? 0;

// Initialize arrays
$months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
$monthlyCounts = array_fill(0, 12, 0);
$ratingBreakdown = [0, 0, 0, 0, 0]; // Index 0 = 5 stars, 4, ..., 1
$totalAppointments = 0;
$completedAppointments = 0;
$totalRating = 0;
$ratingCount = 0;

// Query all completed appointments with ratings
$query = "SELECT scheduled_date, rating, status FROM appointments WHERE consultant_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $totalAppointments++;
    $monthIndex = (int)date('n', strtotime($row['scheduled_date'])) - 1;
    $monthlyCounts[$monthIndex]++;

    if ($row['status'] === 'completed') {
        $completedAppointments++;
    }

    if (!is_null($row['rating'])) {
        $rating = (int)$row['rating'];
        if ($rating >= 1 && $rating <= 5) {
            $ratingBreakdown[5 - $rating]++;
            $totalRating += $rating;
            $ratingCount++;
        }
    }
}

$averageRating = $ratingCount > 0 ? $totalRating / $ratingCount : 0;

// Return JSON
echo json_encode([
    'months' => $months,
    'monthlyCounts' => $monthlyCounts,
    'ratingBreakdown' => $ratingBreakdown,
    'totalAppointments' => $totalAppointments,
    'completedAppointments' => $completedAppointments,
    'averageRating' => $averageRating
]);
