<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $ic_passport = $_POST['ic_passport'] ?? '';
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

        // Insert into users table
        $sql_user = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'agent')";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param('sss', $name, $email, $password);

        if ($stmt_user->execute()) {
            $user_id = $stmt_user->insert_id;

            // Insert into agents table
            $sql_agent = "INSERT INTO agents (user_id, phone, ic_passport, approval_status) VALUES (?, ?, ?, 'pending')";
            $stmt_agent = $conn->prepare($sql_agent);
            $stmt_agent->bind_param('iss', $user_id, $phone, $ic_passport);

            if ($stmt_agent->execute()) {
                header("Location: agent_management.php");
                exit();
            } else {
                die("Error adding agent: " . $stmt_agent->error);
            }
        } else {
            die("Error adding user: " . $stmt_user->error);
        }
    } elseif ($action === 'approve' || $action === 'decline') {
        $agent_id = $_POST['agent_id'] ?? '';
        $approval_status = $action === 'approve' ? 'approved' : 'declined';

        // Update approval status
        $sql = "UPDATE agents SET approval_status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $approval_status, $agent_id);

        if ($stmt->execute()) {
            header("Location: agent_management.php");
            exit();
        } else {
            die("Error updating agent approval status: " . $stmt->error);
        }
    } elseif ($action === 'delete') {
        $agent_id = $_POST['agent_id'] ?? '';

        // Delete agent from agents and users tables
        $sql_agent = "DELETE FROM agents WHERE id = ?";
        $stmt_agent = $conn->prepare($sql_agent);
        $stmt_agent->bind_param('i', $agent_id);

        if ($stmt_agent->execute()) {
            $sql_user = "DELETE FROM users WHERE id = (SELECT user_id FROM agents WHERE id = ?)";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param('i', $agent_id);

            if ($stmt_user->execute()) {
                header("Location: agent_management.php");
                exit();
            } else {
                die("Error deleting user: " . $stmt_user->error);
            }
        } else {
            die("Error deleting agent: " . $stmt_agent->error);
        }
    }
}

// Fetch pending agents
$sql_pending = "SELECT a.id, u.name, u.email, a.phone, a.ic_passport 
                FROM agents a 
                INNER JOIN users u ON a.user_id = u.id 
                WHERE a.approval_status = 'pending'";
$result_pending = $conn->query($sql_pending);

// Fetch approved agents
$sql_approved = "SELECT a.id AS agent_id, u.id AS user_id, u.name, u.email, a.phone, a.ic_passport 
                 FROM agents a 
                 INNER JOIN users u ON a.user_id = u.id 
                 WHERE a.approval_status = 'approved'";
$result_approved = $conn->query($sql_approved);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Management</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_agentmanagement.css">
</head>
<body>
  <div class="dashboard-container">
    <?php include '../adminsidebar.php'; ?>

    <main class="dashboard-content">
      <!-- Pending Agents Section -->
      <section class="dashboard-section">
        <h1><i class="fas fa-user-clock"></i> Pending Agent Registrations</h1>
        <p>Approve or decline agent registration requests.</p>
        <table class="contact-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>IC/Passport</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result_pending->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['ic_passport']); ?></td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="agent_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="action" value="approve" class="action-btn save">Approve</button>
                    <button type="submit" name="action" value="decline" class="action-btn delete">Decline</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>

      <!-- Approved Agents Section -->
      <section class="dashboard-section">
        <h1><i class="fas fa-user-check"></i> Approved Agents</h1>
        <p>Edit, delete, or add new agents.</p>
        <button class="action-btn add" onclick="openAddAgentModal()">+ Add Agent</button>

        <table class="contact-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>IC/Passport</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result_approved->fetch_assoc()): ?>
              <tr id="row-<?php echo $row['agent_id']; ?>">
                <td><?php echo htmlspecialchars($row['agent_id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['ic_passport']); ?></td>
                <td>
                  <button class="action-btn edit" onclick="enableEdit(<?php echo $row['agent_id']; ?>)">Edit</button>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="agent_id" value="<?php echo $row['agent_id']; ?>">
                    <button type="submit" name="action" value="delete" class="action-btn delete">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>
    </main>

    <!-- Add Agent Modal -->
    <div id="addAgentModal" class="modal">
      <div class="modal-content">
        <span class="close-btn" onclick="closeAddAgentModal()">&times;</span>
        <h2>Add New Agent</h2>
        <form method="POST" action="agent_management.php">
          <input type="hidden" name="action" value="add">
          <div class="input-group">
            <label for="add-name">Name</label>
            <input type="text" id="add-name" name="name" required>
          </div>
          <div class="input-group">
            <label for="add-email">Email</label>
            <input type="email" id="add-email" name="email" required>
          </div>
          <div class="input-group">
            <label for="add-phone">Phone</label>
            <input type="text" id="add-phone" name="phone" required>
          </div>
          <div class="input-group">
            <label for="add-ic-passport">IC/Passport</label>
            <input type="text" id="add-ic-passport" name="ic_passport" required>
          </div>
          <div class="input-group">
            <label for="add-password">Password</label>
            <input type="password" id="add-password" name="password" required>
          </div>
          <button type="submit" class="action-btn save">Add Agent</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openAddAgentModal() {
      document.getElementById('addAgentModal').style.display = 'flex';
    }

    function closeAddAgentModal() {
      document.getElementById('addAgentModal').style.display = 'none';
    }
  </script>
</body>
</html>

