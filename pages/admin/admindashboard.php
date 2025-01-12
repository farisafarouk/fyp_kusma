<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../../assets/css/admindashboard.css"> <!-- Admin-specific CSS -->
  <link rel="stylesheet" href="../../assets/css/adminsidebar.css"> <!-- Sidebar-specific CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Font Awesome for icons -->
</head>
<body>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include 'adminsidebar.php'; ?>

    <!-- Main Content -->
    <main class="dashboard-content">
      <!-- Customer Management Section -->
      <section id="customer-management" class="dashboard-section">
        <h1><i class="fas fa-users"></i> Customer Management</h1>
        <p>View, add, update, or delete customer accounts.</p>
        <button class="dashboard-btn" onclick="location.href='customer_management/admin_customermanagement.php';">Go to Customer Management</button>
      </section>

      <!-- Agent Management Section -->
      <section id="agent-management" class="dashboard-section">
        <h1><i class="fas fa-user-tie"></i> Agent Management</h1>
        <p>Approve, reject, or track agent performance.</p>
        <button class="dashboard-btn">Manage Agents</button>
      </section>

      <!-- Consultant Management Section -->
      <section id="consultant-management" class="dashboard-section">
        <h1><i class="fas fa-user-friends"></i> Consultant Management</h1>
        <p>View, add, update, or delete consultant profiles.</p>
        <button class="dashboard-btn">Manage Consultants</button>
      </section>

      <!-- Resource Management Section -->
      <section id="resource-management" class="dashboard-section">
        <h1><i class="fas fa-book"></i> Program and Resources</h1>
        <p>Manage loans, grants, and training programs.</p>
        <button class="dashboard-btn">Manage Resources</button>
      </section>

      <!-- Subscriptions Section -->
      <section id="subscriptions" class="dashboard-section">
        <h1><i class="fas fa-file-invoice"></i> Subscriptions</h1>
        <p>Track subscription plans and billing statuses.</p>
        <button class="dashboard-btn">Manage Subscriptions</button>
      </section>

      <!-- Referrals Section -->
      <section id="referrals" class="dashboard-section">
        <h1><i class="fas fa-handshake"></i> Referrals</h1>
        <p>Track agent referrals and commissions.</p>
        <button class="dashboard-btn">View Referrals</button>
      </section>

      <!-- Reports Section -->
      <section id="reports" class="dashboard-section">
        <h1><i class="fas fa-chart-line"></i> Reports</h1>
        <p>Generate detailed insights on system performance.</p>
        <button class="dashboard-btn">Generate Reports</button>
      </section>

      <!-- Notifications Section -->
      <section id="notifications" class="dashboard-section">
        <h1><i class="fas fa-bell"></i> Notifications</h1>
        <p>Send updates and alerts to all users.</p>
        <button class="dashboard-btn">Send Notifications</button>
      </section>
    </main>
  </div>
</body>
</html>
