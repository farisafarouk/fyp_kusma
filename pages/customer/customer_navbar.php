<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="navbar">
  <div class="container">
    <ul class="navbar-menu">
      <li>
        <a href="../profile/user_dashboard.php" class="<?= $current_page === 'user_dashboard.php' ? 'active' : '' ?>">
          <i class="fas fa-home"></i> Dashboard
        </a>
      </li>
      <li>
        <a href="../profile/manage_profile.php" class="<?= $current_page === 'manage_profile.php' ? 'active' : '' ?>">
          <i class="fas fa-user"></i> Profile
        </a>
      </li>
      <li>
        <a href="../recommendations.php" class="<?= $current_page === 'recommendations.php' ? 'active' : '' ?>">
          <i class="fas fa-lightbulb"></i> Recommendations
        </a>
      </li>
      <li>
        <a href="../booking/customer_appointments.php" class="<?= $current_page === 'customer_appointments.php' ? 'active' : '' ?>">
          <i class="fas fa-calendar-alt"></i> Appointments
        </a>
      </li>
      <li>
        <a href="../notifications/notification_cust.php" class="<?= $current_page === 'notification_cust.php' ? 'active' : '' ?>">
          <i class="fas fa-bell"></i> Notifications
        </a>
      </li>
      <li>
        <a href="/fyp_kusma/pages/login/login.php" class="logout-link">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </li>
    </ul>
  </div>
</nav>
