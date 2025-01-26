<?php
require '../../../config/database.php';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $updates = json_decode($_POST['updates'], true);

        if (!empty($id) && is_array($updates)) {
            $fields = [];
            $params = [];
            $types = '';

            foreach ($updates as $field => $value) {
                $fields[] = "$field = ?";
                $params[] = $value;
                $types .= is_int($value) ? 'i' : 's';
            }

            $params[] = $id;
            $types .= 'i';

            $query = "UPDATE programs SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Resource updated successfully.']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error updating resource: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid ID or updates.']);
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';

        if (!empty($id)) {
            $stmt = $conn->prepare("DELETE FROM programs WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $id);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Resource deleted successfully.']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error deleting resource: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid resource ID.']);
        }
    } elseif ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $resource_types = $_POST['resource_types'] ?? '';
        $agency_id = $_POST['agency_id'] ?? '';

        if (!empty($name) && !empty($description) && !empty($resource_types) && !empty($agency_id)) {
            $stmt = $conn->prepare("INSERT INTO programs (name, description, resource_types, agency_id) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param('sssi', $name, $description, $resource_types, $agency_id);
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Resource added successfully.']);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error adding resource: ' . $stmt->error]);
                }
                $stmt->close();
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error preparing statement: ' . $conn->error]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
    exit();
}

// Fetch resources and agencies for the frontend
$sql = "SELECT programs.id, programs.name, programs.description, programs.resource_types, agencies.name AS agency, programs.agency_id
        FROM programs
        INNER JOIN agencies ON programs.agency_id = agencies.id";
$result = $conn->query($sql);

$agency_sql = "SELECT id, name FROM agencies";
$agency_result = $conn->query($agency_sql);

$resource_types = ['Loan', 'Grant', 'Training', 'Premises', 'Other'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Resources</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_resources.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="dashboard-container">
    <?php include '../adminsidebar.php'; ?>

    <main class="dashboard-content">
      <section class="dashboard-section">
        <h1><i class="fas fa-folder-open"></i> Manage Resources</h1>
        <p>Add, update, or remove resources such as loans, grants, and training programs.</p>
        <div class="filter-container">
          <input type="text" id="filter-input" placeholder="Search by Program Name" onkeyup="filterTable()">
        </div>
        <button class="action-btn add" onclick="openAddResourceModal()">+ Add Resource</button>

        <table id="resources-table" class="contact-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Description</th>
              <th>Type</th>
              <th>Agency</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "
                    <tr id='row-{$row['id']}'>
                      <td>{$row['id']}</td>
                      <td data-id='{$row['id']}' data-field='name'>{$row['name']}</td>
                      <td data-id='{$row['id']}' data-field='description'>{$row['description']}</td>
                      <td data-id='{$row['id']}' data-field='resource_types'>
                        <span>{$row['resource_types']}</span>
                        <select class='edit-input' style='display:none;'>
                          " . implode('', array_map(fn($type) => "<option value='{$type}' " . ($type === $row['resource_types'] ? 'selected' : '') . ">{$type}</option>", $resource_types)) . "
                        </select>
                      </td>
                      <td data-id='{$row['id']}' data-field='agency_id'>
                        <span>{$row['agency']}</span>
                        <select class='edit-input' style='display:none;'>
                          " . implode('', array_map(fn($agency) => "<option value='{$agency['id']}' " . ($agency['id'] == $row['agency_id'] ? 'selected' : '') . ">{$agency['name']}</option>", $agency_result->fetch_all(MYSQLI_ASSOC))) . "
                        </select>
                      </td>
                      <td class='action-btn-group'>
                        <button class='action-btn edit' onclick='editRow({$row['id']})'>Edit</button>
                        <button class='action-btn save' id='save-{$row['id']}' style='display:none;' onclick='saveRow({$row['id']})'>Save</button>
                        <button class='action-btn delete' onclick='deleteRow({$row['id']})'>Delete</button>
                      </td>

                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No resources found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </section>
    </main>

    <!-- Add Resource Modal -->
    <div id="addResourceModal" class="modal">
      <div class="modal-content">
        <span class="close-btn" onclick="closeAddResourceModal()">&times;</span>
        <h2>Add New Resource</h2>
        <form id="addResourceForm">
          <input type="hidden" name="action" value="add">
          <div class="input-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>
          </div>
          <div class="input-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" required></textarea>
          </div>
          <div class="input-group">
            <label for="type">Type</label>
            <select id="type" name="resource_types" required>
              <?php foreach ($resource_types as $type) {
                  echo "<option value='{$type}'>{$type}</option>";
              } ?>
            </select>
          </div>
          <div class="input-group">
            <label for="agency">Agency</label>
            <select id="agency" name="agency_id" required>
              <?php
              $agency_result->data_seek(0);
              while ($agency = $agency_result->fetch_assoc()) {
                  echo "<option value='{$agency['id']}'>{$agency['name']}</option>";
              }
              ?>
            </select>
          </div>
          <button type="button" class="action-btn save" onclick="addResource()">Add Resource</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openAddResourceModal() {
        document.getElementById('addResourceModal').style.display = 'flex';
    }

    function closeAddResourceModal() {
        document.getElementById('addResourceModal').style.display = 'none';
    }

    function filterTable() {
        const filterInput = document.getElementById('filter-input').value.toLowerCase();
        const rows = document.querySelectorAll('#resources-table tbody tr');
        rows.forEach(row => {
            const nameCell = row.querySelector('td:nth-child(2)');
            row.style.display = nameCell && nameCell.textContent.toLowerCase().includes(filterInput) ? '' : 'none';
        });
    }

    function editRow(id) {
        const editableCells = document.querySelectorAll(`[data-id='${id}']`);
        editableCells.forEach(cell => {
            const span = cell.querySelector('span');
            const select = cell.querySelector('select');
            if (select) {
                span.style.display = 'none';
                select.style.display = 'block';
            } else {
                const originalContent = cell.textContent;
                cell.innerHTML = `<input type="text" value="${originalContent}" class="edit-input">`;
            }
        });
        document.getElementById(`save-${id}`).style.display = 'inline-block';
    }

    function saveRow(id) {
        const editableCells = document.querySelectorAll(`[data-id='${id}']`);
        const updates = {};
        editableCells.forEach(cell => {
            const select = cell.querySelector('select');
            const input = cell.querySelector('input');
            const field = cell.dataset.field;

            if (select) {
                updates[field] = select.value;
            } else if (input) {
                updates[field] = input.value;
            }
        });

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'manage_resources.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                alert('Resource updated successfully.');
                location.reload();
            } else {
                alert('Error updating resource.');
            }
        };
        xhr.send(`action=edit&id=${id}&updates=${encodeURIComponent(JSON.stringify(updates))}`);
    }

    function deleteRow(id) {
        if (confirm('Are you sure you want to delete this resource?')) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'manage_resources.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    document.getElementById(`row-${id}`).remove();
                } else {
                    alert('Error deleting resource.');
                }
            };
            xhr.send(`action=delete&id=${id}`);
        }
    }

    function addResource() {
        const form = document.getElementById('addResourceForm');
        const formData = new FormData(form);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'manage_resources.php', true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                alert('Resource added successfully.');
                location.reload();
            } else {
                alert('Error adding resource.');
            }
        };
        xhr.send(formData);
    }
  </script>
</body>
</html>
