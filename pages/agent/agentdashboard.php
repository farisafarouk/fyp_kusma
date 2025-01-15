<?php
session_start();
require_once '../../config/database.php';

// Ensure the user is logged in and has the "agent" role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Dashboard</title>
  <link rel="stylesheet" href="../../assets/css/agent_sidebar.css"> <!-- Sidebar-specific CSS -->
  <link rel="stylesheet" href="../../assets/css/agentdashboard.css"> <!-- Dashboard-specific CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include 'agentsidebar.php'; ?>

    <!-- Main Content -->
    <main class="dashboard-content">
      <!-- Commission Overview Section -->
      <section id="commission-overview" class="dashboard-section">
        <h1><i class="fas fa-wallet"></i> Commission Overview</h1>
        <p>Track your earnings based on completed customer referrals.</p>
        <button class="dashboard-btn" onclick="location.href='commission_details.php';">View Details</button>
      </section>

      <!-- User Tracking Section -->
      <section id="user-tracking" class="dashboard-section">
        <h1><i class="fas fa-users"></i> User Tracking</h1>
        <p>Monitor users who signed up using your referral code.</p>
        <button class="dashboard-btn" onclick="location.href='user_tracking.php';">View Referred Users</button>
      </section>

      <!-- Profile Management Section -->
      <section id="profile-updates" class="dashboard-section">
        <h1><i class="fas fa-user-edit"></i> Profile Updates</h1>
        <p>Add, update, or delete your personal and business information.</p>
        <button class="dashboard-btn" onclick="location.href='profile_management.php';">Edit Profile</button>
      </section>

      <!-- Referral Code Management Section -->
      <section id="referral-code-management" class="dashboard-section">
        <h1><i class="fas fa-link"></i> Referral Code Management</h1>
        <p>View and manage your unique referral code to share with customers.</p>
        <button class="dashboard-btn" onclick="location.href='referral_code.php';">Manage Referral Code</button>
      </section>

      <!-- Commission Management Section -->
      <section id="commission-management" class="dashboard-section">
        <h1><i class="fas fa-chart-line"></i> Commission Management</h1>
        <p>View detailed commission reports and request payouts.</p>
        <button class="dashboard-btn" onclick="location.href='commission_reports.php';">View Reports</button>
      </section>

      <!-- Notifications Section -->
      <section id="notifications" class="dashboard-section">
        <h1><i class="fas fa-bell"></i> Notifications</h1>
        <p>Stay updated with alerts about commission updates or new referrals.</p>
        <button class="dashboard-btn" onclick="location.href='notifications.php';">View Notifications</button>
      </section>
    </main>
  </div>
</body>
</html>
