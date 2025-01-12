<?php
session_start();
require_once '../../../config/database.php';

// Fetch the current agent's data
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'agent') {
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT u.name, u.email, a.phone, a.ic_passport 
            FROM users u 
            INNER JOIN agents a ON u.id = a.user_id 
            WHERE u.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $agent_data = $result->fetch_assoc();
    } else {
        die("Agent not found.");
    }
} else {
    header("Location: ../../login/login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $ic_passport = $_POST['ic_passport'];

    try {
        $conn->begin_transaction();

        // Update user table
        $sql_users = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("ssi", $name, $email, $user_id);
        $stmt_users->execute();

        // Update agents table
        $sql_agents = "UPDATE agents SET phone = ?, ic_passport = ? WHERE user_id = ?";
        $stmt_agents = $conn->prepare($sql_agents);
        $stmt_agents->bind_param("ssi", $phone, $ic_passport, $user_id);
        $stmt_agents->execute();

        $conn->commit();
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: agent_profile.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "An error occurred. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Profile Management</title>
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
      <section class="dashboard-section">
        <h1><i class="fas fa-user"></i> Profile Management</h1>
        <p>Update your personal and business information here.</p>

        <!-- Success/Error Message -->
        <?php if (isset($_SESSION['success'])): ?>
          <p class="success-message" style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php elseif (isset($error_message)): ?>
          <p class="error-message" style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <!-- Profile Form -->
        <form method="POST" class="profile-form">
          <div class="input-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($agent_data['name']); ?>" required>
          </div>
          <div class="input-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($agent_data['email']); ?>" required>
          </div>
          <div class="input-group">
            <label for="phone">Phone Number</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($agent_data['phone']); ?>" required>
          </div>
          <div class="input-group">
            <label for="ic_passport">IC Number/Passport Number</label>
            <input type="text" id="ic_passport" name="ic_passport" value="<?php echo htmlspecialchars($agent_data['ic_passport']); ?>" required>
          </div>
          <button type="submit" class="dashboard-btn">Save Changes</button>
        </form>
      </section>
    </main>
  </div>
</body>
</html>
