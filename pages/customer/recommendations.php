<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login/login.php");
    exit();
}

function formatCurrencyShort($number) {
  if ($number >= 1000000) return 'RM' . round($number / 1000000, 1) . 'M';
  if ($number >= 1000) return 'RM' . round($number / 1000, 1) . 'K';
  return 'RM' . number_format($number);
}

function formatLoanRange($min, $max) {
    return formatCurrencyShort($min) . ' - ' . formatCurrencyShort($max);
}

function renderTypeBadge($type) {
  $type = strtolower($type);
  $colors = [
    'loan' => 'badge-loan',
    'grant' => 'badge-grant',
    'training' => 'badge-training',
    'premises' => 'badge-mentorship',
    'other' => 'badge-other'
  ];
  $class = $colors[$type] ?? 'badge-other';
  return "<span class='badge-type {$class}'>" . ucfirst($type) . "</span>";
}

function ageMatches($age, $criteria) {
    if (empty($criteria['age'])) return true;
    foreach ($criteria['age'] as $range) {
        [$min, $max] = explode('-', str_replace('Above ', '', $range));
        if ($age >= (int)$min && $age <= (int)$max) {
            return true;
        }
    }
    return false;
}

function explainMatch($criteria, $user, $age, $types) {
    $explanations = [];
    if (!empty($criteria['gender']) && in_array($user['gender'], $criteria['gender'])) $explanations[] = " You're a " . $user['gender'];
    if (!empty($criteria['bumiputera_status']) && in_array($user['bumiputera_status'], $criteria['bumiputera_status'])) $explanations[] = " You're a " . $user['bumiputera_status'];
    if (isset($criteria['oku_status']) && $user['oku_status'] == 1 && $criteria['oku_status'] == true) $explanations[] = " OKU status matched";
    if (!empty($criteria['business_type']) && in_array($user['business_type'], $criteria['business_type'])) $explanations[] = " Your business type qualifies";
    if (!empty($criteria['business_experience']) && in_array($user['business_experience'], $criteria['business_experience'])) $explanations[] = " Your business experience qualifies";
    if (!empty($criteria['education_type']) && in_array($user['education_type'], $criteria['education_type'])) $explanations[] = " Education background matches";
    if (!empty($criteria['certification_level']) && in_array($user['certification_level'], $criteria['certification_level'])) $explanations[] = " Certification level matches";
    if (ageMatches($age, $criteria)) $explanations[] = " You're aged $age";

    // Show loan range preference only for loan/grant
    $typesLower = array_map('strtolower', $types);
    if (!empty($user['preferred_loan_range']) && (in_array('loan', $typesLower) || in_array('grant', $typesLower))) {
      $range = explode("-", $user['preferred_loan_range']);
      if (count($range) == 2) {
        $formattedRange = formatCurrencyShort((int)$range[0]) . " - " . formatCurrencyShort((int)$range[1]);
        $explanations[] = " You prefer resource of " . $formattedRange;
      } else {
        $explanations[] = " You prefer loans of " . $user['preferred_loan_range'];
      }
    }

    return $explanations;
}

$user_id = $_SESSION['user_id'];

$sqlUser = "SELECT u.subscription_status, pd.*, bd.*, er.*
            FROM users u
            LEFT JOIN personal_details pd ON u.id = pd.user_id
            LEFT JOIN business_details bd ON u.id = bd.user_id
            LEFT JOIN education_resources er ON u.id = er.user_id
            WHERE u.id = ?";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$userProfile = $stmtUser->get_result()->fetch_assoc();

