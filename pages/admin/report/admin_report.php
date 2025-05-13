<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

// Get total subscribed users (those who paid RM99.90)
$subscribersQuery = "SELECT COUNT(*) AS count FROM users WHERE subscription_status = 'subscribed'";
$subscribersResult = $conn->query($subscribersQuery);
$totalSubscribers = $subscribersResult->fetch_assoc()['count'] ?? 0;

// Revenue based on paid subscriptions (RM99.90 each)
$subscriptionPrice = 99.90;
$totalRevenue = $totalSubscribers * $subscriptionPrice;

// Total Approved Referrals
$referralsQuery = "SELECT COUNT(*) AS count FROM referrals WHERE status = 'approved'";
$referralsResult = $conn->query($referralsQuery);
$totalReferrals = $referralsResult->fetch_assoc()['count'] ?? 0;

// Total Commission Paid (RM1.00 each)
$commissionQuery = "SELECT COUNT(*) * 1.00 AS total_paid FROM referrals WHERE commission_status = 'paid'";
$commissionResult = $conn->query($commissionQuery);
$totalCommission = $commissionResult->fetch_assoc()['total_paid'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>KUSMA Report</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css" />
  <link rel="stylesheet" href="../../../assets/css/admin_report.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="dashboard-container">
    <?php include '../adminsidebar.php'; ?>

    <main class="dashboard-content">
      <section class="dashboard-section">
        <h1><i class="fas fa-chart-line"></i> KUSMA Reports</h1>
        <p>This dashboard provides visual insights into customer subscriptions, referral activity, and financial summaries.</p>

        <!-- Summary Cards -->
        <div class="summary-cards">
          <div class="card">
            <h3>💰 Total Revenue</h3>
            <p>RM <?= number_format($totalRevenue, 2) ?></p>
          </div>
          <div class="card">
            <h3>📈 Subscribed Users</h3>
            <p><?= $totalSubscribers ?> customers</p>
          </div>
          <div class="card">
            <h3>🤝 Referrals</h3>
            <p><?= $totalReferrals ?> approved</p>
          </div>
          <div class="card">
            <h3>🧾 Commission Paid</h3>
            <p>RM <?= number_format($totalCommission, 2) ?></p>
          </div>
        </div>

        <!-- Chart (static for now) -->
        <canvas id="adminChart" height="100"></canvas>
      </section>
    </main>
  </div>

  <script>
    const ctx = document.getElementById('adminChart').getContext('2d');
    const adminChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [
          {
            label: 'Revenue (RM)',
            data: [120, 230, 310, 280, 420, 500],
            borderColor: '#6610f2',
            fill: false
          },
          {
            label: 'Subscribers',
            data: [5, 10, 15, 13, 18, 22],
            borderColor: '#0dcaf0',
            fill: false
          },
          {
            label: 'Referrals',
            data: [2, 4, 6, 5, 8, 10],
            borderColor: '#28a745',
            fill: false
          },
          {
            label: 'Commissions (RM)',
            data: [2.00, 4.00, 6.00, 5.00, 8.00, 10.00],
            borderColor: '#ff4d4d',
            fill: false
          }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: 'Performance Overview' }
        }
      }
    });
  </script>
</body>
</html>
