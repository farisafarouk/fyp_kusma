<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
  header("Location: ../../login/login.php");
  exit();
}
?>
<nav class="navbar">
  <div class="container">
    <a href="../profile/customer_dashboard.php" class="navbar-brand">
      <i class="fas fa-user-circle"></i> KUSMA
    </a>
    <ul class="navbar-menu">
      <li><a href="../profile/customer_dashboard.php">Dashboard</a></li>
      <li><a href="../booking/consultant_list.php">Book Appointment</a></li>
      <li><a href="../booking/customer_appointments.php">My Appointments</a></li>
      <li><a href="../payment/manage_subscription.php">Manage Subscription</a></li>
      <li><a href="../profile/manage_profile.php">Profile</a></li>
      <li><a href="../login/login.php" class="logout-link">Logout</a></li>
    </ul>
  </div>
</nav>
