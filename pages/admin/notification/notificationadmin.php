<?php
require '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete notification
    if (isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];
        $conn->query("DELETE FROM notifications WHERE id = $delete_id");
        header("Location: notificationadmin.php");
        exit();
    }

    // Send notification
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';

    if (!empty($email) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES ((SELECT id FROM users WHERE email = ? LIMIT 1), ?)");
        $stmt->bind_param('ss', $email, $message);
        if ($stmt->execute()) {
            echo "<script>alert('Notification sent successfully!'); window.location.href = 'notificationadmin.php';</script>";
        } else {
            echo "<script>alert('Failed to send notification.'); window.location.href = 'notificationadmin.php';</script>";
        }
    } else {
        echo "<script>alert('Please fill in all fields.'); window.location.href = 'notificationadmin.php';</script>";
    }
}

$result = $conn->query("
    SELECT n.id, u.email, n.message, n.created_at 
    FROM notifications n
    JOIN users u ON u.id = n.user_id
    ORDER BY n.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Notifications</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_notifications.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
  <?php include '../adminsidebar.php'; ?>

  <main class="dashboard-content">
    <section class="dashboard-section">
      <h1><i class="fas fa-bell"></i> Send Notification</h1>
      <form method="POST" class="notification-form">
        <div class="input-group">
          <label for="email">Recipient Email</label>
          <input type="email" name="email" id="email" placeholder="Enter user email" required>
        </div>
        <div class="input-group">
          <label for="message">Message</label>
          <textarea name="message" id="message" rows="4" required placeholder="Write your message..."></textarea>
        </div>
        <button type="submit" class="dashboard-btn">Send Notification</button>
      </form>
    </section>

    <section class="dashboard-section">
      <h2><i class="fas fa-history"></i> Notification History</h2>

      <div class="filter-container">
        <input type="text" id="searchInput" placeholder="Search by Email or Message" onkeyup="filterTable()">
      </div>

      <table class="contact-table">
      <thead>
  <tr>
    <th>Email</th>
    <th>Message</th>
    <th>Date</th>
    <th class="action-col">Action</th>
  </tr>
</thead>
<tbody id="notificationTable">
  <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
      <td><?= htmlspecialchars($row['email']) ?></td>
      <td><?= htmlspecialchars($row['message']) ?></td>
      <td><?= $row['created_at'] ?></td>
      <td class="action-col">
        <form method="POST" style="display:inline;">
          <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
          <button class="action-btn delete" onclick="return confirm('Delete this notification?');">
            <i class="fas fa-trash-alt"></i> Delete
          </button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
</tbody>

      </table>
    </section>
  </main>
</div>

<script>
function filterTable() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const rows = document.querySelectorAll("#notificationTable tr");
  rows.forEach(row => {
    const email = row.cells[0].textContent.toLowerCase();
    const message = row.cells[1].textContent.toLowerCase();
    row.style.display = (email.includes(input) || message.includes(input)) ? "" : "none";
  });
}
</script>
</body>
</html>
