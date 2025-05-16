<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch agent data
$sql = "SELECT u.name, u.email, a.phone, a.ic_passport 
        FROM users u 
        INNER JOIN agents a ON u.id = a.user_id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$agent_data = $result->fetch_assoc() ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $ic_passport = $_POST['ic_passport'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        $conn->begin_transaction();

        // Update users table
        $stmt_users = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt_users->bind_param("ssi", $name, $email, $user_id);
        $stmt_users->execute();

        // Update agents table
        $stmt_agents = $conn->prepare("UPDATE agents SET phone = ?, ic_passport = ? WHERE user_id = ?");
        $stmt_agents->bind_param("ssi", $phone, $ic_passport, $user_id);
        $stmt_agents->execute();

        // Optional password update
        if (!empty($password)) {
            if ($password !== $confirm_password) {
                throw new Exception("Passwords do not match.");
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_password = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_password->bind_param("si", $hashed_password, $user_id);
            $stmt_password->execute();
        }

        $conn->commit();
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: agentprofile.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Agent Profile</title>
  <link rel="stylesheet" href="../../../assets/css/agent_sidebar.css" />
  <link rel="stylesheet" href="../../../assets/css/agent_editprofile.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
</head>
<body>
<div class="dashboard-container">
  <?php include '../agentsidebar.php'; ?>

  <main class="dashboard-content">
    <section class="dashboard-section">
      <h1><i class="fas fa-user-edit"></i> Edit Profile</h1>
      <p>Update your contact and login details below.</p>

      <?php if (isset($_SESSION['success'])): ?>
        <p class="success-message"><?= $_SESSION['success']; unset($_SESSION['success']); ?></p>
      <?php elseif (isset($error_message)): ?>
        <p class="error-message"><?= $error_message; ?></p>
      <?php endif; ?>

      <form method="POST" class="profile-form" onsubmit="return confirmEdit();">
        <div class="input-group">
          <label>Full Name</label>
          <input type="text" name="name" value="<?= htmlspecialchars($agent_data['name']) ?>" required />
          <i class="fas fa-user"></i>
        </div>
        <div class="input-group">
          <label>Email</label>
          <input type="email" name="email" value="<?= htmlspecialchars($agent_data['email']) ?>" required />
          <i class="fas fa-envelope"></i>
        </div>
        <div class="input-group">
          <label>Phone Number</label>
          <input type="text" name="phone" value="<?= htmlspecialchars($agent_data['phone']) ?>" required />
          <i class="fas fa-phone"></i>
        </div>
        <div class="input-group">
          <label>IC/Passport No</label>
          <input type="text" name="ic_passport" value="<?= htmlspecialchars($agent_data['ic_passport']) ?>" required />
          <i class="fas fa-id-card"></i>
        </div>
        <div class="input-group">
          <label>New Password</label>
          <input type="password" name="password" placeholder="Leave blank to keep current password" />
          <i class="fas fa-lock"></i>
        </div>
        <div class="input-group">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="Confirm new password" />
          <i class="fas fa-lock"></i>
        </div>
        <button type="submit" class="dashboard-btn">Save Changes</button>
      </form>
    </section>
  </main>
</div>

<script>
function confirmEdit() {
  return confirm("Are you sure you want to save these changes?");
}
</script>
</body>
</html>
