<?php
require '../../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

$subscriptions = $conn->query("
    SELECT id, name, email, subscription_status, subscription_expiry, subscription_remaining_days 
    FROM users 
    WHERE role = 'customer' 
    ORDER BY subscription_expiry DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Subscription Management</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css" />
  <link rel="stylesheet" href="../../../assets/css/admin_subscription.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
</head>
<body>
<div class="dashboard-container">
  <?php include '../adminsidebar.php'; ?>

  <main class="dashboard-content">
    <section class="dashboard-section">
      <h1><i class="fas fa-crown"></i> Customer Subscriptions</h1>
      <p>Manage customer subscription plans by extending or stopping them with ease.</p>

      <table class="contact-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
            <th>Expiry</th>
            <th>Remaining</th>
            <th>Remarks</th> <!-- New Column -->
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $subscriptions->fetch_assoc()): 
            $expiry = $row['subscription_expiry'] ? new DateTime($row['subscription_expiry']) : null;
            $now = new DateTime();
            $daysLeft = $expiry && $expiry > $now ? $expiry->diff($now)->format("%a") : 0;
          ?>
          <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>
              <?php
              switch ($row['subscription_status']) {
                case 'subscribed':
                  echo '<span class="badge badge-active">Subscribed</span>'; break;
                case 'subscription stopped':
                  echo '<span class="badge badge-stopped">Stopped</span>'; break;
                default:
                  echo '<span class="badge badge-free">Free</span>';
              }
              ?>
            </td>
            <td><?= $row['subscription_expiry'] ?? '-' ?></td>
            <td><?= ($row['subscription_status'] === 'subscription stopped') ? $row['subscription_remaining_days']." days" : $daysLeft." days" ?></td>
            <td>
              <?php if ($row['subscription_status'] === 'subscription stopped'): ?>
                <span class="remark-stopped">Subscription stopped — <?= $row['subscription_remaining_days'] ?> days remaining</span>
              <?php elseif ($row['subscription_status'] === 'subscribed'): ?>
                <span class="remark-active">Active — <?= $daysLeft ?> days left</span>
              <?php else: ?>
                <span class="remark-free">Free Plan</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="action-btn-group">
                <button class="action-btn edit" onclick="openExtendModal(<?= $row['id'] ?>)">Extend</button>
                <button class="action-btn delete" onclick="openStopModal(<?= $row['id'] ?>)">Stop</button>
              </div>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>
  </main>
</div>

<!-- Extend Modal -->
<div id="extendModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeExtendModal()">&times;</span>
    <h2>Extend Subscription</h2>
    <form method="POST" action="process_subscription.php" enctype="multipart/form-data">
      <input type="hidden" name="action" value="extend">
      <input type="hidden" name="user_id" id="extend-user-id">
      <div class="input-group">
        <label>Extend by</label>
        <select name="days" required>
          <option value="365">1 Year</option>
          <option value="730">2 Years</option>
          <option value="1095">3 Years</option>
        </select>
      </div>
      <div class="input-group">
        <label>Upload Receipt (PDF)</label>
        <input type="file" name="receipt" accept="application/pdf" required />
      </div>
      <button type="submit" class="action-btn save">Save</button>
    </form>
  </div>
</div>

<!-- Stop Modal -->
<div id="stopModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeStopModal()">&times;</span>
    <h2>Confirm Stop</h2>
    <form method="POST" action="process_subscription.php" onsubmit="return confirm('Confirm stopping subscription?');">
      <input type="hidden" name="action" value="stop">
      <input type="hidden" name="user_id" id="stop-user-id">
      <p>This will retain the remaining days and mark the subscription as stopped.</p>
      <button type="submit" class="action-btn reset">Stop Subscription</button>
    </form>
  </div>
</div>

<script>
function openExtendModal(userId) {
  document.getElementById('extend-user-id').value = userId;
  document.getElementById('extendModal').style.display = 'flex';
}
function closeExtendModal() {
  document.getElementById('extendModal').style.display = 'none';
}
function openStopModal(userId) {
  document.getElementById('stop-user-id').value = userId;
  document.getElementById('stopModal').style.display = 'flex';
}
function closeStopModal() {
  document.getElementById('stopModal').style.display = 'none';
}
</script>
</body>
</html>
