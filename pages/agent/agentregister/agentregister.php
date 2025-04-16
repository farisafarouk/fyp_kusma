<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../config/database.php';

function generateReferralCode($length = 10) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

function getUniqueReferralCode($conn) {
    do {
        $referral_code = generateReferralCode();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM agents WHERE referral_code = ?");
        $stmt->bind_param("s", $referral_code);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    } while ($count > 0);
    return $referral_code;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $ic_passport = $_POST['ic_passport'];
    $password = $_POST['password'];

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        $conn->begin_transaction();

        // Insert into users table
        $sql_users = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'agent')";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("sss", $name, $email, $hashed_password);
        $stmt_users->execute();

        $user_id = $stmt_users->insert_id;

        // Generate unique referral code
        $referral_code = getUniqueReferralCode($conn);

        // Insert into agents table
        $sql_agents = "INSERT INTO agents (user_id, phone, ic_passport, approval_status, referral_code) VALUES (?, ?, ?, 'pending', ?)";
        $stmt_agents = $conn->prepare($sql_agents);
        $stmt_agents->bind_param("isss", $user_id, $phone, $ic_passport, $referral_code);
        $stmt_agents->execute();

        $conn->commit();

        $success_message = "Your agent registration request has been submitted. Please wait for admin approval.";
    } catch (Exception $e) {
        $conn->rollback();
        error_log($e->getMessage());
        $error_message = "An error occurred during registration. Please try again later.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Registration</title>
  <link rel="stylesheet" href="../../../assets/css/agentregister.css">
</head>
<body>
  <div class="container">
    <div class="signup-left">
      <img src="../../../assets/img/agent-img.png" alt="Agent Registration Image">
    </div>
    <div class="signup-right">
      <form method="POST" class="signup-form" autocomplete="off">
        <h1>Become an <strong>Agent</strong></h1>
        <p>Join KUSMA and start earning commissions:</p>

        <!-- Display success or error message -->
        <?php if (isset($success_message)): ?>
          <p class="success-message" style="color: green;"><?php echo $success_message; ?></p>
        <?php elseif (isset($error_message)): ?>
          <p class="error-message" style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <div class="input-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" name="name" placeholder="Enter your full name" required>
        </div>
        <div class="input-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="input-group">
          <label for="phone">Phone Number</label>
          <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required>
        </div>
        <div class="input-group">
          <label for="ic_passport">IC Number/Passport Number</label>
          <input type="text" id="ic_passport" name="ic_passport" placeholder="Enter your IC or Passport Number" required>
        </div>
        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter a secure password" required>
        </div>
        <div class="terms">
          <input type="checkbox" id="terms" name="terms" required>
          <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
        </div>
        <button type="submit" class="signup-btn">Register</button>
        
        <p class="redirect-home"><a href="../../../index.php">Go to Home</a></p>
      </form>
    </div>
  </div>
</body>
</html>