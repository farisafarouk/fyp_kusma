<?php
session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $expertise = $_POST['expertise'] ?? '';
    $rate = floatval($_POST['rate_per_hour'] ?? 0);
    $password = $_POST['password'] ?? '';

    if ($action === 'add') {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmtUser = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'consultant')");
        $stmtUser->bind_param("sss", $name, $email, $hashed);
        if ($stmtUser->execute()) {
            $user_id = $stmtUser->insert_id;
            $stmtConsultant = $conn->prepare("INSERT INTO consultants (user_id, phone, expertise, rate_per_hour) VALUES (?, ?, ?, ?)");
            $stmtConsultant->bind_param("issd", $user_id, $phone, $expertise, $rate);
            $stmtConsultant->execute();
        }
        header("Location: consultant_management.php");
        exit();
    }

    if ($action === 'edit') {
        $cid = $_POST['consultant_id'];
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users u JOIN consultants c ON u.id = c.user_id SET u.name=?, u.email=?, c.phone=?, c.expertise=?, c.rate_per_hour=?, u.password=? WHERE c.id=?");
            $stmt->bind_param("ssssdsi", $name, $email, $phone, $expertise, $rate, $hashed, $cid);
        } else {
            $stmt = $conn->prepare("UPDATE users u JOIN consultants c ON u.id = c.user_id SET u.name=?, u.email=?, c.phone=?, c.expertise=?, c.rate_per_hour=? WHERE c.id=?");
            $stmt->bind_param("ssssdi", $name, $email, $phone, $expertise, $rate, $cid);
        }
        $stmt->execute();
        header("Location: consultant_management.php");
        exit();
    }

    if ($action === 'delete') {
        $cid = $_POST['consultant_id'];
        $stmt = $conn->prepare("DELETE u, c FROM users u JOIN consultants c ON u.id = c.user_id WHERE c.id = ?");
        $stmt->bind_param("i", $cid);
        $stmt->execute();
        header("Location: consultant_management.php");
        exit();
    }
}

$result = $conn->query("SELECT c.id AS cid, u.name, u.email, c.phone, c.expertise, c.rate_per_hour FROM consultants c JOIN users u ON c.user_id = u.id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Consultant Management</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_customermanagement.css">
  <link rel="stylesheet" href="../../../assets/css/admin_consultant.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="dashboard-container">
  <?php include '../adminsidebar.php'; ?>
  <main class="dashboard-content">
    <section class="dashboard-section">
      <h1><i class="fas fa-user-tie"></i> Consultant Management</h1>
      <p>Manage all consultants, edit their details, or remove them as needed.</p>
      <button class="action-btn add" onclick="openAddModal()">+ Add Consultant</button>

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
              <button class="action-btn edit" onclick='openEditModal(<?= json_encode($row) ?>)'>Edit</button>
              <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="consultant_id" value="<?= $row['cid'] ?>">
                <button class="action-btn delete" onclick="return confirm('Delete this consultant?')" type="submit">Delete</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>
  </main>
</div>

<!-- Modals -->
<div class="modal" id="addModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('addModal')">&times;</span>
    <h2>Add Consultant</h2>
    <form method="POST">
      <input type="hidden" name="action" value="add">
      <div class="input-group"><label>Name</label><input name="name" required></div>
      <div class="input-group"><label>Email</label><input type="email" name="email" required></div>
      <div class="input-group"><label>Phone</label><input name="phone" required></div>
      <div class="input-group"><label>Expertise</label><input name="expertise" required></div>
      <div class="input-group"><label>Rate/hour</label><input type="number" step="0.01" name="rate_per_hour" required></div>
      <div class="input-group"><label>Password</label><input type="password" name="password" required></div>
      <button class="action-btn save">Add Consultant</button>
    </form>
  </div>
</div>

<div class="modal" id="editModal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeModal('editModal')">&times;</span>
    <h2>Edit Consultant</h2>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="consultant_id" id="edit-id">
      <div class="input-group"><label>Name</label><input name="name" id="edit-name" required></div>
      <div class="input-group"><label>Email</label><input name="email" id="edit-email" required></div>
      <div class="input-group"><label>Phone</label><input name="phone" id="edit-phone" required></div>
      <div class="input-group"><label>Expertise</label><input name="expertise" id="edit-expertise" required></div>
      <div class="input-group"><label>Rate/hour</label><input name="rate_per_hour" type="number" step="0.01" id="edit-rate" required></div>
      <div class="input-group"><label>Password (leave blank to keep current)</label><input name="password" type="password"></div>
      <button class="action-btn save">Update Consultant</button>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('addModal').style.display = 'flex';
}
function openEditModal(data) {
  document.getElementById('editModal').style.display = 'flex';
  document.getElementById('edit-id').value = data.cid;
  document.getElementById('edit-name').value = data.name;
  document.getElementById('edit-email').value = data.email;
  document.getElementById('edit-phone').value = data.phone;
  document.getElementById('edit-expertise').value = data.expertise;
  document.getElementById('edit-rate').value = data.rate_per_hour;
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}
</script>
</body>
</html>
