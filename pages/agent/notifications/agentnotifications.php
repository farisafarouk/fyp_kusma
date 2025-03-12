<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is logged in and has the "agent" role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../../login/login.php");
    exit();
}

// Get the logged-in user's email
$user_email = $_SESSION['email']; // Assuming 'email' is stored in the session during login

// Fetch notifications for the logged-in user
$sql = "SELECT id, title, message, recipient, recipient_email, sent_at 
        FROM notifications 
        WHERE recipient = 'all' OR recipient = 'agents' OR recipient_email = ? 
        ORDER BY sent_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Notifications</title>
  <link rel="stylesheet" href="../../../assets/css/agent_notifications.css">
  <link rel="stylesheet" href="../../../assets/css/agent_sidebar.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include '../agentsidebar.php'; ?>

    <!-- Main Content -->
    <main class="dashboard-content">
      <section class="dashboard-section">
        <h1><i class="fas fa-bell"></i> Notifications</h1>
        <p>View notifications sent to you or broadcasted to all agents.</p>

        <!-- Notification Table -->
        <table class="contact-table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Message</th>
              <th>Sent At</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['title']); ?></td>
                  <td><?php echo htmlspecialchars($row['message']); ?></td>
                  <td><?php echo htmlspecialchars($row['sent_at']); ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="3">No notifications found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</body>
</html>
