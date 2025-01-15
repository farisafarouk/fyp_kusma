<?php
require_once '../../../config/database.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$consultant_id = $_GET['consultant_id'] ?? null;

if (!$consultant_id) {
    die("No consultant selected.");
}

// Fetch consultant details and available schedules
try {
    $consultantQuery = $conn->prepare("
        SELECT u.name AS consultant_name, cd.expertise, cd.rate
        FROM users u
        INNER JOIN consultant_details cd ON u.id = cd.consultant_id
        WHERE u.id = ?
    ");
    $consultantQuery->bind_param("i", $consultant_id);
    $consultantQuery->execute();
    $consultantResult = $consultantQuery->get_result()->fetch_assoc();

    if (!$consultantResult) {
        die("Consultant not found.");
    }

    $scheduleQuery = $conn->prepare("
        SELECT schedule_id, day, date, start_time, end_time, appointment_mode
        FROM consultant_schedule
        WHERE consultant_id = ? AND is_available = 1
    ");
    $scheduleQuery->bind_param("i", $consultant_id);
    $scheduleQuery->execute();
    $schedules = $scheduleQuery->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = $_POST['schedule_id'];
    $appointment_mode = $_POST['appointment_mode'];

    try {
        // Update schedule to mark it as unavailable
        $updateSchedule = $conn->prepare("
            UPDATE consultant_schedule 
            SET is_available = 0, customer_id = ? 
            WHERE schedule_id = ?
        ");
        $updateSchedule->bind_param("ii", $user_id, $schedule_id);
        $updateSchedule->execute();

        // Insert booking into the appointments table
        $insertAppointment = $conn->prepare("
            INSERT INTO appointments 
            (appointment_mode, schedule_day, scheduled_date, scheduled_time, status, customer_id, consultant_id)
            SELECT ?, day, date, start_time, 'pending', ?, consultant_id 
            FROM consultant_schedule WHERE schedule_id = ?
        ");
        $insertAppointment->bind_param("sii", $appointment_mode, $user_id, $schedule_id);
        $insertAppointment->execute();

        // Redirect to confirmation page or success message
        header("Location: booking_success.php");
        exit();
    } catch (Exception $e) {
        die("Error booking appointment: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="../../../assets/css/booking_page.css">
</head>
<body>
    <div class="container">
        <h1>Book an Appointment</h1>
        <div class="consultant-details">
            <h2><?= htmlspecialchars($consultantResult['consultant_name']) ?></h2>
            <p>Expertise: <?= htmlspecialchars($consultantResult['expertise']) ?></p>
            <p>Rate: RM<?= number_format($consultantResult['rate'], 2) ?></p>
        </div>

        <h3>Available Schedules</h3>
        <?php if (!empty($schedules)): ?>
            <form action="" method="POST">
                <div class="schedules">
                    <?php foreach ($schedules as $schedule): ?>
                        <label>
                            <input type="radio" name="schedule_id" value="<?= $schedule['schedule_id'] ?>" required>
                            <?= htmlspecialchars($schedule['day']) ?>, 
                            <?= htmlspecialchars($schedule['date']) ?> 
                            (<?= htmlspecialchars($schedule['start_time']) ?> - <?= htmlspecialchars($schedule['end_time']) ?>, 
                            <?= htmlspecialchars($schedule['appointment_mode']) ?>)
                        </label><br>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="book-btn">Confirm Booking</button>
            </form>
        <?php else: ?>
            <p>No available schedules for this consultant.</p>
        <?php endif; ?>
        <a href="consultant_list.php" class="back-btn">Back to Consultants</a>
    </div>
</body>
</html>
