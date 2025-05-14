<?php
require '../../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

// Fetch contact messages from DB
$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Messages</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_dashboard.css">
  <link rel="stylesheet" href="../../../assets/css/contactmessages.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
  <?php include '../adminsidebar.php'; ?>
  <main class="dashboard-content">
    <section class="dashboard-section">
      <h1><i class="fas fa-envelope"></i> Contact Messages</h1>
      <p>Messages submitted through the platform's contact form are listed below.</p>

      <div class="filter-container">
        <input type="text" id="filterInput" placeholder="Search by name, email, or subject..." onkeyup="filterTable()">
      </div>

      <div id="messagesTable">
        <table class="contact-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Subject</th>
              <th>Message</th>
              <th>Submitted At</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= htmlspecialchars($row['subject']) ?></td>
                  <td><?= htmlspecialchars($row['message']) ?></td>
                  <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="no-messages">No contact messages found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</div>

<script>
function filterTable() {
  const filter = document.getElementById("filterInput").value.toLowerCase();
  const rows = document.querySelectorAll(".contact-table tbody tr");
  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    row.style.display = text.includes(filter) ? "table-row" : "none";
  });
}
</script>
</body>
</html>
