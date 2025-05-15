<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || !is_array($data)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

// Get consultant_id
$stmt = $conn->prepare("SELECT id FROM consultants WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Consultant not found']);
    exit();
}
$consultant_id = $res->fetch_assoc()['id'];

foreach ($data as $input) {
    $id = $input['id'] ?? null;
    $start = new DateTime($input['start']);
    $end = new DateTime($input['end']);
    $mode = $input['mode'];
    $is_recurring = $input['recurring'] ? 1 : 0;
    $pattern = $input['pattern'] ?? 'none';
    $repeat_until = !empty($input['repeat_until']) ? new DateTime($input['repeat_until']) : null;
    $scope = $input['scope'] ?? 'single';

    $recurrence_group_id = ($is_recurring && $pattern !== 'none') ? uniqid('rec_', true) : null;

    // Calculate slot entries
    $slots = [];
    $current = clone $start;

    do {
        $slots[] = [
            'date' => $current->format('Y-m-d'),
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s')
        ];

        if ($pattern === 'daily') $current->modify('+1 day');
        elseif ($pattern === 'weekly') $current->modify('+7 days');
        else break;
    } while ($repeat_until && $current <= $repeat_until);

    // Delete old if editing
    if ($id && $scope === 'single') {
        $stmt = $conn->prepare("DELETE FROM schedules WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($id && $scope === 'future') {
        $stmt = $conn->prepare("SELECT recurrence_group_id, date FROM schedules WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($group_id, $from_date);
        if ($stmt->fetch()) {
            $stmt->close();
            $stmt = $conn->prepare("DELETE FROM schedules WHERE recurrence_group_id = ? AND date >= ?");
            $stmt->bind_param("ss", $group_id, $from_date);
            $stmt->execute();
        }
    }

    // Insert slots
    $stmt = $conn->prepare("INSERT INTO schedules (consultant_id, date, start_time, end_time, is_available, is_recurring, recurring_pattern, appointment_mode, recurrence_group_id) VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?)");
    foreach ($slots as $slot) {
        $stmt->bind_param("isssisss", $consultant_id, $slot['date'], $slot['start_time'], $slot['end_time'], $is_recurring, $pattern, $mode, $recurrence_group_id);
        $stmt->execute();
    }
}

echo json_encode(['success' => true]);
