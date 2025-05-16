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
$cancel_note = isset($data['cancel_note']) ? trim($data['cancel_note']) : null;
$slot_id = isset($data['slot_id']) ? intval($data['slot_id']) : null;
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

// Check appointment ownership
$checkStmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND consultant_id = ?");
$checkStmt->bind_param("ii", $appointment_id, $consultant_id);
$checkStmt->execute();
$res = $checkStmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Appointment not found']);
    exit;
}
$appointment = $res->fetch_assoc();

// Handle appointment status changes
if ($status === 'confirmed') {
    $stmt = $conn->prepare("UPDATE appointments SET status = 'confirmed', updated_by = ? WHERE id = ?");
    $stmt->bind_param("si", $updated_by, $appointment_id);

} elseif ($status === 'canceled') {
    $stmt = $conn->prepare("UPDATE appointments SET status = 'canceled', cancel_note = ?, updated_by = ? WHERE id = ?");
    $stmt->bind_param("ssi", $cancel_note, $updated_by, $appointment_id);

} elseif ($status === 'rescheduled') {
    if (!$slot_id) {
        echo json_encode(['success' => false, 'error' => 'Missing slot_id for reschedule']);
        exit;
    }

    // Patch: rescheduled is not a valid ENUM status in appointments
    $status = 'confirmed';

    $slotStmt = $conn->prepare("SELECT date, start_time FROM schedules WHERE id = ? AND is_available = 1");
    $slotStmt->bind_param("i", $slot_id);
    $slotStmt->execute();
    $slotResult = $slotStmt->get_result();

    if ($slotResult->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid or unavailable slot']);
        exit;
    }

    $slot = $slotResult->fetch_assoc();
    $new_date = $slot['date'];
    $new_time = $slot['start_time'];

    // Log to reschedule history
    $history = $conn->prepare("INSERT INTO reschedule_history 
        (appointment_id, reschedule_date, reschedule_time, reason) 
        VALUES (?, ?, ?, ?)");
    $history->bind_param("isss", $appointment_id, $new_date, $new_time, $cancel_note);
    $history->execute();

    // Update appointment with new slot
    $stmt = $conn->prepare("UPDATE appointments SET status = ?, scheduled_date = ?, scheduled_time = ?, updated_by = ?, cancel_note = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $status, $new_date, $new_time, $updated_by, $cancel_note, $appointment_id);

} elseif ($status === 'completed') {
    $stmt = $conn->prepare("UPDATE appointments SET status = 'completed', updated_by = ? WHERE id = ?");
    $stmt->bind_param("si", $updated_by, $appointment_id);
}

if (isset($stmt) && $stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database update failed']);
}
?>
