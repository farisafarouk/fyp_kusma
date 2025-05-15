<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<nav class="navbar">
  <div class="navbar-container">
    <ul class="navbar-menu">
      <li>
        <a href="../profile/customer_dashboard.php" class="<?= $current_page === 'customer_dashboard.php' ? 'active' : '' ?>">
          <i class="fas fa-home"></i><span> Dashboard</span>
        </a>
      </li>
      <li>
        <a href="../booking/consultant_list.php" class="<?= $current_page === 'consultant_list.php' ? 'active' : '' ?>">
          <i class="fas fa-user-md"></i><span> Book</span>
        </a>
      </li>
      <li>
        <a href="../booking/customer_appointments.php" class="<?= $current_page === 'customer_appointments.php' ? 'active' : '' ?>">
          <i class="fas fa-calendar-check"></i><span> Appointments</span>
        </a>
      </li>
      <li>
        <a href="../payment/manage_subscription.php" class="<?= $current_page === 'manage_subscription.php' ? 'active' : '' ?>">
          <i class="fas fa-wallet"></i><span> Subscription</span>
        </a>
      </li>
      <li>
        <a href="../recommendations.php" class="<?= $current_page === 'recommendations.php' ? 'active' : '' ?>">
          <i class="fas fa-lightbulb"></i><span> Recommendations</span>
        </a>
      </li>
      <li>
        <a href="../profile/manage_profile.php" class="<?= $current_page === 'manage_profile.php' ? 'active' : '' ?>">
          <i class="fas fa-user-circle"></i><span> Profile</span>
        </a>
      </li>
      <li>
        <a href="../notifications/notification_cust.php" class="<?= $current_page === 'notification_cust.php' ? 'active' : '' ?>">
          <i class="fas fa-bell"></i><span> Notifications</span>
        </a>
      </li>
      <li>
        <a href="../login/login.php" class="logout-link">
          <i class="fas fa-sign-out-alt"></i><span> Logout</span>
        </a>
      </li>
    </ul>
  </div>
</nav>
