<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch monthly commissions (paid only)
$sql = "
    SELECT DATE_FORMAT(referral_date, '%Y-%m') AS month, COUNT(*) AS referrals, 
           COUNT(*) * 1.00 AS earnings
    FROM referrals
    WHERE agent_id = ? AND commission_status = 'paid'
    GROUP BY month
    ORDER BY month ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$months = [];
$earnings = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $months[] = $row['month'];
    $earnings[] = (float)$row['earnings'];
    $total += (float)$row['earnings'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Agent Commission Graph</title>
  <link rel="stylesheet" href="../../../assets/css/agent_sidebar.css">
  <link rel="stylesheet" href="../../../assets/css/agent_commission_report.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="dashboard-container">
  <?php include '../agentsidebar.php'; ?>
  <main class="dashboard-content">
    <section class="dashboard-section">
      <h1><i class="fas fa-chart-bar"></i> Monthly Commission Report</h1>
      <p>Visual overview of your monthly earnings based on customer referrals.</p>
      <canvas id="commissionChart" height="100"></canvas>
      <p class="total-summary">💰 Total Commission Earned: <strong>RM <?= number_format($total, 2) ?></strong></p>
    </section>
  </main>
</div>

<script>
  const ctx = document.getElementById('commissionChart').getContext('2d');
  const chart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($months) ?>,
      datasets: [{
        label: 'Commission Earned (RM)',
        data: <?= json_encode($earnings) ?>,
        backgroundColor: '#6610f2'
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          title: {
            display: true,
            text: 'RM'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Month'
          }
        }
      }
    }
  });
</script>
</body>
</html>
