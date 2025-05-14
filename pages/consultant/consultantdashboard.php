<?php
session_start();
require_once '../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    header("Location: ../login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Consultant Dashboard</title>
  <link rel="stylesheet" href="../../assets/css/consultantsidebar.css" />
  <link rel="stylesheet" href="../../assets/css/consultantdashboard.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>
<div class="dashboard-container">
  <?php include 'consultantsidebar.php'; ?>
  <div class="dashboard-content">
    <section class="dashboard-section">
      <div class="welcome-banner">
        <h1>👋 Hello, <span id="consultantName">Consultant</span></h1>
        <p>Here’s your snapshot for <span id="todayDate"></span></p>
      </div>

      <div class="summary-bar">
        <div><strong id="todayAppointments">0</strong><span>Today</span></div>
        <div><strong id="monthlyAppointments">0</strong><span>This Month</span></div>
        <div><strong id="averageRating">-</strong><span>Avg. Rating</span></div>
      </div>

      <div class="quick-actions">
        <button onclick="location.href='consultant_schedule.php'"><i class="fas fa-calendar-plus"></i> Schedule Availability</button>
        <button onclick="location.href='consultant_reports.php'"><i class="fas fa-chart-line"></i> View Reports</button>
        <button onclick="location.href='consultant_profile.php'"><i class="fas fa-user-cog"></i> Manage Profile</button>
      </div>

      <div class="section-block">
        <h2><i class="fas fa-calendar-alt"></i> Upcoming Appointments</h2>
        <div id="upcomingList" class="entry-list"></div>
      </div>

      <div class="section-block">
        <h2><i class="fas fa-comments"></i> Recent Feedback</h2>
        <div id="feedbackList" class="entry-list"></div>
      </div>
    </section>
  </div>
</div>

<script>
function formatDate(date) {
  return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

document.addEventListener('DOMContentLoaded', () => {
  const today = new Date();
  document.getElementById('todayDate').textContent = formatDate(today);

  fetch('fetch_dashboard_data.php')
    .then(res => res.json())
    .then(data => {
      document.getElementById('consultantName').textContent = data.consultantName;
      document.getElementById('todayAppointments').textContent = data.todayAppointments || 0;
      document.getElementById('monthlyAppointments').textContent = data.monthlyAppointments || 0;
      document.getElementById('averageRating').textContent = data.averageRating?.toFixed(2) || '-';

      const upcomingContainer = document.getElementById('upcomingList');
      if (!data.upcomingAppointments.length) {
        upcomingContainer.innerHTML = '<div class="empty-state">No upcoming appointments today.</div>';
      } else {
        data.upcomingAppointments.slice(0, 3).forEach(app => {
          const div = document.createElement('div');
          div.className = 'entry-card';
          div.innerHTML = `<strong>${app.customer_name}</strong> • ${app.time} (${app.mode})<br><span>${app.reason}</span>`;
          upcomingContainer.appendChild(div);
        });
      }

      const feedbackContainer = document.getElementById('feedbackList');
      if (!data.recentFeedback.length) {
        feedbackContainer.innerHTML = '<div class="empty-state">No feedback yet.</div>';
      } else {
        data.recentFeedback.slice(0, 3).forEach(fb => {
          const div = document.createElement('div');
          div.className = 'entry-card';
          div.innerHTML = `<strong>${fb.customer_name}</strong> • ${fb.date}<br><span>${fb.feedback}</span>`;
          feedbackContainer.appendChild(div);
        });
      }
    });
});
</script>
</body>
</html>
