<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$schedule_id = $_POST['schedule_id'] ?? null;

// Redirect if no schedule is selected
if (!$schedule_id) {
    header("Location: consultant_list.php");
    exit();
}

// Fetch schedule details
$sql_schedule = "
    SELECT s.id AS schedule_id, s.date, s.start_time, s.end_time, c.id AS consultant_id, 
           u.name AS consultant_name, c.expertise, c.rate_per_hour
    FROM schedules s
    INNER JOIN consultants c ON s.consultant_id = c.id
    INNER JOIN users u ON c.user_id = u.id
    WHERE s.id = ?
";
$stmt_schedule = $conn->prepare($sql_schedule);
$stmt_schedule->bind_param("i", $schedule_id);
$stmt_schedule->execute();
$schedule = $stmt_schedule->get_result()->fetch_assoc();

// Redirect if schedule not found
if (!$schedule) {
    header("Location: consultant_list.php");
    exit();
}

// Handle booking confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {
    $reason = $_POST['reason'] ?? '';

    // Insert into appointments table
    $sql_appointment = "
        INSERT INTO appointments (consultant_id, customer_id, scheduled_date, scheduled_time, duration, 
                                   appointment_mode, reason_for_appointment, status, payment_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')
    ";
    $stmt_appointment = $conn->prepare($sql_appointment);
    $duration = strtotime($schedule['end_time']) - strtotime($schedule['start_time']); // Duration in seconds
    $duration_minutes = $duration / 60; // Convert to minutes
    $appointment_mode = 'online'; // Default mode (can be updated later)

    $stmt_appointment->bind_param(
        "iississ",
        $schedule['consultant_id'],
        $user_id,
        $schedule['date'],
        $schedule['start_time'],
        $duration_minutes,
        $appointment_mode,
        $reason
    );

    if ($stmt_appointment->execute()) {
        header("Location: customer_appointments.php?success=1");
        exit();
    } else {
        die("Error confirming appointment: " . $stmt_appointment->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Appointment</title>
    <link rel="stylesheet" href="../../../assets/css/appointment_confirmation.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="confirmation-container">
        <h1>Confirm Your Appointment</h1>

        <!-- Consultant Details -->
        <div class="consultant-details">
            <h2>Consultant Details</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($schedule['consultant_name']); ?></p>
            <p><strong>Expertise:</strong> <?= htmlspecialchars($schedule['expertise']); ?></p>
            <p><strong>Rate per Hour:</strong> RM <?= htmlspecialchars($schedule['rate_per_hour']); ?></p>
        </div>

        <!-- Appointment Details -->
        <div class="appointment-details">
            <h2>Appointment Details</h2>
            <p><strong>Date:</strong> <?= htmlspecialchars($schedule['date']); ?></p>
            <p><strong>Start Time:</strong> <?= htmlspecialchars($schedule['start_time']); ?></p>
            <p><strong>End Time:</strong> <?= htmlspecialchars($schedule['end_time']); ?></p>
        </div>

        <!-- Booking Form -->
        <form method="POST" action="appointment_confirmation.php">
            <input type="hidden" name="schedule_id" value="<?= $schedule_id; ?>">
            <label for="reason">Reason for Appointment:</label>
            <textarea id="reason" name="reason" placeholder="Briefly explain the purpose of this appointment." required></textarea>
            <button type="submit" name="confirm_booking" class="confirm-btn">Confirm Booking</button>
        </form>
    </div>
</body>
</html>
