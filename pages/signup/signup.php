<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up</title>
  <link rel="stylesheet" href="../../assets/css/signup.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

  <div class="container">
    <div class="signup-left">
      <img src="../../assets/img/hero-img.png" alt="Background Image">
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
        <p class="login-link">Already have an account? <a href="../login/login.php">Log in</a></p>
        <p class="redirect-home"><a href="../../index.php">Go to Home</a></p>
      </form>
    </div>
  </div>

  <?php if (isset($_SESSION['error'])): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Email Already in Use',
      text: 'The email you entered is already registered. Please use a different email or log in instead.',
      confirmButtonColor: '#7B1FA2'
    });
  </script>
  <?php unset($_SESSION['error']); endif; ?>

  <script>
  document.querySelector('.signup-form').addEventListener('submit', function(e) {
      const name = document.getElementById('name').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      const terms = document.getElementById('terms').checked;

      const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

      if (!name || !email || !password || !confirmPassword) {
          e.preventDefault();
          Swal.fire({
              icon: 'warning',
              title: 'Missing Fields',
              text: 'Please fill in all required fields.',
              confirmButtonColor: '#7B1FA2'
          });
          return;
      }

      if (!emailPattern.test(email)) {
          e.preventDefault();
          Swal.fire({
              icon: 'error',
              title: 'Invalid Email',
              text: 'Please enter a valid email address.',
              confirmButtonColor: '#7B1FA2'
          });
          return;
      }

      if (password !== confirmPassword) {
          e.preventDefault();
          Swal.fire({
              icon: 'error',
              title: 'Password Mismatch',
              text: 'Password and Confirm Password do not match.',
              confirmButtonColor: '#7B1FA2'
          });
          return;
      }

      if (!terms) {
          e.preventDefault();
          Swal.fire({
              icon: 'warning',
              title: 'Terms Required',
              text: 'Please agree to the Terms and Conditions.',
              confirmButtonColor: '#7B1FA2'
          });
          return;
      }
  });
  </script>

</body>
</html>