$programs = [];
if ($userProfile) {
    $birth_date = new DateTime($userProfile['birthdate']);
    $current_date = new DateTime();
    $age = $current_date->diff($birth_date)->y;
    $subscription = $userProfile['subscription_status'];

    $sqlPrograms = "SELECT p.*, a.logo_url AS agency_logo FROM programs p JOIN agencies a ON p.agency_id = a.id";
    $result = $conn->query($sqlPrograms);

    if ($result) {
        $count = 0;
        while ($program = $result->fetch_assoc()) {
            $criteria = json_decode($program['eligibility_criteria'], true);
            $types = explode(',', $program['resource_types']);
            $program['explanation'] = explainMatch($criteria, $userProfile, $age, $types);
            $program['score'] = count($program['explanation']);

            $program['show_amount'] = in_array('Loan', $types) || in_array('Grant', $types);
            $program['locked'] = ($subscription === 'free' && $count >= 2);

            if ($program['score'] > 0) {
                $programs[] = $program;
                $count++;
            }
        }
        usort($programs, fn($a, $b) => $b['score'] <=> $a['score']);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recommendations - KUSMA</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
  
  <link rel="stylesheet" href="../../assets/css/customer_navbar.css" />
  <link rel="stylesheet" href="../../assets/css/recommendations.css" />
</head>
<body>

<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="navbar">
  <div class="navbar-container">
    <button class="navbar-toggle"><i class="fas fa-bars"></i></button>
    <div class="navbar-content">
    <ul class="navbar-menu">
      <li>
        <a href="/fyp_kusma/pages/customer/profile/customer_dashboard.php" class="<?= $current_page === 'customer_dashboard.php' ? 'active' : '' ?>">
          <i class="fas fa-home"></i><span> Dashboard</span>
        </a>
      </li>
      <li>
        <a href="/fyp_kusma/pages/customer/booking/consultant_list.php" class="<?= $current_page === 'consultant_list.php' ? 'active' : '' ?>">
          <i class="fas fa-user-md"></i><span> Book</span>
        </a>
      </li>
      <li>
        <a href="/fyp_kusma/pages/customer/booking/customer_appointments.php" class="<?= $current_page === 'customer_appointments.php' ? 'active' : '' ?>">
          <i class="fas fa-calendar-check"></i><span> Appointments</span>
        </a>
      </li>
      <li>
        <a href="/fyp_kusma/pages/customer/payment/manage_subscription.php" class="<?= $current_page === 'manage_subscription.php' ? 'active' : '' ?>">
          <i class="fas fa-wallet"></i><span> Subscription</span>
        </a>
      </li>
      <li>
        <a href="/fyp_kusma/pages/customer/recommendations.php" class="<?= $current_page === 'recommendations.php' ? 'active' : '' ?>">
          <i class="fas fa-lightbulb"></i><span> Recommendations</span>
        </a>
      </li>
      <li>
        <a href="/fyp_kusma/pages/customer/profile/manage_profile.php" class="<?= $current_page === 'manage_profile.php' ? 'active' : '' ?>">
          <i class="fas fa-user-circle"></i><span> Profile</span>
        </a>
      </li>
      <li>
        <a href="/fyp_kusma/pages/customer/notifications/notification_cust.php" class="<?= $current_page === 'notification_cust.php' ? 'active' : '' ?>">
          <i class="fas fa-bell"></i><span> Notifications</span>
        </a>
      </li>
      <li>
        <a href="/fyp_kusma/pages/login/login.php" class="logout-link">
          <i class="fas fa-sign-out-alt"></i><span> Logout</span>
        </a>
      </li>
    </ul>
  </div>
</nav>







<div class="dashboard-container">
  <header class="dashboard-header text-center">
    <h1 class="title">Tailored Resources Just for You</h1>
    <p class="subtitle">Explore curated opportunities designed to help you succeed.</p>
  </header>

  <div class="search-container">
  <input type="text" id="searchInput" placeholder="Search by program, agency, or type..." />
</div>


  <div class="dashboard-sections" id="programCards">
    <?php if (empty($programs)): ?>
      <div class="dashboard-card" style="width: 100%;">
        <p class="text-center">No resources matched your profile. Update your preferences for better results.</p>
      </div>
    <?php else: ?>
      <?php foreach ($programs as $index => $program): ?>
        <div class="dashboard-card">
  <div class="card-top-bar">
    <?php
  $types = explode(',', $program["resource_types"]);
  foreach ($types as $type) {
    echo renderTypeBadge(trim($type)) . ' ';
  }
?>
    
  </div>
          <div class="recommendation-content <?= ($userProfile['subscription_status'] === 'free' && $index >= 2) ? 'blurred' : '' ?>">
            <div class="card-icon" style="margin-bottom: 10px;">
              <img src="/fyp_kusma/<?= htmlspecialchars($program['agency_logo']) ?>" alt="Agency Logo" style="width: 40px; height: 40px;">
            </div>
            <h2 style="margin-bottom: 10px; font-size: 18px;"><?= htmlspecialchars($program['name']) ?></h2>
            <p style="font-size: 14px; color: #555;"><?= htmlspecialchars($program['description']) ?></p>
           <?php if ($program['show_amount']): ?>
  <div style="font-size: 14px; margin: 10px 0;">
    <strong>Resource Range:</strong> <?= formatLoanRange($program['min_loan_amount'], $program['max_loan_amount']) ?>
  </div>
<?php endif; ?>

            <div class="explanation-box" style="background: #f8f8ff; border-radius: 10px; padding: 10px; margin-top: 10px;">
              <strong>🔍 You qualify for this program because:</strong>
              <ul style="text-align: left; margin-top: 8px;">
                <?php foreach ($program['explanation'] as $reason): ?>
                  <li><?= $reason ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </div>

          <?php if ($userProfile['subscription_status'] === 'free' && $index >= 2): ?>
            <div class="lock-overlay">
              <i class="fas fa-lock"></i>
            </div>
          <?php endif; ?>

          <div class="unlock-wrapper" style="margin-top: 15px;">
            <?php if ($userProfile['subscription_status'] === 'free' && $index >= 2): ?>
              <a href="../customer/payment/upgrade.php" class="dashboard-btn">Unlock Full Access</a>
            <?php else: ?>
              <a href="<?= htmlspecialchars($program['application_link']) ?>" target="_blank" class="dashboard-btn">Learn More</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
      searchInput.addEventListener('input', () => {
        const value = searchInput.value.toLowerCase();
        document.querySelectorAll('.dashboard-card').forEach(card => {
          card.style.display = card.textContent.toLowerCase().includes(value) ? 'block' : 'none';
        });
      });
    }
  });


  document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.querySelector(".navbar-toggle");
    const content = document.querySelector(".navbar-content");
    if (toggle && content) {
      toggle.addEventListener("click", () => {
        content.classList.toggle("open");
      });
    }
  });


</script>
</body>
</html>