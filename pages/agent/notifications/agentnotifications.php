<?php
session_start();
require_once '../../config/database.php';

// Ensure the user is an agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../../login/login.php");
    exit();
}

$agent_id = $_SESSION['user_id'];

// Fetch notifications for the agent
$sql = "SELECT * FROM notifications WHERE recipient_role = 'agent' OR recipient_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $agent_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Notifications</title>
  <link rel="stylesheet" href="../../../assets/css/agent_notifications.css"> <!-- Dashboard CSS -->
  <link rel="stylesheet" href="../../../assets/css/agentsidebar.css"> <!-- Sidebar CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="dashboard-container">
    <?php include 'agentsidebar.php'; ?>

    <main class="dashboard-content">
      <section class="dashboard-section">
        <h1><i class="fas fa-bell"></i> Notifications</h1>
        <p>Stay informed with the latest updates from the admin.</p>

        <div class="notification-list">
          <?php if ($result->num_rows > 0): ?>
            <?php while ($notification = $result->fetch_assoc()): ?>
              <div class="notification-item">
                <h3><?php echo htmlspecialchars($notification['title']); ?></h3>
                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                <span class="notification-time">
                  <?php echo date("F j, Y, g:i a", strtotime($notification['created_at'])); ?>
                </span>
              </div>
            <?php endwhile; ?>
          <?php else: ?>
            <p class="no-notifications">No notifications at the moment.</p>
          <?php endif; ?>
        </div>
      </section>
    </main>
  </div>
</body>
</html>
