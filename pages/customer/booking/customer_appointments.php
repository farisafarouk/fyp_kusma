<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch upcoming appointments
$sql_upcoming = "
    SELECT 
        a.id AS appointment_id, 
        u.name AS consultant_name, 
        c.expertise, 
        a.scheduled_date, 
        a.scheduled_time, 
        a.status 
    FROM appointments a
    INNER JOIN consultants c ON a.consultant_id = c.id
    INNER JOIN users u ON c.user_id = u.id
    WHERE a.customer_id = ? AND a.status IN ('pending', 'confirmed')
    ORDER BY a.scheduled_date, a.scheduled_time
";
$stmt_upcoming = $conn->prepare($sql_upcoming);
$stmt_upcoming->bind_param("i", $user_id);
$stmt_upcoming->execute();
$result_upcoming = $stmt_upcoming->get_result();

// Fetch completed appointments
$sql_completed = "
    SELECT 
        a.id AS appointment_id, 
        u.name AS consultant_name, 
        c.expertise, 
        a.scheduled_date, 
        a.scheduled_time, 
        a.feedback, 
        a.rating 
    FROM appointments a
    INNER JOIN consultants c ON a.consultant_id = c.id
    INNER JOIN users u ON c.user_id = u.id
    WHERE a.customer_id = ? AND a.status = 'completed'
    ORDER BY a.scheduled_date DESC
";
$stmt_completed = $conn->prepare($sql_completed);
$stmt_completed->bind_param("i", $user_id);
$stmt_completed->execute();
$result_completed = $stmt_completed->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
    <link rel="stylesheet" href="../../../assets/css/customer_navbar.css">
    <link rel="stylesheet" href="../../../assets/css/customer_appointments.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.0.0/axios.min.js"></script>
</head>
<body>
    <!-- Navbar -->
    <?php include '../customer_navbar.php'; ?>

    <div class="dashboard-container">
        <main class="dashboard-content">
            <h1>Your Appointments</h1>

            <!-- Upcoming Appointments Section -->
            <section class="appointments-section">
                <h2>Upcoming Appointments</h2>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Consultant</th>
                            <th>Expertise</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_upcoming->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['consultant_name']); ?></td>
                                <td><?= htmlspecialchars($row['expertise']); ?></td>
                                <td><?= htmlspecialchars($row['scheduled_date']); ?></td>
                                <td><?= htmlspecialchars($row['scheduled_time']); ?></td>
                                <td><?= htmlspecialchars($row['status']); ?></td>
                                <td>
                                    <button class="action-btn reschedule" onclick="openRescheduleModal(<?= $row['appointment_id']; ?>)">Reschedule</button>
                                    <button class="action-btn cancel" onclick="openCancelModal(<?= $row['appointment_id']; ?>)">Cancel</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <!-- Completed Appointments Section -->
            <section class="appointments-section">
                <h2>Completed Appointments</h2>
                <table class="appointments-table">
                    <thead>
                        <tr>
                            <th>Consultant</th>
                            <th>Expertise</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Feedback</th>
                            <th>Rating</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_completed->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['consultant_name']); ?></td>
                                <td><?= htmlspecialchars($row['expertise']); ?></td>
                                <td><?= htmlspecialchars($row['scheduled_date']); ?></td>
                                <td><?= htmlspecialchars($row['scheduled_time']); ?></td>
                                <td><?= htmlspecialchars($row['feedback'] ?? 'No feedback'); ?></td>
                                <td><?= htmlspecialchars($row['rating'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if (empty($row['feedback'])): ?>
                                        <button class="action-btn feedback" onclick="openFeedbackModal(<?= $row['appointment_id']; ?>)">Give Feedback</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>

            <!-- Book New Appointment -->
            <div class="book-appointment">
                <button onclick="window.location.href='consultant_list.php'">Book a New Appointment</button>
            </div>
        </main>
    </div>

    <!-- Feedback Modal -->
    <div id="feedbackModal" class="modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeFeedbackModal()">&times;</span>
            <h2>Give Feedback</h2>
            <form id="feedbackForm">
                <input type="hidden" id="feedbackAppointmentId">
                <div class="input-group">
                    <label for="rating">Rating (1-5)</label>
                    <select id="rating" required>
                        <option value="">Select Rating</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="input-group">
                    <label for="feedback">Feedback</label>
                    <textarea id="feedback" rows="4" placeholder="Share your experience..." required></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="save-btn" onclick="submitFeedback()">Submit</button>
                    <button type="button" class="cancel-btn" onclick="closeFeedbackModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let selectedAppointmentId = null;

        function openFeedbackModal(appointmentId) {
            selectedAppointmentId = appointmentId;
            document.getElementById('feedbackModal').style.display = 'flex';
        }

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').style.display = 'none';
        }

        function submitFeedback() {
            const rating = document.getElementById('rating').value;
            const feedback = document.getElementById('feedback').value;

            axios.post('submit_feedback.php', {
                id: selectedAppointmentId,
                rating: rating,
                feedback: feedback
            }).then(() => {
                alert('Feedback submitted successfully!');
                window.location.reload();
            }).catch(() => alert('Error submitting feedback.'));
        }
    </script>
</body>
</html>
