<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Registration</title>
  <link rel="stylesheet" href="../../assets/css/agentregister.css"> <!-- Reusing the same CSS -->
</head>
<body>
  <?php include 'includes/navbar.php'; ?>

  <div class="container">
    <div class="signup-left">
      <img src="../../assets/img/agent-img.png" alt="Agent Registration Image"> <!-- Replace with your agent image -->
    </div>
    <div class="signup-right">
      <form action="process_agent_register.php" method="POST" class="signup-form" autocomplete="off">
        <h1>Become an <strong>Agent</strong></h1>
        <p>Join KUSMA and start earning commissions:</p>
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
          <label for="business_name">Business Name</label>
          <input type="text" id="business_name" name="business_name" placeholder="Enter your business name" required>
        </div>
        <div class="input-group">
          <label for="business_address">Business Address</label>
          <textarea id="business_address" name="business_address" placeholder="Enter your business address" rows="3" required></textarea>
        </div>
        <div class="terms">
          <input type="checkbox" id="terms" name="terms" required>
          <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
        </div>
        <button type="submit" class="signup-btn">Register</button>
        <p class="login-link">Already an agent? <a href="../login/login.php">Log in</a></p>
        <p class="redirect-home"><a href="../../index.php">Go to Home</a></p>
      </form>
    </div>
  </div>
</body>
</html>
