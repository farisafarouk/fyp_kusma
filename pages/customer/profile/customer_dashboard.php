<?php
session_start();
require_once '../../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  header("Location: ../../login/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, subscription_status, subscription_expiry, form_status FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Customer Dashboard</title>
  <link rel="stylesheet" href="../../../assets/css/customer_navbar.css" />
  <link rel="stylesheet" href="../../../assets/css/customer_dashboard.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
</head>
<body>
<?php include '../customer_navbar.php'; ?>

<div class="dashboard-container">
  <div class="dashboard-content">
    <section class="dashboard-section">
      <header>
        <h1>👋 Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
        <p class="muted">Here's your personalized dashboard overview.</p>
      </header>

      <div class="badge-group">
        <span class="badge <?= $user['subscription_status'] === 'subscribed' ? 'badge-success' : 'badge-warning' ?>">
          <?= ucfirst($user['subscription_status']) ?> Member
          <?php if ($user['subscription_status'] === 'subscribed' && $user['subscription_expiry']) echo " - Expires: " . $user['subscription_expiry']; ?>
        </span>
        <span class="badge badge-muted">Profile Status: <?= ucfirst($user['form_status']) ?></span>
      </div>

      <div class="report-grid">
        <div class="stat-card">
          <h3>📚 Matched Recommendations</h3>
          <p id="recommendationCount">-</p>
        </div>
        <div class="stat-card">
          <h3>📅 Next Appointment</h3>
          <p id="nextAppointment">-</p>
        </div>
        <div class="stat-card">
          <h3>📋 Profile Completion</h3>
          <p id="profileSteps">-</p>
        </div>
      </div>

      <div class="quick-links">
        <a href="../booking/consultant_list.php" class="quick-link">
          <i class="fas fa-calendar-plus"></i>
          <span>Book Appointment</span>
        </a>
        <a href="../recommendations/recommendations.php" class="quick-link">
          <i class="fas fa-lightbulb"></i>
          <span>View Recommendations</span>
        </a>
        <a href="../manage_profile.php" class="quick-link">
          <i class="fas fa-user-cog"></i>
          <span>Manage Profile</span>
        </a>
        <a href="../subscription/manage_subscription.php" class="quick-link">
          <i class="fas fa-sync-alt"></i>
          <span>Renew Subscription</span>
        </a>
        <a href="../notifications.php" class="quick-link">
          <i class="fas fa-bell"></i>
          <span>Notifications</span>
        </a>
      </div>
    </section>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  fetch("fetch_dashboard_data.php")
    .then(res => res.json())
    .then(data => {
      document.getElementById("recommendationCount").textContent = data.recommendation_count || 0;
      document.getElementById("nextAppointment").textContent = data.upcoming_date || "-";

      let steps = [];
      if (data.has_personal) steps.push("Personal ✔️");
      if (data.has_business) steps.push("Business ✔️");
      if (data.has_education) steps.push("Education ✔️");
      document.getElementById("profileSteps").textContent = steps.length > 0 ? steps.join(", ") : "Incomplete";
    });
});
</script>
</body>
</html>
