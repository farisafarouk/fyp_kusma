<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../../login/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT subscription_status FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
$currentPlan = $user['subscription_status'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upgrade Subscription - KUSMA</title>
  <link rel="stylesheet" href="../../../assets/css/upgrade.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="upgrade-container">
  <div style="text-align: left; margin-bottom: 15px;">
    <button onclick="confirmBack()" style="background: none; border: none; color: #5e72e4; font-size: 14px; cursor: pointer; text-decoration: underline;">
      ← Back
    </button>
  </div>

  <h2>Upgrade Your Subscription</h2>
  <p class="subtitle">Choose a plan that suits your needs and unlock premium features!</p>

  <div class="plans-wrapper">
    <div class="plan-card <?= $currentPlan === 'free' ? 'current' : '' ?>">
      <h3>Free Plan</h3>
      <div class="plan-price">RM0<span>/forever</span></div>
      <ul>
        <li><span class="tick"></span> Access to 2 recommendations</li>
        <li><span class="tick"></span> Book consultants</li>
        <li><span class="cross">✖</span> Full resource access</li>
      </ul>
      <?php if ($currentPlan === 'free'): ?>
        <span class="badge-current">Your Plan</span>
      <?php endif; ?>
    </div>

    <div class="plan-card <?= $currentPlan === 'premium' ? 'current' : '' ?>">
      <h3>Premium Plan</h3>
      <div class="plan-price">RM99<span>/year</span></div>
      <ul>
        <li><span class="tick"></span> Unlimited recommendations</li>
        <li><span class="tick"></span> Book consultants</li>
        <li><span class="tick"></span> Full access to all programs</li>
        <li><span class="tick"></span> Personalized support</li>
      </ul>
      <?php if ($currentPlan === 'premium'): ?>
        <span class="badge-current">Your Plan</span>
      <?php else: ?>
        <button class="dashboard-btn" onclick="confirmUpgrade()">Upgrade Now</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function confirmUpgrade() {
  Swal.fire({
    title: 'Confirm Upgrade',
    text: 'Proceed to payment gateway to upgrade to Premium?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#5e72e4',
    cancelButtonColor: '#ccc',
    confirmButtonText: 'Yes, upgrade!'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'simulate_gateway.php';
    }
  });
}

function confirmBack() {
  Swal.fire({
    title: 'Cancel Upgrade?',
    text: 'You will return to the subscription management page without upgrading.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#6c757d',
    cancelButtonColor: '#5e72e4',
    confirmButtonText: 'Yes, go back'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = 'manage_subscription.php';
    }
  });
}
</script>
</body>
</html>
