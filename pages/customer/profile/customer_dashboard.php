<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch customer name
$stmt = $conn->prepare("SELECT pd.first_name, u.subscription_status, u.subscription_expiry FROM users u JOIN personal_details pd ON u.id = pd.user_id WHERE u.id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Count confirmed future appointments
$apptQuery = $conn->prepare("SELECT COUNT(*) AS total FROM appointments WHERE customer_id = ? AND status = 'confirmed' AND scheduled_date >= CURDATE()");
$apptQuery->bind_param("i", $user_id);
$apptQuery->execute();
$apptResult = $apptQuery->get_result()->fetch_assoc();
$totalAppointments = $apptResult['total'] ?? 0;

$sqlUser = "SELECT u.*, pd.*, bd.*, er.*
            FROM users u
            LEFT JOIN personal_details pd ON u.id = pd.user_id
            LEFT JOIN business_details bd ON u.id = bd.user_id
            LEFT JOIN education_resources er ON u.id = er.user_id
            WHERE u.id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$userProfile = $stmtUser->get_result()->fetch_assoc();

$birth_date = new DateTime($userProfile['birthdate']);
$today = new DateTime();
$age = $today->diff($birth_date)->y;

function ageMatches($age, $criteria) {
    if (empty($criteria['age'])) return true;
    foreach ($criteria['age'] as $range) {
        [$min, $max] = explode('-', str_replace('Above ', '', $range));
        if ($age >= (int)$min && $age <= (int)$max) return true;
    }
    return false;
}

function explainMatch($criteria, $user, $age) {
    $explanations = [];
    if (!empty($criteria['gender']) && in_array($user['gender'], $criteria['gender'])) $explanations[] = 'gender';
    if (!empty($criteria['bumiputera_status']) && in_array($user['bumiputera_status'], $criteria['bumiputera_status'])) $explanations[] = 'Bumiputera status';
    if (isset($criteria['oku_status']) && $user['oku_status'] == $criteria['oku_status']) $explanations[] = 'OKU status';
    if (!empty($criteria['business_type']) && in_array($user['business_type'], $criteria['business_type'])) $explanations[] = 'business type';
    if (!empty($criteria['business_experience']) && in_array($user['business_experience'], $criteria['business_experience'])) $explanations[] = 'business experience';
    if (!empty($criteria['education_type']) && in_array($user['education_type'], $criteria['education_type'])) $explanations[] = 'education background';
    if (!empty($criteria['certification_level']) && in_array($user['certification_level'], $criteria['certification_level'])) $explanations[] = 'certification level';
    if (ageMatches($age, $criteria)) $explanations[] = 'age';
    return $explanations;
}

$topProgram = null;
$topScore = 0;
$topExplanation = [];

$sqlPrograms = "SELECT p.*, a.logo_url AS agency_logo FROM programs p JOIN agencies a ON p.agency_id = a.id";
$result = $conn->query($sqlPrograms);
while ($program = $result->fetch_assoc()) {
    $criteria = json_decode($program['eligibility_criteria'], true);
    $explanation = explainMatch($criteria, $userProfile, $age);
    if (count($explanation) > $topScore) {
        $topScore = count($explanation);
        $topProgram = $program;
        $topExplanation = $explanation;
    }
}
$totalRecommendations = $topScore > 0 ? 1 : 0;

$suggestions = [];
if ($totalAppointments == 0) {
    $suggestions[] = [
        'text' => "You haven't booked any consultant yet.",
        'link' => '../booking/consultant_list.php',
        'action' => 'Book Now'
    ];
}
if ($user['subscription_status'] === 'free') {
    $suggestions[] = [
        'text' => "Upgrade to Premium to unlock more recommendations.",
        'link' => '../payment/manage_subscription.php',
        'action' => 'Upgrade Now'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8">
  <title>Customer Dashboard</title>
  <link rel="stylesheet" href="../../../assets/css/customer_navbar.css">
  <link rel="stylesheet" href="../../../assets/css/customer_dashboard.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include '../customer_navbar.php'; ?>
<div class="dashboard-container">
  <header class="dashboard-header text-center">
    <h1 class="title">👋 Welcome, <?= htmlspecialchars($user['first_name']) ?>!</h1>
    <p class="subtitle">Here's your personalized dashboard summary.</p>
  </header>

  <div class="dashboard-grid">
    <div class="dashboard-card">
      <i class="fas fa-user-shield card-icon"></i>
      <h2>Subscription</h2>
      <p>Status: <span class="badge <?= $user['subscription_status'] === 'subscribed' ? 'badge-premium' : 'badge-free' ?>"><?= ucfirst($user['subscription_status']) ?></span></p>
      <?php if ($user['subscription_expiry']): ?>
      <p>Expires: <?= date('F j, Y', strtotime($user['subscription_expiry'])) ?></p>
      <?php endif; ?>
      <a href="../payment/manage_subscription.php" class="dashboard-btn">Manage Subscription</a>
    </div>

    <div class="dashboard-card">
      <i class="fas fa-calendar-check card-icon"></i>
      <h2>Appointments</h2>
      <p><strong><?= $totalAppointments ?></strong> confirmed appointments upcoming.</p>
      <a href="../booking/customer_appointments.php" class="dashboard-btn">View Appointments</a>
    </div>

    <div class="dashboard-card">
      <i class="fas fa-lightbulb card-icon"></i>
      <h2>Recommendations</h2>
      <p><strong><?= $totalRecommendations ?></strong> top program matched your profile.</p>
      <a href="../recommendations.php" class="dashboard-btn">See Recommendations</a>
    </div>

    <div class="dashboard-card">
      <i class="fas fa-user-cog card-icon"></i>
      <h2>Profile</h2>
      <p>Review and update your personal, business and education details.</p>
      <a href="manage_profile.php" class="dashboard-btn">Edit Profile</a>
    </div>
  </div>

  <?php if ($topProgram): ?>
  <div class="highlight-card">
    <h2>🌟 Top Recommendation</h2>
    <img src="/fyp_kusma/<?= htmlspecialchars($topProgram['agency_logo']) ?>" class="top-agency-logo" alt="Agency Logo">
    <h3><?= htmlspecialchars($topProgram['name']) ?></h3>
    <p class="explanation">You're eligible based on: <?= implode(', ', $topExplanation) ?>.</p>
    <a href="<?= htmlspecialchars($topProgram['application_link']) ?>" target="_blank" class="dashboard-btn">Learn More</a>
  </div>
  <?php endif; ?>

  <?php if (!empty($suggestions)): ?>
  <div class="suggestion-card">
    <h2>💡 Smart Suggestions</h2>
    <div class="suggestion-list">
      <?php foreach ($suggestions as $sug): ?>
        <div class="suggestion-item">
          <?= htmlspecialchars($sug['text']) ?>
          <a href="<?= $sug['link'] ?>" class="dashboard-btn small"><?= $sug['action'] ?></a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>


 <div class="chart-section">
  <h2>📈 Appointment Activity</h2>
  <p class="chart-description">
    This chart visualizes how many confirmed appointments you've had over the past 6 months.
    Use this to track your engagement and consultant activity.
  </p>
  <canvas id="activityChart" height="120"></canvas>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
  fetch("customer_chart_data.php")
    .then(res => res.json())
    .then(data => {
      const labels = data.map(item => item.month);
      const counts = data.map(item => item.count);
      new Chart(document.getElementById("activityChart"), {
        type: 'bar',
        data: {
          labels: labels,
          datasets: [{
            label: 'Appointments',
            backgroundColor: '#6610f2',
            data: counts,
            borderRadius: 6
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1 }
            }
          },
          responsive: true,
          plugins: {
            legend: { display: false }
          }
        }
      });
    });
});
</script>
</body>
</html>
