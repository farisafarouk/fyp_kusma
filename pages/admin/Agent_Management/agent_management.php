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
  
      // Function to generate a referral code
      function generateReferralCode($length = 10) {
          $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
          $code = '';
          for ($i = 0; $i < $length; $i++) {
              $code .= $characters[rand(0, strlen($characters) - 1)];
          }
          return $code;
      }
  
      // Make sure referral code is unique
      function getUniqueReferralCode($conn) {
          do {
              $code = generateReferralCode();
              $stmt = $conn->prepare("SELECT COUNT(*) FROM agents WHERE referral_code = ?");
              $stmt->bind_param("s", $code);
              $stmt->execute();
              $stmt->bind_result($count);
              $stmt->fetch();
              $stmt->close();
          } while ($count > 0);
          return $code;
      }
  
      // Create referral code
      $referral_code = getUniqueReferralCode($conn);
  
      // Insert into users table
      $sql_user = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'agent')";
      $stmt_user = $conn->prepare($sql_user);
      $stmt_user->bind_param('sss', $name, $email, $password);
  
      if ($stmt_user->execute()) {
          $user_id = $stmt_user->insert_id;
  
          // Insert into agents table with referral code
          $sql_agent = "INSERT INTO agents (user_id, phone, ic_passport, approval_status, referral_code) VALUES (?, ?, ?, 'approved', ?)";
          $stmt_agent = $conn->prepare($sql_agent);
          $stmt_agent->bind_param('isss', $user_id, $phone, $ic_passport, $referral_code);
  
          if ($stmt_agent->execute()) {
              header("Location: agent_management.php");
              exit();
          } else {
              die("Error adding agent: " . $stmt_agent->error);
          }
      } else {
          die("Error adding user: " . $stmt_user->error);
      }
  }
   elseif ($action === 'edit') {
        $agent_id = $_POST['agent_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $ic_passport = $_POST['ic_passport'] ?? '';

        if (!empty($agent_id)) {
            $sql = "UPDATE users 
                    INNER JOIN agents ON users.id = agents.user_id 
                    SET users.name = ?, users.email = ?, agents.phone = ?, agents.ic_passport = ? 
                    WHERE agents.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssi', $name, $email, $phone, $ic_passport, $agent_id);

            if ($stmt->execute()) {
                header("Location: agent_management.php");
                exit();
            } else {
                die("Error updating agent: " . $stmt->error);
            }
        }
    } elseif ($action === 'delete') {
        $agent_id = $_POST['agent_id'] ?? '';

        // Delete from agents and users tables
        $sql_agent = "DELETE a, u FROM agents a
                      INNER JOIN users u ON a.user_id = u.id
                      WHERE a.id = ?";
        $stmt_agent = $conn->prepare($sql_agent);
        $stmt_agent->bind_param('i', $agent_id);

        if ($stmt_agent->execute()) {
            header("Location: agent_management.php");
            exit();
        } else {
            die("Error deleting agent: " . $stmt_agent->error);
        }
    } elseif ($action === 'approve') {
        $agent_id = $_POST['agent_id'] ?? '';

        if (!empty($agent_id)) {
            $sql = "UPDATE agents SET approval_status = 'approved' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $agent_id);

            if ($stmt->execute()) {
                header("Location: agent_management.php");
                exit();
            } else {
                die("Error approving agent: " . $stmt->error);
            }
        }
    } elseif ($action === 'decline') {
        $agent_id = $_POST['agent_id'] ?? '';

        if (!empty($agent_id)) {
            $sql = "UPDATE agents SET approval_status = 'declined' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $agent_id);

            if ($stmt->execute()) {
                header("Location: agent_management.php");
                exit();
            } else {
                die("Error declining agent: " . $stmt->error);
            }
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
  <link rel="stylesheet" href="../../../assets/css/admin_customermanagement.css">
</head>
<body>
  <div class="dashboard-container">
    <?php include '../adminsidebar.php'; ?>

    <main class="dashboard-content">
      <!-- Pending Agents Section -->
      <section class="dashboard-section">
        <h1><i class="fas fa-user-clock"></i> Pending Agent Registrations</h1>
        <p>Approve or decline agent registration requests.</p>
        <div class="filter-container">
          <input type="text" id="filter-pending-agents" placeholder="Search Pending Agents" onkeyup="filterPendingAgents()">
        </div>
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
         <!-- Pending Agents Table -->
<tbody id="pending-agents-table">
  <?php while ($row = $result_pending->fetch_assoc()): ?>
    <tr>
      <td><?php echo htmlspecialchars($row['name']); ?></td>
      <td><?php echo htmlspecialchars($row['email']); ?></td>
      <td><?php echo htmlspecialchars($row['phone']); ?></td>
      <td><?php echo htmlspecialchars($row['ic_passport']); ?></td>
      <td>
        <form method="POST" action="agent_management.php" style="display:inline;">
          <input type="hidden" name="agent_id" value="<?php echo $row['id']; ?>">
          <button type="submit" name="action" value="approve" class="action-btn save"
            onclick="return confirm('Are you sure you want to approve this agent?');">Approve</button>
          <button type="submit" name="action" value="decline" class="action-btn delete"
            onclick="return confirm('Are you sure you want to decline this agent?');">Decline</button>
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
       
        <div class="filter-container">
          <input type="text" id="filter-approved-agents" placeholder="Search Approved Agents" onkeyup="filterApprovedAgents()">
        </div>
        <button class="action-btn add" onclick="openAddAgentModal()">+ Add Agent</button>
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
        <!-- Approved Agents Table -->
<tbody id="approved-agents-table">
  <?php while ($row = $result_approved->fetch_assoc()): ?>
    <tr>
      <td><?php echo htmlspecialchars($row['name']); ?></td>
      <td><?php echo htmlspecialchars($row['email']); ?></td>
      <td><?php echo htmlspecialchars($row['phone']); ?></td>
      <td><?php echo htmlspecialchars($row['ic_passport']); ?></td>
      <td>
        <button class="action-btn edit" onclick="openEditAgentModal(
            '<?php echo htmlspecialchars($row['agent_id']); ?>',
            '<?php echo htmlspecialchars($row['name']); ?>',
            '<?php echo htmlspecialchars($row['email']); ?>',
            '<?php echo htmlspecialchars($row['phone']); ?>',
            '<?php echo htmlspecialchars($row['ic_passport']); ?>'
        )"> Edit</button>
        
        <form method="POST" action="agent_management.php" style="display:inline;">
          <input type="hidden" name="agent_id" value="<?php echo $row['agent_id']; ?>">
          <button type="submit" name="action" value="delete" class="action-btn delete"
            onclick="return confirm('Are you sure you want to delete this agent? This action cannot be undone.');">
            Delete
          </button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
</tbody>

        </table>
      </section>
    </main>
  </div>

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
          <label for="add-ic">IC/Passport</label>
          <input type="text" id="add-ic" name="ic_passport" required>
        </div>
        <div class="input-group">
          <label for="add-password">Password</label>
          <input type="password" id="add-password" name="password" required>
        </div>
        <button type="submit" class="action-btn save">Add Agent</button>
      </form>
    </div>
  </div>

<!-- Edit Agent Modal -->
<div id="editAgentModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeEditAgentModal()">&times;</span>
    <h2>Edit Agent</h2>
    <form method="POST" action="agent_management.php">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" id="edit-agent-id" name="agent_id">
      <div class="input-group">
        <label for="edit-name">Name</label>
        <input type="text" id="edit-name" name="name" required>
      </div>
      <div class="input-group">
        <label for="edit-email">Email</label>
        <input type="email" id="edit-email" name="email" required>
      </div>
      <div class="input-group">
        <label for="edit-phone">Phone</label>
        <input type="text" id="edit-phone" name="phone" required>
      </div>
      <div class="input-group">
        <label for="edit-ic">IC/Passport</label>
        <input type="text" id="edit-ic" name="ic_passport" required>
      </div>
      <div class="input-group">
        <label for="edit-password">Password <span style="font-weight: normal;">(Leave blank to keep current password)</span></label>
        <input type="password" id="edit-password" name="password" placeholder="Enter new password">
      </div>
      <button type="submit" class="action-btn save"
        onclick="return confirm('Are you sure you want to save these changes?');">
        Save Changes
      </button>
    </form>
  </div>
</div>



  <script>
    // Filter Pending Agents
    function filterPendingAgents() {
      const filterValue = document.getElementById('filter-pending-agents').value.toLowerCase();
      const rows = document.querySelectorAll('#pending-agents-table tr');
      rows.forEach(row => {
        const name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        if (name.includes(filterValue) || email.includes(filterValue)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    // Filter Approved Agents
    function filterApprovedAgents() {
      const filterValue = document.getElementById('filter-approved-agents').value.toLowerCase();
      const rows = document.querySelectorAll('#approved-agents-table tr');
      rows.forEach(row => {
        const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        if (name.includes(filterValue) || email.includes(filterValue)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      });
    }

    // Open Add Agent Modal
    function openAddAgentModal() {
      document.getElementById('addAgentModal').style.display = 'flex';
    }

    function closeAddAgentModal() {
      document.getElementById('addAgentModal').style.display = 'none';
    }

    // Open Edit Agent Modal
function openEditAgentModal(agentId, name, email, phone, icPassport) {
  document.getElementById('edit-agent-id').value = agentId;
  document.getElementById('edit-name').value = name;
  document.getElementById('edit-email').value = email;
  document.getElementById('edit-phone').value = phone;
  document.getElementById('edit-ic').value = icPassport;

  // Clear the password field
  document.getElementById('edit-password').value = '';

  document.getElementById('editAgentModal').style.display = 'flex';
}

function closeEditAgentModal() {
  document.getElementById('editAgentModal').style.display = 'none';
}


    
  </script>
</body>
</html>
