<?php
session_start();
require_once '../../config/database.php';

// Ensure the user is logged in as a consultant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    header("Location: ../../login/login.php");
    exit();
}

$consultant_user_id = $_SESSION['user_id'];

// Fetch consultant ID from the `consultants` table
$sql_consultant_id = "SELECT id FROM consultants WHERE user_id = ?";
$stmt_consultant_id = $conn->prepare($sql_consultant_id);
$stmt_consultant_id->bind_param('i', $consultant_user_id);
$stmt_consultant_id->execute();
$result_consultant_id = $stmt_consultant_id->get_result();
$consultant = $result_consultant_id->fetch_assoc();

if (!$consultant) {
    die("Consultant profile not found.");
}

$consultant_id = $consultant['id'];

// Handle appointment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_status') {
        $appointment_id = $_POST['appointment_id'] ?? null;
        $status = $_POST['status'] ?? null;

        if ($appointment_id && $status) {
            $sql_update = "UPDATE appointments SET status = ? WHERE id = ? AND consultant_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('sii', $status, $appointment_id, $consultant_id);
            $stmt_update->execute();
        }
    } elseif ($action === 'reschedule') {
        $appointment_id = $_POST['appointment_id'] ?? null;
        $new_date = $_POST['reschedule_date'] ?? null;
        $new_time = $_POST['reschedule_time'] ?? null;

        if ($appointment_id && $new_date && $new_time) {
            $sql_update = "UPDATE appointments SET scheduled_date = ?, scheduled_time = ? WHERE id = ? AND consultant_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param('ssii', $new_date, $new_time, $appointment_id, $consultant_id);
            $stmt_update->execute();
        }
    }
}

// Fetch appointments for the consultant
$sql_appointments = "
    SELECT 
        a.id AS appointment_id,
        a.scheduled_date,
        a.scheduled_time,
        a.duration,
        a.status,
        u.name AS customer_name,
        a.reason_for_appointment,
        a.feedback,
        a.rating
    FROM appointments a
    INNER JOIN users u ON a.customer_id = u.id
    WHERE a.consultant_id = ?
    ORDER BY a.scheduled_date, a.scheduled_time
";
$stmt_appointments = $conn->prepare($sql_appointments);
$stmt_appointments->bind_param('i', $consultant_id);
$stmt_appointments->execute();
$result_appointments = $stmt_appointments->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultant Appointments</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/consultantsidebar.css">
    <link rel="stylesheet" href="../../assets/css/consultant_appointments.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../consultant/consultantsidebar.php'; ?>

        <main class="dashboard-content">
            <section class="dashboard-section">
                <h1>Manage Appointments</h1>
                <p>View, update, or reschedule appointments with customers.</p>

                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Feedback</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_appointments->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['scheduled_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['scheduled_time']); ?></td>
                                <td><?php echo htmlspecialchars($row['duration']); ?> mins</td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td>
                                    <?php if (!empty($row['feedback'])): ?>
                                        <strong>Rating:</strong> <?php echo htmlspecialchars($row['rating']); ?>/5<br>
                                        <?php echo htmlspecialchars($row['feedback']); ?>
                                    <?php else: ?>
                                        <em>No feedback yet</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $row['appointment_id']; ?>">
                                        <input type="hidden" name="action" value="update_status">
                                        <select name="status">
                                            <option value="confirmed" <?php echo $row['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirm</option>
                                            <option value="completed" <?php echo $row['status'] === 'completed' ? 'selected' : ''; ?>>Complete</option>
                                            <option value="canceled" <?php echo $row['status'] === 'canceled' ? 'selected' : ''; ?>>Cancel</option>
                                        </select>
                                        <button type="submit" class="action-btn save">Update</button>
                                    </form>
                                    <button class="action-btn reschedule" onclick="openRescheduleModal(<?php echo $row['appointment_id']; ?>)">Reschedule</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Reschedule Modal -->
    <div id="rescheduleModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeRescheduleModal()">&times;</span>
            <h2>Reschedule Appointment</h2>
            <form method="POST">
                <input type="hidden" name="action" value="reschedule">
                <input type="hidden" id="reschedule-appointment-id" name="appointment_id">
                <label for="reschedule-date">New Date:</label>
                <input type="date" id="reschedule-date" name="reschedule_date" required>
                <label for="reschedule-time">New Time:</label>
                <input type="time" id="reschedule-time" name="reschedule_time" required>
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="cancel-btn" onclick="closeRescheduleModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openRescheduleModal(id) {
            document.getElementById('reschedule-appointment-id').value = id;
            document.getElementById('rescheduleModal').style.display = 'flex';
        }

        function closeRescheduleModal() {
            document.getElementById('rescheduleModal').style.display = 'none';
        }
    </script>
</body>
</html>
