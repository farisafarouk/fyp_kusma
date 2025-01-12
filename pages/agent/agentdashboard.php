<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is an agent
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../../login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Dashboard</title>
  <link rel="stylesheet" href="../../../assets/css/agentdashboard.css"> <!-- Dashboard CSS -->
  <link rel="stylesheet" href="../../../assets/css/agentsidebar.css"> <!-- Sidebar CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include 'agentsidebar.php'; ?>

    <!-- Main Content -->
    <main class="dashboard-content">
      <section id="commission-overview" class="dashboard-section">
        <h1><i class="fas fa-money-check-alt"></i> Commission Overview</h1>
        <p>Track your earnings based on completed referrals.</p>
      </section>

      <section id="user-tracking" class="dashboard-section">
        <h1><i class="fas fa-users"></i> User Tracking</h1>
        <p>Monitor users who signed up using your referral code.</p>
      </section>

      <section id="profile-management" class="dashboard-section">
        <h1><i class="fas fa-user"></i> Profile Management</h1>
        <p>Manage your personal and business information.</p>
        <button class="dashboard-btn" onclick="location.href='agent_profile.php';">Manage Profile</button>
      </section>
    </main>
  </div>
</body>
</html>
