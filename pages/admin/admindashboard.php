<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

// Get key stats
$totalCustomers = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'")->fetch_assoc()['total'] ?? 0;
$totalAgents = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'agent'")->fetch_assoc()['total'] ?? 0;
$totalConsultants = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'consultant'")->fetch_assoc()['total'] ?? 0;
$totalSubscriptions = $conn->query("SELECT COUNT(*) AS total FROM users WHERE subscription_status = 'subscribed'")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../../assets/css/adminsidebar.css" />
  <link rel="stylesheet" href="../../assets/css/admin_report.css" />
  <link rel="stylesheet" href="../../assets/css/admindashboard.css" />

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="dashboard-container">
    <?php include 'adminsidebar.php'; ?>

    <main class="dashboard-content">
      <section class="dashboard-section">
        <div class="welcome-banner">
          <h1><i class="fas fa-user-shield"></i> Welcome, Admin</h1>
          <p>Here's a quick overview of the platform performance today.</p>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
          <div class="card">
            <h3>👥 Total Customers</h3>
            <p><?= $totalCustomers ?></p>
          </div>
          <div class="card">
            <h3>🧑‍💼 Total Agents</h3>
            <p><?= $totalAgents ?></p>
          </div>
          <div class="card">
            <h3>🎓 Total Consultants</h3>
            <p><?= $totalConsultants ?></p>
          </div>
          <div class="card">
            <h3>📦 Active Subscriptions</h3>
            <p><?= $totalSubscriptions ?></p>
          </div>
        </div>

        <!-- Insights Chart -->
        <section class="chart-section">
          <h3><i class="fas fa-chart-pie"></i> User Distribution</h3>
          <canvas id="adminDashboardChart" height="90"></canvas>
        </section>
      </section>
    </main>
  </div>

  <script>
    const ctx = document.getElementById('adminDashboardChart').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Customers', 'Agents', 'Consultants'],
        datasets: [{
          label: 'Total Users by Role',
          data: [<?= $totalCustomers ?>, <?= $totalAgents ?>, <?= $totalConsultants ?>],
          backgroundColor: ['#6610f2', '#0dcaf0', '#28a745']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'User Role Overview' }
        }
      }
    });
  </script>
</body>
</html>
