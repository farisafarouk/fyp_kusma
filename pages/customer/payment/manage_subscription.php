<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Downgrade logic via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['downgrade_confirm']) && $_POST['downgrade_confirm'] === 'yes') {
    $conn->query("UPDATE users SET subscription_status = 'free', subscription_expiry = NULL WHERE id = $user_id");
    header("Location: manage_subscription.php?status=downgraded");
    exit();
}

// Fetch subscription status and expiry
$sql = "SELECT subscription_status, subscription_expiry, subscription_updated_at FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$status = $user['subscription_status'];
$expiry = $user['subscription_expiry'] ? new DateTime($user['subscription_expiry']) : null;
$updated = $user['subscription_updated_at'] ? new DateTime($user['subscription_updated_at']) : null;
$today = new DateTime();

// Auto downgrade if expired
if ($status === 'subscribed' && $expiry && $expiry < $today) {
    $conn->query("UPDATE users SET subscription_status = 'free', subscription_expiry = NULL WHERE id = $user_id");
    $status = 'free';
    $expiry = null;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>Manage Subscription - KUSMA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/customer_subscription.css">
      <link rel="stylesheet" href="../../../assets/css/customer_navbar.css">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">


</head>

<body>
<?php include '../customer_navbar.php'; ?>

<div class="dashboard-container">
  <div class="dashboard-content">
    <section class="dashboard-section">
<?php if (isset($_GET['status']) && $_GET['status'] === 'downgraded'): ?>
        <div class="status-banner">Your subscription has been downgraded successfully.</div>
        
    <?php endif; ?>

        <h1>Manage Your Subscription</h1>

        <div class="status-box">
            <p><strong>Status:</strong>
                <span class="badge <?= $status === 'subscribed' ? 'badge-premium' : 'badge-free' ?>">
                    <?= ucfirst($status) ?>
                </span>
            </p>
            <?php if ($status === 'subscribed' && $expiry): ?>
                <p><strong>Expires on:</strong> <?= $expiry->format('F j, Y') ?></p>
            <?php endif; ?>
            <?php if ($updated): ?>
                <p><strong>Last Updated:</strong> <?= $updated->format('F j, Y') ?></p>
            <?php endif; ?>
        </div>

        <div class="actions">
            <?php if ($status === 'free'): ?>
                <a href="upgrade.php" class="btn btn-primary">Upgrade to Premium</a>
            <?php else: ?>
                <?php if ($expiry && $today->diff($expiry)->days <= 30): ?>
                    <a href="upgrade.php" class="btn btn-warning">Renew Subscription</a>
                <?php endif; ?>
                <button class="btn btn-danger" onclick="openModal()">Downgrade to Free</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal -->
    <div id="downgradeModal" class="modal">
        <div class="modal-content">
            <h2>Are you sure you want to downgrade?</h2>
            <p>This action will cancel your premium access immediately.</p>
            <form method="POST">
                <input type="hidden" name="downgrade_confirm" value="yes">
                <div class="modal-buttons">
                    <button type="submit" class="btn btn-danger">Yes, Downgrade</button>
                    <button type="button" class="btn btn-primary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('downgradeModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('downgradeModal').style.display = 'none';
        }

        window.onclick = function(event) {
            const modal = document.getElementById('downgradeModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>