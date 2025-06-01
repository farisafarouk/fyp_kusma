<!DOCTYPE html>
<html lang="en">
<head>
       <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log In</title>
  <link rel="stylesheet" href="../../assets/css/login.css">
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
        <button type="submit" class="login-btn" href>Log In</button>
        <p class="signup-link">Don't have an account? <a href="../signup/signup.php">Sign up</a></p>
        <p class="redirect-home"><a href="../../index.php">Go to Home</a></p>
      </form>
    </div>
    <div class="login-right">
      <img src="../../assets/img/login.png" alt="Login Background Image"> <!-- Replace with your image path -->
    </div>
  </div>
</body>
</html>
