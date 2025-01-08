<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link rel="stylesheet" href="../../assets/css/signup.css">
</head>
<body>
 
  <div class="container">
    <div class="signup-left">
      <img src="../../assets/img/hero-img.png" alt="Background Image"> <!-- Replace with your image path -->
    </div>
    <div class="signup-right">
      <form action="process_signup.php" method="POST" class="signup-form" autocomplete="off">
        <h1>Create an <strong>Account</strong></h1>
        <p>Sign up to get started with KUSMA:</p>
        <div class="input-group">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" placeholder="Enter your name" required>
        </div>
        <div class="input-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        <div class="input-group">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
        </div>
        <div class="terms">
          <input type="checkbox" id="terms" name="terms" required>
          <label for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
        </div>
        <button type="submit" class="signup-btn">Sign Up</button>
        <!-- Corrected Log in link -->
        <p class="login-link">Already have an account? <a href="../login/login.php">Log in</a></p>
        <p class="redirect-home"><a href="../../index.php">Go to Home</a></p>
      </form>
    </div>
  </div>
</body>
</html>
