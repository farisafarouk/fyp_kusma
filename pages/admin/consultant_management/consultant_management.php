<?php
session_start();
require_once '../../../config/database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        $expertise = $_POST['expertise'] ?? '';
        $rate_per_hour = $_POST['rate_per_hour'] ?? 0.00;
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

        // Insert into `users` table
        $sql_user = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'consultant')";
        $stmt_user = $conn->prepare($sql_user);
        $stmt_user->bind_param('sss', $name, $email, $password);

        if ($stmt_user->execute()) {
            $user_id = $stmt_user->insert_id;

            // Insert into `consultants` table
            $sql_consultant = "INSERT INTO consultants (user_id, phone, expertise, rate_per_hour) VALUES (?, ?, ?, ?)";
            $stmt_consultant = $conn->prepare($sql_consultant);
            $stmt_consultant->bind_param('issd', $user_id, $phone, $expertise, $rate_per_hour);

            if ($stmt_consultant->execute()) {
                header("Location: consultant_management.php");
                exit();
            } else {
                die("Error adding consultant: " . $stmt_consultant->error);
            }
        } else {
            die("Error adding user: " . $stmt_user->error);
        }
    } elseif ($action === 'edit') {
        $consultant_id = $_POST['consultant_id'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $expertise = $_POST['expertise'] ?? '';
        $rate_per_hour = $_POST['rate_per_hour'] ?? 0.00;
        $password = $_POST['password'] ?? null;

        // Update `users` and `consultants` tables
        $sql_update = "UPDATE users u
                       INNER JOIN consultants c ON u.id = c.user_id
                       SET u.name = ?, u.email = ?, c.phone = ?, c.expertise = ?, c.rate_per_hour = ?";

        $params = [$name, $email, $phone, $expertise, $rate_per_hour];
        $types = 'sssds';

        // Update password only if provided
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_update .= ", u.password = ?";
            $params[] = $hashed_password;
            $types .= 's';
        }

        $sql_update .= " WHERE c.id = ?";
        $params[] = $consultant_id;
        $types .= 'i';

        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param($types, ...$params);

        if ($stmt_update->execute()) {
            header("Location: consultant_management.php");
            exit();
        } else {
            die("Error updating consultant: " . $stmt_update->error);
        }
    } elseif ($action === 'delete') {
        $consultant_id = $_POST['consultant_id'] ?? '';

        // Delete from `consultants` and `users` tables
        $sql_delete = "DELETE c, u FROM consultants c
                       INNER JOIN users u ON c.user_id = u.id
                       WHERE c.id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param('i', $consultant_id);

        if ($stmt_delete->execute()) {
            header("Location: consultant_management.php");
            exit();
        } else {
            die("Error deleting consultant: " . $stmt_delete->error);
        }
    }
}

// Fetch consultants
$sql_consultants = "SELECT c.id AS consultant_id, u.id AS user_id, u.name, u.email, c.phone, c.expertise, c.rate_per_hour, c.rating, c.feedback_count
                     FROM consultants c
                     INNER JOIN users u ON c.user_id = u.id";
$result_consultants = $conn->query($sql_consultants);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Consultants</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_customermanagement.css">
</head>
<body>
  <div class="dashboard-container">
    <?php include '../adminsidebar.php'; ?>

    <main class="dashboard-content">
      <section class="dashboard-section">
        <h1><i class="fas fa-user-tie"></i> Manage Consultants</h1>
        <p>Edit, delete, or add new consultants.</p>
        <button class="action-btn add" onclick="openAddConsultantModal()">+ Add Consultant</button>

        <table class="contact-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Expertise</th>
              <th>Rate/Hour</th>
              <th>Rating</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result_consultants->fetch_assoc()): ?>
              <tr id="row-<?php echo $row['consultant_id']; ?>">
                <td><?php echo htmlspecialchars($row['consultant_id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td><?php echo htmlspecialchars($row['expertise']); ?></td>
                <td><?php echo htmlspecialchars($row['rate_per_hour']); ?></td>
                <td><?php echo htmlspecialchars($row['rating'] ?? 'N/A'); ?></td>
                <td>
                  <!-- Edit Button with Icon -->
                  <button class="action-btn edit" onclick="openEditConsultantModal(
                    <?php echo $row['consultant_id']; ?>,
                    '<?php echo htmlspecialchars($row['name']); ?>',
                    '<?php echo htmlspecialchars($row['email']); ?>',
                    '<?php echo htmlspecialchars($row['phone']); ?>',
                    '<?php echo htmlspecialchars($row['expertise']); ?>',
                    '<?php echo htmlspecialchars($row['rate_per_hour']); ?>'
                  )">
                    <i class="fas fa-edit"></i> Edit
                  </button>

                  <!-- Delete Button with Icon -->
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="consultant_id" value="<?php echo $row['consultant_id']; ?>">
                    <button type="submit" name="action" value="delete" class="action-btn delete">
                      <i class="fas fa-trash-alt"></i> Delete
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

  <!-- Add Consultant Modal -->
  <div id="addConsultantModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeAddConsultantModal()">&times;</span>
      <h2>Add Consultant</h2>
      <form method="POST" action="consultant_management.php">
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
          <label for="add-expertise">Expertise</label>
          <input type="text" id="add-expertise" name="expertise" required>
        </div>
        <div class="input-group">
          <label for="add-rate">Rate/Hour</label>
          <input type="number" id="add-rate" name="rate_per_hour" step="0.01" required>
        </div>
        <div class="input-group">
          <label for="add-password">Password</label>
          <input type="password" id="add-password" name="password" required>
        </div>
        <button type="submit" class="action-btn save">Add Consultant</button>
      </form>
    </div>
  </div>

  <!-- Edit Consultant Modal -->
  <div id="editConsultantModal" class="modal">
    <div class="modal-content">
      <span class="close-btn" onclick="closeEditConsultantModal()">&times;</span>
      <h2>Edit Consultant</h2>
      <form method="POST" action="consultant_management.php">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" id="edit-consultant-id" name="consultant_id">
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
          <label for="edit-expertise">Expertise</label>
          <input type="text" id="edit-expertise" name="expertise" required>
        </div>
        <div class="input-group">
          <label for="edit-rate">Rate/Hour</label>
          <input type="number" id="edit-rate" name="rate_per_hour" step="0.01" required>
        </div>
        <div class="input-group">
          <label for="edit-password">Password (optional)</label>
          <input type="password" id="edit-password" name="password">
        </div>
        <button type="submit" class="action-btn save">Update Consultant</button>
      </form>
    </div>
  </div>

  <script>
    function openAddConsultantModal() {
      document.getElementById("addConsultantModal").style.display = "flex";
    }

    function closeAddConsultantModal() {
      document.getElementById("addConsultantModal").style.display = "none";
    }

    function openEditConsultantModal(id, name, email, phone, expertise, rate_per_hour) {
      document.getElementById("edit-consultant-id").value = id;
      document.getElementById("edit-name").value = name;
      document.getElementById("edit-email").value = email;
      document.getElementById("edit-phone").value = phone;
      document.getElementById("edit-expertise").value = expertise;
      document.getElementById("edit-rate").value = rate_per_hour;
      document.getElementById("editConsultantModal").style.display = "flex";
    }

    function closeEditConsultantModal() {
      document.getElementById("editConsultantModal").style.display = "none";
    }
  </script>
</body>
</html>
