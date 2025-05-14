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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Dashboard</title>
  <link rel="stylesheet" href="../../../assets/css/customer_navbar.css">
  <link rel="stylesheet" href="../../../assets/css/customer_dashboard.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>
<?php include '../customer_navbar.php'; ?>

<div class="dashboard-container">
  <div class="dashboard-content">
    <section class="dashboard-section">
      <header>
        <h1>👋 Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
        <p class="muted">Here's your personalized dashboard summary.</p>
      </header>

      <div class="summary-cards">
        <div class="summary-box">
          <h3>📜 Subscription</h3>
          <p class="big-number" id="subscriptionType">-</p>
          <p class="caption" id="expiryCountdown">Loading expiry info...</p>
        </div>
        <div class="summary-box">
          <h3>🎯 Matched Programs</h3>
          <p class="big-number" id="recommendationCount">-</p>
          <p class="caption">Based on your profile</p>
        </div>
        <div class="summary-box">
          <h3>📅 Next Appointment</h3>
          <p class="big-number" id="nextAppointment">-</p>
          <p class="caption" id="appointmentWith">Consultant info</p>
        </div>
      </div>

      <div class="progress-section">
        <h3>✅ Profile Completion</h3>
        <div class="progress-grid">
          <div class="progress-tile" id="personalStatus">Personal: -</div>
          <div class="progress-tile" id="businessStatus">Business: -</div>
          <div class="progress-tile" id="educationStatus">Education: -</div>
        </div>
      </div>

      <div class="tip-section" id="tipBox">
        <i class="fas fa-lightbulb"></i> Loading tip...
      </div>
    </section>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  fetch("fetch_dashboard_data.php")
    .then(res => res.json())
    .then(data => {
      // Subscription
      document.getElementById("subscriptionType").textContent = data.subscription_status || '-';
      document.getElementById("expiryCountdown").textContent = data.expiry_text || 'N/A';

      // Recommendation logic
      document.getElementById("recommendationCount").textContent = data.recommendation_count || 0;

      // Appointment
      document.getElementById("nextAppointment").textContent = data.upcoming_date || '-';
      document.getElementById("appointmentWith").textContent = data.consultant_name || '-';

      // Profile
      document.getElementById("personalStatus").textContent = data.has_personal ? 'Personal: ✔️' : 'Personal: ❌';
      document.getElementById("businessStatus").textContent = data.has_business ? 'Business: ✔️' : 'Business: ❌';
      document.getElementById("educationStatus").textContent = data.has_education ? 'Education: ✔️' : 'Education: ❌';

      // Tip
      const tips = [];
      if (!data.has_business) tips.push("Complete your business info for tailored programs.");
      if (data.subscription_status === 'free') tips.push("Upgrade to access full recommendations.");
      if (!data.upcoming_date) tips.push("Book a consultation to get started!");
      if (tips.length === 0) tips.push("You're all set! ✅ Keep exploring.");
      document.getElementById("tipBox").innerHTML = `<i class='fas fa-lightbulb'></i> ${tips[0]}`;
    });
});
</script>
</body>
</html>
