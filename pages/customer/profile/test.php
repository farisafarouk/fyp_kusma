<?php
include 'customer_navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to right, #f8f9ff, #ffffff);
      color: #333;
      margin: 0;
      padding: 0;
    }

    .dashboard-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
    }

    .greeting {
      text-align: center;
      margin-bottom: 30px;
      animation: fadeInDown 0.6s ease-out;
    }

    .greeting h1 {
      font-size: 36px;
      color: #6610f2;
      margin-bottom: 10px;
    }

    .greeting p {
      font-size: 16px;
      color: #555;
    }

    .metrics {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      justify-content: center;
      margin-bottom: 40px;
    }

    .metric-box {
      background: #fff;
      border-radius: 15px;
      padding: 20px;
      width: 260px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.07);
      text-align: center;
      animation: fadeInUp 0.8s ease;
    }

    .metric-box h2 {
      font-size: 16px;
      color: #888;
      margin-bottom: 8px;
    }

    .circle-chart {
      width: 100px;
      height: 100px;
      margin: 0 auto 10px;
    }

    .section-title {
      font-size: 20px;
      font-weight: bold;
      color: #6610f2;
      margin-bottom: 10px;
    }

    .recommendation, .activity-feed, .suggestions {
      background: #fff;
      border-radius: 15px;
      padding: 25px;
      margin-bottom: 30px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
      animation: fadeIn 1s ease;
    }

    .recommendation p,
    .activity-feed p,
    .suggestions p {
      font-size: 15px;
      color: #444;
      line-height: 1.6;
    }

    .timeline-item {
      margin-bottom: 15px;
    }

    .timeline-item span {
      font-weight: bold;
      color: #6610f2;
      margin-right: 6px;
    }

    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @media (max-width: 768px) {
      .metric-box {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-container">
    <div class="greeting">
      <h1>Hi Marissa 👋 Ready to grow your business today?</h1>
      <p>Last login: May 16, 2025 | Plan: <span class="badge badge-premium">Premium</span></p>
    </div>

    <div class="metrics">
      <div class="metric-box">
        <h2>Next Appointment</h2>
        <p>In 2 Days</p>
      </div>
      <div class="metric-box">
        <h2>Profile Completion</h2>
        <canvas id="profileChart" class="circle-chart"></canvas>
      </div>
      <div class="metric-box">
        <h2>Plan Status</h2>
        <p>Premium</p>
      </div>
    </div>

    <div class="recommendation">
      <div class="section-title">🎯 Matched Resources</div>
      <p>✓ 5 Grants 🟢 &nbsp; ✓ 3 Trainings 🟣<br>
        Based on your business profile.</p>
    </div>

    <div class="activity-feed">
      <div class="section-title">🕒 Recent Activity</div>
      <div class="timeline-item"><span>Today:</span> You booked an appointment with Dr. Salmiah</div>
      <div class="timeline-item"><span>Yesterday:</span> New notification received</div>
      <div class="timeline-item"><span>May 14:</span> Feedback submitted for last session</div>
    </div>

    <div class="suggestions">
      <div class="section-title">💡 Smart Suggestions</div>
      <p>• You haven’t completed your education details yet — update now to unlock more resources.<br>
         • Submit feedback for your last appointment and earn 2x referral points!</p>
    </div>
  </div>

  <script>
    const ctx = document.getElementById('profileChart');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Completed', 'Remaining'],
        datasets: [{
          label: 'Profile Completion',
          data: [65, 35],
          backgroundColor: ['#6610f2', '#e0e0e0'],
          borderWidth: 0
        }]
      },
      options: {
        cutout: '70%',
        plugins: {
          legend: { display: false },
          tooltip: { enabled: false },
          title: {
            display: true,
            text: '65%',
            position: 'center',
            color: '#333',
            font: { size: 18 }
          }
        }
      }
    });
  </script>
</body>
</html>
