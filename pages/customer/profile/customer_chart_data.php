<?php
session_start();
require_once '../../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Prepare data for the last 6 months
$sql = "SELECT DATE_FORMAT(scheduled_date, '%b') AS month, COUNT(*) AS count
        FROM appointments
        WHERE customer_id = ?
          AND scheduled_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(scheduled_date, '%Y-%m')
        ORDER BY MIN(scheduled_date) ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'month' => $row['month'],
        'count' => (int)$row['count']
    ];
}

echo json_encode($data);
