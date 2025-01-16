<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$consultant_id = $_POST['consultant_id'];
$appointment_mode = $_POST['appointment_mode'];
$schedule_id = $_POST['schedule_id'];

// Fetch schedule details
$query = "SELECT day, date, start_time FROM consultant_schedule WHERE schedule_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $schedule_id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();

// Insert the appointment
$query = "INSERT INTO appointments (
            appointment_mode, schedule_day, scheduled_date, scheduled_time, 
            status, customer_id, consultant_id
          ) VALUES (?, ?, ?, ?, 'pending', ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param(
    "ssssii",
    $appointment_mode, $schedule['day'], $schedule['date'], $schedule['start_time'],
    $user_id, $consultant_id
);

if ($stmt->execute()) {
    // Mark the schedule as unavailable
    $updateQuery = "UPDATE consultant_schedule SET is_available = 0, customer_id = ? WHERE schedule_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $user_id, $schedule_id);
    $updateStmt->execute();

    header("Location: dashboard.php?message=Appointment booked successfully");
    exit();
} else {
    echo "Error: " . $stmt->error;
}
?>
