<?php
session_start();
require_once '../../config/database.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id']) ? intval($data['id']) : 0;
$scope = $data['scope'] ?? 'single';

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    exit();
}

$conn->begin_transaction();

// Get the slot details
$stmt = $conn->prepare("SELECT recurrence_group_id, date FROM schedules WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$slot = $result->fetch_assoc();
$stmt->close();

if (!$slot) {
    echo json_encode(['success' => false, 'error' => 'Slot not found']);
    $conn->rollback();
    exit();
}

if ($scope === 'single' || empty($slot['recurrence_group_id'])) {
    // Just delete this one slot
    $stmt = $conn->prepare("DELETE FROM schedules WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $conn->commit();
    echo json_encode(['success' => true, 'deleted' => 'single']);
    exit();
}

// If recurring and scope = future
if ($scope === 'future') {
    $group_id = $slot['recurrence_group_id'];
    $from_date = $slot['date'];

    $stmt = $conn->prepare("DELETE FROM schedules WHERE recurrence_group_id = ? AND date >= ?");
    $stmt->bind_param("ss", $group_id, $from_date);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'deleted' => 'future']);
    exit();
}

$conn->rollback();
echo json_encode(['success' => false, 'error' => 'Unhandled condition']);
