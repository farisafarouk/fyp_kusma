<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In</title>
  <link rel="stylesheet" href="../../assets/css/login.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
 

  <div class="container">
    <div class="login-left">
      <form action="process_login.php" method="POST" class="login-form" autocomplete="off">
        <h1>Welcome Back</h1>
        <p>Log in to continue to KUSMA:</p>
        <div class="input-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>
        <div class="input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
<button type="submit" class="login-btn">Log In</button>
        <p class="signup-link">Don't have an account? <a href="../signup/signup.php">Sign up</a></p>
        <p class="redirect-home"><a href="../../index.php">Go to Home</a></p>
      </form>
    </div>
    <div class="login-right">
      <img src="../../assets/img/login.png" alt="Login Background Image"> <!-- Replace with your image path -->
    </div>
  </div>
<?php if (isset($_SESSION['error'])): ?>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: '<?= $_SESSION['error'] ?>',
        confirmButtonColor: '#7B1FA2'
    });
</script>
<?php unset($_SESSION['error']); endif; ?>


<script>
document.querySelector('.login-form').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value.trim();

    if (!email || !password) {
Swal.fire({
  icon: 'warning',
  title: 'Missing Fields',
  text: 'Please fill in both email and password.',
  confirmButtonColor: '#7B1FA2'
});
        e.preventDefault();
        return;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
Swal.fire({
  icon: 'warning',
  title: 'Invalid Email',
  text: 'Please enter a valid email address.',
  confirmButtonColor: '#7B1FA2'
});
        e.preventDefault();
    }
});
</script>

</body>
</html>
