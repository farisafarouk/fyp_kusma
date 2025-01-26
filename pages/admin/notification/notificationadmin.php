<?php
require '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recipientType = $_POST['recipient'] ?? '';
    $email = $_POST['email'] ?? null; // Retrieve the specific email if provided
    $title = $_POST['title'] ?? '';
    $message = $_POST['message'] ?? '';

    // Validate input
    if (!empty($title) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO notifications (title, message, recipient, recipient_email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $title, $message, $recipientType, $email);

        if ($stmt->execute()) {
            echo "<script>alert('Notification sent successfully!'); window.location.href = 'notificationadmin.php';</script>";
        } else {
            echo "<script>alert('Failed to send notification.'); window.location.href = 'notificationadmin.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Please fill in all fields.'); window.location.href = 'notificationadmin.php';</script>";
    }
}

// Fetch notification history
$sql = "SELECT id, title, message, recipient, recipient_email, sent_at FROM notifications ORDER BY sent_at DESC";
$result = $conn->query($sql);
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
        <p>Send notifications to a specific email or to groups of users.</p>

        <!-- Notification Form -->
        <form id="notificationForm" action="" method="POST">
          <div class="input-group">
            <label for="recipient">Select Recipient Type</label>
            <select id="recipient" name="recipient" onchange="toggleEmailInput()" required>
              <option value="all">All Users</option>
              <option value="agents">Agents</option>
              <option value="consultants">Consultants</option>
              <option value="customers">Customers</option>
              <option value="email">Specific Email</option>
            </select>
          </div>

          <div class="input-group" id="email-input-group" style="display: none;">
            <label for="email">Recipient Email</label>
            <input type="email" id="email" name="email" placeholder="Enter recipient's email">
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
        
        <!-- Filter -->
        <div class="input-group">
          <label for="filter">Filter by Title or Message</label>
          <input type="text" id="filter" placeholder="Search notifications by title or message" onkeyup="filterNotifications()">
        </div>

        <table class="contact-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Title</th>
              <th>Message</th>
              <th>Recipient Type</th>
              <th>Recipient Email</th>
              <th>Sent At</th>
            </tr>
          </thead>
          <tbody id="notificationTable">
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['message']); ?></td>
                <td><?php echo htmlspecialchars($row['recipient']); ?></td>
                <td><?php echo htmlspecialchars($row['recipient_email'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($row['sent_at']); ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <script>
    function toggleEmailInput() {
      const recipientType = document.getElementById('recipient').value;
      const emailInputGroup = document.getElementById('email-input-group');
      emailInputGroup.style.display = recipientType === 'email' ? 'block' : 'none';
    }

    function filterNotifications() {
      const filterValue = document.getElementById('filter').value.toLowerCase();
      const rows = document.querySelectorAll('#notificationTable tr');
      rows.forEach(row => {
        const title = row.cells[1].textContent.toLowerCase();
        const message = row.cells[2].textContent.toLowerCase();
        if (title.includes(filterValue) || message.includes(filterValue)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }
  </script>
</body>
</html>
