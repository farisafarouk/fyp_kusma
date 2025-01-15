<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Consultant Dashboard</title>
  <link rel="stylesheet" href="../../assets/css/consultantsidebar.css"> <!-- Sidebar-specific CSS -->
  <link rel="stylesheet" href="../../assets/css/consultantdashboard.css"> <!-- Dashboard-specific CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Font Awesome for icons -->
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include 'consultantsidebar.php'; ?>

    <!-- Main Content -->
    <main class="dashboard-content">
      <!-- Profile Management -->
      <section class="dashboard-section">
        <h1><i class="fas fa-user"></i> Profile Management</h1>
        <p>Manage your personal and professional details, including qualifications and consultation rates.</p>
        <button class="dashboard-btn" onclick="location.href='profile_management.php';">Manage Profile</button>
      </section>

      <!-- Manage Appointments -->
      <section class="dashboard-section">
        <h1><i class="fas fa-calendar-check"></i> Manage Appointments</h1>
        <p>View, approve, reject, or reschedule appointments based on availability.</p>
        <button class="dashboard-btn" onclick="location.href='appointments.php';">Manage Appointments</button>
      </section>

      <!-- View Feedback -->
      <section class="dashboard-section">
        <h1><i class="fas fa-comments"></i> View Feedback</h1>
        <p>View and respond to customer feedback to improve your service delivery.</p>
        <button class="dashboard-btn" onclick="location.href='feedback.php';">View Feedback</button>
      </section>

      <!-- Availability Scheduling -->
      <section class="dashboard-section">
        <h1><i class="fas fa-clock"></i> Availability Scheduling</h1>
        <p>Set and update your availability for consultations in real-time.</p>
        <button class="dashboard-btn" onclick="location.href='availability.php';">Schedule Availability</button>
      </section>

      <!-- Reports and Analytics -->
      <section class="dashboard-section">
        <h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
        <p>Access performance reports and analytics to enhance service delivery.</p>
        <button class="dashboard-btn" onclick="location.href='reports.php';">View Reports</button>
      </section>

      <!-- Notifications -->
      <section class="dashboard-section">
        <h1><i class="fas fa-bell"></i> Notifications</h1>
        <p>Stay updated with real-time alerts about appointments, feedback, and platform updates.</p>
        <button class="dashboard-btn" onclick="location.href='notifications.php';">View Notifications</button>
      </section>
    </main>
  </div>
</body>
</html>
