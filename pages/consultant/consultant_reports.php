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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8" />
  <title>Consultant Reports</title>
  <link rel="stylesheet" href="../../assets/css/consultantsidebar.css" />
  <link rel="stylesheet" href="../../assets/css/consultant_reports.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="dashboard-container">
  <?php include 'consultantsidebar.php'; ?>
  <div class="dashboard-content">
    <section class="dashboard-section">
      <header>
        <h1><i class="fas fa-chart-line"></i> Reports & Statistics</h1>
        <p class="muted">Your consultation performance and feedback summary.</p>
      </header>

      <div class="report-grid">
        <div class="stat-card">
          <h3>Total Appointments</h3>
          <p id="totalAppointments">-</p>
        </div>
        <div class="stat-card">
          <h3>Average Rating</h3>
          <p id="averageRating">-</p>
        </div>
        <div class="stat-card">
          <h3>Completed Sessions</h3>
          <p id="completedAppointments">-</p>
        </div>
      </div>

      <div class="charts">
  <div class="chart-box">
    <h4>Monthly Appointment Trends</h4>
    <div class="chart-wrapper">
      <canvas id="appointmentsChart"></canvas>
    </div>
  </div>

  <div class="chart-box">
    <div class="chart-header">
      <h4>Ratings Distribution</h4>
      <div class="rating-summary">
        <span id="averageScore">-</span>
        <div id="averageStars" class="star-display"></div>
      </div>
    </div>
    <div class="chart-wrapper">
      <canvas id="ratingsChart"></canvas>
    </div>
  </div>
</div>
</section>
</div>
</div>


<script>
function loadReportData() {
  fetch('fetch_report_data.php')
    .then(res => res.json())
    .then(data => {
      document.getElementById('totalAppointments').textContent = data.totalAppointments || 0;
      document.getElementById('averageRating').textContent = data.averageRating?.toFixed(2) || '-';
      document.getElementById('completedAppointments').textContent = data.completedAppointments || 0;

      // Render average stars
      const average = data.averageRating?.toFixed(2) || '0.00';
      const fullStars = Math.floor(average);
      const halfStar = average - fullStars >= 0.5;
      let starsHTML = '';

      for (let i = 0; i < 5; i++) {
        if (i < fullStars) {
          starsHTML += '<span class="star full">★</span>';
        } else if (i === fullStars && halfStar) {
          starsHTML += '<span class="star half">★</span>';
        } else {
          starsHTML += '<span class="star empty">☆</span>';
        }
      }

      document.getElementById('averageScore').textContent = `${average}/5`;
      document.getElementById('averageStars').innerHTML = starsHTML;

      // Bar Chart: Appointments Per Month
      const ctx1 = document.getElementById('appointmentsChart').getContext('2d');
      new Chart(ctx1, {
        type: 'bar',
        data: {
          labels: data.months,
          datasets: [{
            label: 'Appointments Per Month',
            data: data.monthlyCounts,
            backgroundColor: 'rgba(108, 99, 255, 0.7)',
            borderRadius: 10
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false
        }
      });

      // Doughnut Chart: Ratings Distribution
      const ctx2 = document.getElementById('ratingsChart').getContext('2d');
      new Chart(ctx2, {
        type: 'doughnut',
        data: {
          labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
          datasets: [{
            label: 'Rating Distribution',
            data: data.ratingBreakdown,
            backgroundColor: [
              '#2ecc71', '#3498db', '#f1c40f', '#e67e22', '#e74c3c'
            ]
          }]
        },
        options: {
          cutout: '60%',
          responsive: true,
          maintainAspectRatio: false
        }
      });
    });
}

window.onload = loadReportData;
</script>
</body>
</html>
