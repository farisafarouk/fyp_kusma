<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id FROM consultants WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$consultant = $stmt->get_result()->fetch_assoc();
$consultant_id = $consultant['id'];

// Optional filters
$ratingFilter = $_GET['rating'] ?? null;
$fromDate = $_GET['from'] ?? null;
$toDate = $_GET['to'] ?? null;

$sql = "
    SELECT 
        a.scheduled_date,
        a.rating,
        a.feedback,
        u.name AS customer_name
    FROM appointments a
    JOIN users u ON a.customer_id = u.id
    WHERE a.consultant_id = ? AND a.status = 'completed' AND a.feedback IS NOT NULL
";

$params = [$consultant_id];
$types = "i";

if ($ratingFilter) {
    $sql .= " AND a.rating = ?";
    $params[] = $ratingFilter;
    $types .= "i";
}
if ($fromDate && $toDate) {
    $sql .= " AND a.scheduled_date BETWEEN ? AND ?";
    $params[] = $fromDate;
    $params[] = $toDate;
    $types .= "ss";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();

$result = $stmt->get_result();
$feedback = [];
while ($row = $result->fetch_assoc()) {
    $feedback[] = $row;
}

echo json_encode($feedback);
?>
