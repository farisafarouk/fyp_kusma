<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Dashboard</title>
  <link rel="stylesheet" href="../../assets/css/agentdashboard.css"> <!-- Dashboard-specific CSS -->
</head>
<body>


  <div class="container">
    <aside class="sidebar">
      <h2>KUSMA Agent</h2>
      <ul>
        <li><a href="#overview" class="active">Dashboard Overview</a></li>
        <li><a href="#registered-users">Registered Users</a></li>
        <li><a href="#profile-management">Profile Management</a></li>
        <li><a href="#commission-tracking">Commission Tracking</a></li>
        <li><a href="/index.php">Logout</a></li>
      </ul>
    </aside>

    <main class="content">
      <!-- Dashboard Overview Section -->
      <section id="overview" class="dashboard-section">
        <h1>Welcome, [Agent Name]</h1>
        <p>Track your referrals, commissions, and manage your account with ease.</p>
      </section>

      <!-- Registered Users Section -->
      <section id="registered-users" class="dashboard-section">
        <h2>Registered Users</h2>
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Referral Code</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>John Doe</td>
              <td>johndoe@example.com</td>
              <td>AGENT123</td>
              <td>Subscribed</td>
            </tr>
            <!-- Add rows dynamically -->
          </tbody>
        </table>
      </section>

      <!-- Profile Management Section -->
      <section id="profile-management" class="dashboard-section">
        <h2>Profile Management</h2>
        <form action="process_profile_update.php" method="POST">
          <div class="input-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="Agent Name">
          </div>
          <div class="input-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="agent@example.com">
          </div>
          <div class="input-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" value="1234567890">
          </div>
          <button type="submit" class="update-btn">Update Profile</button>
        </form>
      </section>

      <!-- Commission Tracking Section -->
      <section id="commission-tracking" class="dashboard-section">
        <h2>Commission Tracking</h2>
        <p>Total Earned Commissions: <strong>$500</strong></p>
        <table>
          <thead>
            <tr>
              <th>Referral</th>
              <th>Date</th>
              <th>Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>John Doe</td>
              <td>2025-01-05</td>
              <td>$50</td>
            </tr>
            <!-- Add rows dynamically -->
          </tbody>
        </table>
      </section>
    </main>
  </div>
</body>
</html>
