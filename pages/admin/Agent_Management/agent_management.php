<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

// Fetch pending agent registrations
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

// Handle form submissions for approval, decline, edit, or delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        // Approve or decline agent
        if ($action === 'approve' || $action === 'decline') {
            $agent_id = $_POST['agent_id'];
            $status = ($action === 'approve') ? 'approved' : 'declined';
            $sql_update = "UPDATE agents SET approval_status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql_update);
            $stmt->bind_param("si", $status, $agent_id);
            $stmt->execute();
        }

        // Edit agent details
        if ($action === 'edit') {
            $agent_id = $_POST['agent_id'];
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $ic_passport = $_POST['ic_passport'];

            $sql_update_user = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $stmt_user = $conn->prepare($sql_update_user);
            $stmt_user->bind_param("ssi", $name, $email, $_POST['user_id']);
            $stmt_user->execute();

            $sql_update_agent = "UPDATE agents SET phone = ?, ic_passport = ? WHERE id = ?";
            $stmt_agent = $conn->prepare($sql_update_agent);
            $stmt_agent->bind_param("ssi", $phone, $ic_passport, $agent_id);
            $stmt_agent->execute();
        }

        // Delete agent
        if ($action === 'delete') {
            $user_id = $_POST['user_id'];
            $sql_delete_user = "DELETE FROM users WHERE id = ?";
            $stmt_delete = $conn->prepare($sql_delete_user);
            $stmt_delete->bind_param("i", $user_id);
            $stmt_delete->execute();
        }

        // Redirect back to the page to refresh data
        header("Location: agent_management.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agent Management</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../../assets/css/admin_agentmanagement.css"> <!-- Admin CSS -->
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css"> <!-- Admin CSS -->
</head>
<body>
  <div class="dashboard-container">
    <?php include '../adminsidebar.php'; ?>

    <main class="dashboard-content">
      <!-- Pending Agents Section -->
      <section class="dashboard-section">
        <h1>Pending Agent Registrations</h1>
        <p>Approve or decline agent registration requests.</p>

        <table class="agent-table">
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
                  <form method="POST">
                    <input type="hidden" name="agent_id" value="<?php echo $row['id']; ?>">
                    <button type="submit" name="action" value="approve" class="btn-approve">Approve</button>
                    <button type="submit" name="action" value="decline" class="btn-decline">Decline</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>

      <!-- Approved Agents Section -->
      <section class="dashboard-section">
        <h1>Approved Agents</h1>
        <p>Edit or delete approved agents.</p>

        <table class="agent-table">
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
            <?php while ($row = $result_approved->fetch_assoc()): ?>
              <tr>
                <form method="POST">
                  <td>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                  </td>
                  <td>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                  </td>
                  <td>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
                  </td>
                  <td>
                    <input type="text" name="ic_passport" value="<?php echo htmlspecialchars($row['ic_passport']); ?>" required>
                  </td>
                  <td>
                    <input type="hidden" name="agent_id" value="<?php echo $row['agent_id']; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                    <button type="submit" name="action" value="edit" class="btn-edit">Save</button>
                    <button type="submit" name="action" value="delete" class="btn-delete">Delete</button>
                  </td>
                </form>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>
</body>
</html>
