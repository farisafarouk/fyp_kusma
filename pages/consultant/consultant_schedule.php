<?php
session_start();
require_once '../../config/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the consultant is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Logged-in consultant's user_id
$consultant_id = null;

// Fetch the consultant ID from the consultants table
$sql_consultant = "SELECT id FROM consultants WHERE user_id = ?";
$stmt_consultant = $conn->prepare($sql_consultant);
$stmt_consultant->bind_param("i", $user_id);
$stmt_consultant->execute();
$result_consultant = $stmt_consultant->get_result();

if ($result_consultant->num_rows === 1) {
    $consultant = $result_consultant->fetch_assoc();
    $consultant_id = $consultant['id'];
} else {
    die("Error: Consultant data not found for the logged-in user.");
}

// Handle form submissions for adding availability
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $is_recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $recurring_pattern = $_POST['recurring_pattern'] ?? null;

    // Insert schedule into the schedules table
    $sql_schedule = "INSERT INTO schedules (consultant_id, date, start_time, end_time, is_available, is_recurring, recurring_pattern) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_schedule = $conn->prepare($sql_schedule);
    $stmt_schedule->bind_param(
        "isssiss",
        $consultant_id,
        $date,
        $start_time,
        $end_time,
        $is_available,
        $is_recurring,
        $recurring_pattern
    );

    if ($stmt_schedule->execute()) {
        header("Location: consultant_schedule.php?success=1");
        exit();
    } else {
        die("Error adding schedule: " . $stmt_schedule->error);
    }
}

// Fetch existing schedules for the consultant
$sql_fetch_schedules = "SELECT * FROM schedules WHERE consultant_id = ?";
$stmt_fetch_schedules = $conn->prepare($sql_fetch_schedules);
$stmt_fetch_schedules->bind_param("i", $consultant_id);
$stmt_fetch_schedules->execute();
$schedules = $stmt_fetch_schedules->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultant Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/consultantsidebar.css">
    <link href="../../assets/css/consultant_schedule.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'consultantsidebar.php'; ?>

        <main class="dashboard-content">
            <h1>Manage Your Availability</h1>
            <p>Set your availability so customers can schedule appointments with you.</p>

            <!-- Success Message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Schedule added successfully!</div>
            <?php endif; ?>

            <!-- Form for Adding Availability -->
            <form method="POST" class="form-section">
                <div class="mb-3">
                    <label for="date" class="form-label">Date</label>
                    <input type="date" id="date" name="date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="start_time" class="form-label">Start Time</label>
                    <input type="time" id="start_time" name="start_time" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="end_time" class="form-label">End Time</label>
                    <input type="time" id="end_time" name="end_time" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" id="is_available" name="is_available" class="form-check-input" checked>
                    <label for="is_available" class="form-check-label">Available</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" id="is_recurring" name="is_recurring" class="form-check-input">
                    <label for="is_recurring" class="form-check-label">Recurring</label>
                </div>
                <div class="mb-3">
                    <label for="recurring_pattern" class="form-label">Recurring Pattern</label>
                    <select id="recurring_pattern" name="recurring_pattern" class="form-control">
                        <option value="none">None</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Availability</button>
            </form>

            <!-- Display Existing Schedules -->
            <h2>Your Availability</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Available</th>
                        <th>Recurring</th>
                        <th>Recurring Pattern</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($schedule = $schedules->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($schedule['date']) ?></td>
                            <td><?= htmlspecialchars($schedule['start_time']) ?></td>
                            <td><?= htmlspecialchars($schedule['end_time']) ?></td>
                            <td><?= $schedule['is_available'] ? 'Yes' : 'No' ?></td>
                            <td><?= $schedule['is_recurring'] ? 'Yes' : 'No' ?></td>
                            <td><?= htmlspecialchars($schedule['recurring_pattern'] ?? 'None') ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
