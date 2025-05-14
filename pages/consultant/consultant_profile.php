<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
  header("Location: ../login/login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$user_stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

// Fetch consultant info
$consult_stmt = $conn->prepare("SELECT phone, expertise, rate_per_hour FROM consultants WHERE user_id = ?");
$consult_stmt->bind_param("i", $user_id);
$consult_stmt->execute();
$consultant = $consult_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Consultant Profile</title>
  <link rel="stylesheet" href="../../assets/css/consultantsidebar.css">
  <link rel="stylesheet" href="../../assets/css/consultant_profile.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

</head>
<body>
<div class="dashboard-container">
  <?php include 'consultantsidebar.php'; ?>
  <div class="dashboard-content">
    <section class="dashboard-section">
      <header>
        <h1><i class="fas fa-user"></i> Manage Profile</h1>
        <p class="muted">Update your personal and professional information below.</p>
      </header>

      <form id="profileForm">
        <div class="form-group">
          <label for="name">Name</label>
          <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required readonly>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required readonly>
        </div>

        <div class="form-group">
          <label for="phone">Phone</label>
          <input type="text" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($consultant['phone']) ?>" required readonly>
        </div>

        <div class="form-group">
          <label for="expertise">Area of Expertise</label>
          <input type="text" id="expertise" name="expertise" class="form-control" value="<?= htmlspecialchars($consultant['expertise']) ?>" required readonly>
        </div>

        <div class="form-group">
          <label for="rate">Consultation Rate (RM)</label>
<input type="number" id="rate_per_hour" name="rate_per_hour" class="form-control" min="0" step="0.01" value="<?= htmlspecialchars($consultant['rate_per_hour']) ?>" readonly>
        </div>

        <div class="form-actions">
          <button type="button" id="editBtn" class="btn-edit"><i class="fas fa-pen"></i> Edit</button>
          <button type="submit" id="saveBtn" class="btn-save" style="display:none;"><i class="fas fa-save"></i> Save Changes</button>
        </div>
        <div id="statusMessage"></div>
      </form>
    </section>
  </div>
</div>

<script>
document.getElementById('editBtn').addEventListener('click', () => {
  document.querySelectorAll('.form-control').forEach(input => input.removeAttribute('readonly'));
  document.getElementById('saveBtn').style.display = 'inline-flex';
});

document.getElementById('profileForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  fetch('update_profile.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const msg = document.getElementById('statusMessage');
    msg.textContent = data.message;
    msg.style.color = data.success ? 'green' : 'red';
    if (data.success) {
      document.querySelectorAll('.form-control').forEach(input => input.setAttribute('readonly', true));
      document.getElementById('saveBtn').style.display = 'none';
    }
  });
});
</script>
</body>
</html>