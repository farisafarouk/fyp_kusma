<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Total referral earnings
$stmt1 = $conn->prepare("SELECT referral_earnings FROM agents WHERE user_id = ?");
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$referralEarnings = $stmt1->get_result()->fetch_assoc()['referral_earnings'] ?? 0.00;

// Total referrals
$stmt2 = $conn->prepare("SELECT COUNT(*) as total FROM referrals WHERE agent_id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$totalReferrals = $stmt2->get_result()->fetch_assoc()['total'];

// Pending commissions
$stmt3 = $conn->prepare("SELECT COUNT(*) as pending FROM referrals WHERE agent_id = ? AND commission_status = 'unpaid'");
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$pendingCommissions = $stmt3->get_result()->fetch_assoc()['pending'];

// Notifications
$stmt4 = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ?");
$stmt4->bind_param("i", $user_id);
$stmt4->execute();
$notifications = $stmt4->get_result()->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/agent_sidebar.css">
    <link rel="stylesheet" href="../../assets/css/agentdashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php include 'agentsidebar.php'; ?>
    <main class="dashboard-content">
        <section class="dashboard-section">
            <h1><i class="fas fa-user-shield"></i> Welcome, Agent</h1>
            <p>This is your main dashboard. Monitor your performance and track your referrals.</p>

            <div class="dashboard-cards">
                <div class="dashboard-card purple">
                    <i class="fas fa-users"></i>
                    <h2><?= $totalReferrals ?></h2>
                    <p>Total Referrals</p>
                </div>
                <div class="dashboard-card green">
                    <i class="fas fa-coins"></i>
                    <h2>RM <?= number_format($referralEarnings, 2) ?></h2>
                    <p>Total Earnings</p>
                </div>
                <div class="dashboard-card orange">
                    <i class="fas fa-clock"></i>
                    <h2><?= $pendingCommissions ?></h2>
                    <p>Pending Commissions</p>
                </div>
                <div class="dashboard-card blue">
                    <i class="fas fa-bell"></i>
                    <h2><?= $notifications ?></h2>
                    <p>Notifications</p>
                </div>
            </div>

            <div class="dashboard-links">
                <a href="agent_commission_report.php" class="dashboard-link"><i class="fas fa-chart-line"></i> View Commission Graph</a>
                <a href="agent_referral_report.php" class="dashboard-link"><i class="fas fa-table"></i> View Referral Report</a>
                <a href="agentnotifications.php" class="dashboard-link"><i class="fas fa-bell"></i> View Notifications</a>
            </div>
        </section>
    </main>
</div>
</body>
</html>
