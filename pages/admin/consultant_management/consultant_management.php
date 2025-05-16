<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $expertise = trim($_POST['expertise'] ?? '');
    $rate = floatval($_POST['rate_per_hour'] ?? 0);
    $password = $_POST['password'] ?? '';

    if ($action === 'add') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $insertUser = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'consultant')");
        $insertUser->bind_param("sss", $name, $email, $hashed);
        if ($insertUser->execute()) {
            $uid = $insertUser->insert_id;
            $insertConsultant = $conn->prepare("INSERT INTO consultants (user_id, phone, expertise, rate_per_hour) VALUES (?, ?, ?, ?)");
            $insertConsultant->bind_param("issd", $uid, $phone, $expertise, $rate);
            $_SESSION['toast_message'] = $insertConsultant->execute() ? 'Consultant added.' : 'Consultant insert error.';
            $_SESSION['toast_type'] = $insertConsultant->execute() ? 'success' : 'error';
        } else {
            $_SESSION['toast_message'] = 'User insert error.';
            $_SESSION['toast_type'] = 'error';
        }
        header("Location: consultant_management.php");
        exit();
    }

    if ($action === 'edit') {
        $cid = $_POST['consultant_id'];
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users u JOIN consultants c ON u.id = c.user_id 
                SET u.name=?, u.email=?, c.phone=?, c.expertise=?, c.rate_per_hour=?, u.password=? 
                WHERE c.id=?");
            $update->bind_param("ssssdsi", $name, $email, $phone, $expertise, $rate, $hashed, $cid);
        } else {
            $update = $conn->prepare("UPDATE users u JOIN consultants c ON u.id = c.user_id 
                SET u.name=?, u.email=?, c.phone=?, c.expertise=?, c.rate_per_hour=? 
                WHERE c.id=?");
            $update->bind_param("ssssdi", $name, $email, $phone, $expertise, $rate, $cid);
        }
        $_SESSION['toast_message'] = $update->execute() ? 'Consultant updated.' : 'Update error.';
        $_SESSION['toast_type'] = $update->execute() ? 'success' : 'error';
        header("Location: consultant_management.php");
        exit();
    }

    if ($action === 'delete') {
        $cid = $_POST['consultant_id'];
        $del = $conn->prepare("DELETE u, c FROM users u JOIN consultants c ON u.id = c.user_id WHERE c.id = ?");
        $del->bind_param("i", $cid);
        $_SESSION['toast_message'] = $del->execute() ? 'Consultant deleted.' : 'Delete failed.';
        $_SESSION['toast_type'] = $del->execute() ? 'success' : 'error';
        header("Location: consultant_management.php");
        exit();
    }
}

$result = $conn->query("SELECT c.id AS cid, u.name, u.email, c.phone, c.expertise, c.rate_per_hour 
                        FROM consultants c JOIN users u ON c.user_id = u.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Consultants</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_consultant.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="dashboard-container">
  <?php include '../adminsidebar.php'; ?>
  <main class="dashboard-content">
    <section class="dashboard-section">
      <h1>Manage Consultants</h1>
      <button class="action-btn add" onclick="openModal('addModal')">+ Add Consultant</button>
      <table class="contact-table">
        <thead>
          <tr>
            <th>Name</th><th>Email</th><th>Phone</th><th>Expertise</th><th>Rate</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['phone']) ?></td>
              <td><?= htmlspecialchars($row['expertise']) ?></td>
              <td>RM <?= number_format($row['rate_per_hour'], 2) ?></td>
              <td class="action-btn-group">
                <button class="action-btn edit" onclick='populateEdit(<?= json_encode($row) ?>)'>Edit</button>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="consultant_id" value="<?= $row['cid'] ?>">
                  <button class="action-btn delete" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>
  </main>
</div>

<!-- Add Modal -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <h3>Add Consultant</h3>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="input-group"><label>Name</label><input name="name" required></div>
      <div class="input-group"><label>Email</label><input type="email" name="email" required></div>
      <div class="input-group"><label>Phone</label><input name="phone" required></div>
      <div class="input-group"><label>Expertise</label><input name="expertise" required></div>
      <div class="input-group"><label>Rate per Hour (RM)</label><input type="number" name="rate_per_hour" step="0.01" required></div>
      <div class="input-group"><label>Password</label><input type="password" name="password" required></div>
      <button class="action-btn save" type="submit">Add</button>
      <button type="button" class="action-btn cancel" onclick="closeModal('addModal')">Cancel</button>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <h3>Edit Consultant</h3>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="consultant_id" id="edit-id">
      <div class="input-group"><label>Name</label><input name="name" id="edit-name" required></div>
      <div class="input-group"><label>Email</label><input type="email" name="email" id="edit-email" required></div>
      <div class="input-group"><label>Phone</label><input name="phone" id="edit-phone" required></div>
      <div class="input-group"><label>Expertise</label><input name="expertise" id="edit-expertise" required></div>
      <div class="input-group"><label>Rate per Hour (RM)</label><input type="number" step="0.01" name="rate_per_hour" id="edit-rate" required></div>
      <div class="input-group"><label>Password (leave blank to keep current)</label><input type="password" name="password" id="edit-password"></div>
      <button class="action-btn save" type="submit">Update</button>
      <button type="button" class="action-btn cancel" onclick="closeModal('editModal')">Cancel</button>
    </form>
  </div>
</div>

<?php if (!empty($_SESSION['toast_message'])): ?>
  <div class="toast <?= $_SESSION['toast_type'] ?>"><?= htmlspecialchars($_SESSION['toast_message']) ?></div>
  <script>setTimeout(() => document.querySelector('.toast')?.remove(), 3000);</script>
  <?php unset($_SESSION['toast_message'], $_SESSION['toast_type']); ?>
<?php endif; ?>

<script>
  function openModal(id) {
    document.getElementById(id).style.display = 'flex';
  }

  function closeModal(id) {
    document.getElementById(id).style.display = 'none';
  }

  function populateEdit(data) {
    document.getElementById('edit-id').value = data.cid;
    document.getElementById('edit-name').value = data.name;
    document.getElementById('edit-email').value = data.email;
    document.getElementById('edit-phone').value = data.phone;
    document.getElementById('edit-expertise').value = data.expertise;
    document.getElementById('edit-rate').value = data.rate_per_hour;
    document.getElementById('edit-password').value = '';
    openModal('editModal');
  }
</script>
</body>
</html>
