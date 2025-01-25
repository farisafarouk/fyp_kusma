<?php
session_start();
require '../../../config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipient = $_POST['recipient'];
    $title = $_POST['title'];
    $message = $_POST['message'];

    // Insert notification into the database
    $sql = "INSERT INTO notifications (title, message, recipient, sent_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $title, $message, $recipient);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Notification sent successfully!";
    } else {
        $_SESSION['error'] = "Failed to send notification: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Notifications</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_notifications.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include '../adminsidebar.php'; ?>

    <!-- Main Content -->
    <main class="dashboard-content">
      <section class="dashboard-section">
        <h1><i class="fas fa-bell"></i> Send Notifications</h1>
        <p>Notify agents, consultants, or customers about updates, changes, or reminders.</p>

        <?php
        if (isset($_SESSION['success'])) {
            echo "<p style='color: green; font-weight: bold;'>{$_SESSION['success']}</p>";
            unset($_SESSION['success']);
        }
        if (isset($_SESSION['error'])) {
            echo "<p style='color: red; font-weight: bold;'>{$_SESSION['error']}</p>";
            unset($_SESSION['error']);
        }
        ?>

        <!-- Notification Form -->
        <form id="notificationForm" action="" method="POST">
          <div class="input-group">
            <label for="recipient">Select Recipient Type</label>
            <select id="recipient" name="recipient" required>
              <option value="all">All Users</option>
              <option value="agents">Agents</option>
              <option value="consultants">Consultants</option>
              <option value="customers">Customers</option>
            </select>
          </div>

          <div class="input-group">
            <label for="title">Notification Title</label>
            <input type="text" id="title" name="title" placeholder="Enter notification title" required>
          </div>

          <div class="input-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" placeholder="Write your message here..." rows="5" required></textarea>
          </div>

          <button type="submit" class="dashboard-btn">Send Notification</button>
        </form>
      </section>

      <!-- Notification History -->
      <section class="dashboard-section">
        <h1><i class="fas fa-history"></i> Notification History</h1>
        <p>View all previously sent notifications.</p>
        <table class="contact-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Message</th>
              <th>Recipient Type</th>
              <th>Sent At</th>
            </tr>
          </thead>
          <tbody>
            <?php
            // Fetch notifications from the database
            $sql = "SELECT id, title, message, recipient, sent_at FROM notifications ORDER BY sent_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['id']}</td>
                            <td>{$row['title']}</td>
                            <td>{$row['message']}</td>
                            <td>{$row['recipient']}</td>
                            <td>{$row['sent_at']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No notifications sent yet.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</body>
</html>
