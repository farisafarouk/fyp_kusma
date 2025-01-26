<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$consultant_id = $_GET['consultant_id'] ?? null;

// Redirect if no consultant is selected
if (!$consultant_id) {
    header("Location: consultant_list.php");
    exit();
}

// Fetch consultant details
$sql_consultant = "SELECT u.name, c.expertise, c.rate_per_hour 
                   FROM consultants c 
                   INNER JOIN users u ON c.user_id = u.id 
                   WHERE c.id = ?";
$stmt_consultant = $conn->prepare($sql_consultant);
$stmt_consultant->bind_param("i", $consultant_id);
$stmt_consultant->execute();
$consultant = $stmt_consultant->get_result()->fetch_assoc();

// Fetch consultant's availability
$sql_schedule = "SELECT id, date, start_time, end_time 
                 FROM schedules 
                 WHERE consultant_id = ? 
                 AND date >= CURDATE()
                 ORDER BY date, start_time";
$stmt_schedule = $conn->prepare($sql_schedule);
$stmt_schedule->bind_param("i", $consultant_id);
$stmt_schedule->execute();
$schedule_result = $stmt_schedule->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Appointment</title>
    <link rel="stylesheet" href="../../../assets/css/select_appointment.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="appointment-header">
        <h1>Book an Appointment</h1>
        <p>Choose a date and time with <strong><?= htmlspecialchars($consultant['name']) ?></strong>, an expert in <?= htmlspecialchars($consultant['expertise']) ?>.</p>
    </header>

    <!-- Consultant Info -->
    <div class="consultant-info">
        <h2>Consultant Details</h2>
        <p><strong>Expertise:</strong> <?= htmlspecialchars($consultant['expertise']) ?></p>
        <p><strong>Rate/Hour:</strong> RM <?= htmlspecialchars($consultant['rate_per_hour']) ?></p>
    </div>

    <!-- Available Slots -->
    <div class="available-slots">
        <h2>Available Slots</h2>
        <?php if ($schedule_result->num_rows > 0): ?>
            <form method="POST" action="appointment_confirmation.php">
                <table class="slots-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Select</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $schedule_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['date']); ?></td>
                                <td><?= htmlspecialchars($row['start_time']); ?></td>
                                <td><?= htmlspecialchars($row['end_time']); ?></td>
                                <td>
                                    <input type="radio" name="schedule_id" value="<?= $row['id']; ?>" required>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <button type="submit" class="confirm-btn">Confirm Appointment</button>
            </form>
        <?php else: ?>
            <p>No available slots at the moment. Please check back later.</p>
        <?php endif; ?>
    </div>
</body>
</html>
