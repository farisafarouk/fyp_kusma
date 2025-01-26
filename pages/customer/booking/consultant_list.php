<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login/login.php");
    exit();
}

// Fetch consultants
$sql = "SELECT c.id AS consultant_id, u.name, c.expertise, c.rate_per_hour, c.rating, c.feedback_count 
        FROM consultants c
        INNER JOIN users u ON c.user_id = u.id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultant List</title>
    <link rel="stylesheet" href="../../../assets/css/consultant_list.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="consultant-list-header">
        <h1>Book a Consultant</h1>
        <p>Select a consultant to book your appointment.</p>
    </header>

    <!-- Consultant List -->
    <div class="consultant-list-container">
        <?php if ($result->num_rows > 0): ?>
            <table class="consultant-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Expertise</th>
                        <th>Rate/Hour</th>
                        <th>Rating</th>
                        <th>Feedback Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td><?= htmlspecialchars($row['expertise']); ?></td>
                            <td><?= htmlspecialchars($row['rate_per_hour']); ?></td>
                            <td><?= htmlspecialchars($row['rating'] ?? 'N/A'); ?></td>
                            <td><?= htmlspecialchars($row['feedback_count']); ?></td>
                            <td>
                                <form method="GET" action="select_appointment.php">
                                    <input type="hidden" name="consultant_id" value="<?= $row['consultant_id']; ?>">
                                    <button type="submit" class="book-btn">Book Appointment</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No consultants available at the moment. Please check back later.</p>
        <?php endif; ?>
    </div>
</body>
</html>
