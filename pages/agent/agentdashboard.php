<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get agent info
$agent_sql = "SELECT id, referral_earnings, referral_code FROM agents WHERE user_id = ?";
$agent_stmt = $conn->prepare($agent_sql);
$agent_stmt->bind_param("i", $user_id);
$agent_stmt->execute();
$agent_result = $agent_stmt->get_result();
$agent = $agent_result->fetch_assoc();

$referral_earnings = $agent['referral_earnings'] ?? 0.00;
$referral_code = $agent['referral_code'] ?? '-';

$sql = "
    SELECT DATE_FORMAT(referral_date, '%b %Y') AS month, COUNT(*) AS referrals, 
           COUNT(*) * 1.00 AS earnings
    FROM referrals
    WHERE agent_id = ? AND commission_status = 'paid'
    GROUP BY month
    ORDER BY month ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id); // <-- this is correct because referrals.agent_id = agents.user_id
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

// Fetch latest 2 notifications
$notif_stmt = $conn->prepare("SELECT message FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 2");
$notif_stmt->bind_param("i", $user_id);
$notif_stmt->execute();
$notif_result = $notif_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Agent Dashboard</title>
  <link rel="stylesheet" href="../../assets/css/agent_sidebar.css">
  <link rel="stylesheet" href="../../assets/css/agentdashboard.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="dashboard-container">
  <?php include 'agentsidebar.php'; ?>
  <main class="dashboard-content">
    <section class="dashboard-header">
      <h1><i class="fas fa-tachometer-alt"></i> Agent Dashboard</h1>
      <p>Here's your latest summary including commissions and notifications.</p>
    </section>

    <div class="dashboard-cards">
      <div class="dashboard-card purple">
        <h3><i class="fas fa-wallet"></i> Total Earnings</h3>
        <p>RM <?= number_format($referral_earnings, 2) ?></p>
      </div>

      <div class="dashboard-card teal">
  <h3><i class="fas fa-code"></i> Referral Code</h3>
  <div class="referral-copy-row">
    <span class="referral-code"><?= htmlspecialchars($referral_code) ?></span>
    <input type="text" id="referralInput" value="<?= htmlspecialchars($referral_code) ?>" readonly hidden>
    <button onclick="copyReferralCode()" class="copy-btn" title="Copy">
      <i class="fas fa-copy"></i>
    </button>
  </div>
  <div id="copyTooltip" class="tooltip-text">Copied!</div>
</div>


<script>
  function copyReferralCode() {
    const input = document.getElementById("referralInput");
    const tooltip = document.getElementById("copyTooltip");

    input.removeAttribute("hidden");      // temporarily show to select
    input.select();
    input.setSelectionRange(0, 99999);    // for mobile
    document.execCommand("copy");
    input.setAttribute("hidden", true);   // hide again

    tooltip.style.opacity = 1;
    setTimeout(() => {
      tooltip.style.opacity = 0;
    }, 1000);
  }
</script>

</script>


      <div class="dashboard-card yellow">
        <h3><i class="fas fa-bell"></i> Recent Notifications</h3>
        <?php while ($row = $notif_result->fetch_assoc()): ?>
          <p>• <?= htmlspecialchars($row['message']) ?></p>
        <?php endwhile; ?>
        <a href="notifications/agentnotifications.php" class="see-all">View All</a>
      </div>
    </div>

    <section class="dashboard-section">
      <h2><i class="fas fa-chart-bar"></i> Monthly Commission Report</h2>
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
        label: 'Monthly Earnings (RM)',
        data: <?= json_encode($earnings) ?>,
        backgroundColor: 'rgba(102, 16, 242, 0.7)',
        borderRadius: 6,
        borderSkipped: false,
        barThickness: 40
      }]
    },
    options: {
      responsive: true,
      plugins: {
        title: {
          display: true,
          text: 'Monthly Commission Earnings',
          font: {
            size: 18,
            weight: 'bold'
          },
          padding: {
            top: 10,
            bottom: 30
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return 'RM ' + context.parsed.y.toFixed(2);
            }
          }
        },
        legend: {
          display: false
        }
      },
      scales: {
        x: {
          title: {
            display: true,
            text: 'Month',
            font: {
              size: 14,
              weight: 'bold'
            }
          },
          ticks: {
            font: {
              size: 12
            },
            color: '#444'
          },
          grid: {
            display: false
          }
        },
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'RM',
            font: {
              size: 14,
              weight: 'bold'
            }
          },
          ticks: {
            stepSize: 1,
            font: {
              size: 12
            },
            color: '#444',
            callback: function(value) {
              return 'RM ' + value.toFixed(2);
            }
          },
          grid: {
            color: '#eee',
            lineWidth: 1
          }
        }
      }
    }
  });
</script>

</body>
</html>
