<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get consultant ID
$stmt = $conn->prepare("SELECT id FROM consultants WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    echo json_encode([]);
    exit();
}

$consultant_id = $row['id'];

// Fetch all schedule slots for this consultant
$stmt = $conn->prepare("SELECT * FROM schedules WHERE consultant_id = ?");
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $start = $row['date'] . 'T' . $row['start_time'];
    $end = $row['date'] . 'T' . $row['end_time'];

    $events[] = [
        'id' => $row['id'],
        'title' => 'Available',
        'start' => $start,
        'end' => $end,
        'extendedProps' => [
            'mode' => $row['appointment_mode'],
            'recurring' => (bool)$row['is_recurring'],
            'pattern' => $row['recurring_pattern'],
            'group_id' => $row['recurrence_group_id']
        ]
    ];
}

echo json_encode($events);
