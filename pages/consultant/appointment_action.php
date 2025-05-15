<?php
session_start();
require_once '../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$appointment_id = $data['id'];
$status = $data['status'];
$feedback = isset($data['feedback']) ? trim($data['feedback']) : null;
$new_slot = isset($data['new_slot']) ? trim($data['new_slot']) : null;
$updated_by = 'consultant';

// Get consultant_id
$user_id = $_SESSION['user_id'];
$consultantStmt = $conn->prepare("SELECT id FROM consultants WHERE user_id = ?");
$consultantStmt->bind_param("i", $user_id);
$consultantStmt->execute();
$result = $consultantStmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Consultant not found']);
    exit;
}
$consultant_id = $result->fetch_assoc()['id'];

// Get appointment and validate ownership
$checkStmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND consultant_id = ?");
$checkStmt->bind_param("ii", $appointment_id, $consultant_id);
$checkStmt->execute();
$res = $checkStmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Appointment not found']);
    exit;
}
$appointment = $res->fetch_assoc();

// Handle actions
if ($status === 'confirmed') {
    $stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed', updated_by = ? WHERE id = ?");
    $stmt->bind_param("si", $updated_by, $appointment_id);

} elseif ($status === 'canceled') {
    $stmt = $conn->prepare("UPDATE appointments SET status = 'canceled', cancel_note = ?, updated_by = ? WHERE id = ?");
    $stmt->bind_param("ssi", $feedback, $updated_by, $appointment_id);


} elseif ($status === 'rescheduled') {
    if (!$new_slot || strpos($new_slot, '|') === false) {
        echo json_encode(['success' => false, 'error' => 'Invalid slot format']);
        exit;
    }
    list($new_date, $new_time) = explode('|', $new_slot);

    // Log original to history before updating
    $history = $conn->prepare("INSERT INTO reschedule_history (appointment_id, old_date, old_time, new_date, new_time, updated_by, feedback) SELECT id, scheduled_date, scheduled_time, ?, ?, ?, ? FROM appointments WHERE id = ?");
    $history->bind_param("sssssi", $new_date, $new_time, $updated_by, $feedback, $appointment_id);
    $history->execute();

    $stmt = $conn->prepare("UPDATE appointments SET status = 'rescheduled', scheduled_date = ?, scheduled_time = ?, updated_by = ?, feedback = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $new_date, $new_time, $updated_by, $feedback, $appointment_id);

} elseif ($status === 'completed') {
    $stmt = $conn->prepare("UPDATE appointments SET status = 'completed', updated_by = ? WHERE id = ?");
    $stmt->bind_param("si", $updated_by, $appointment_id);
}


if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database update failed']);
}
?>
