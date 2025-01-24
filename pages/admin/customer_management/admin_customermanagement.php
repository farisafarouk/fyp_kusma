<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Management</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_agentmanagement.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
  <div class="dashboard-container">
    <?php include '../adminsidebar.php'; ?>

    <main class="dashboard-content">
      <section class="dashboard-section">
        <h1><i class="fas fa-users"></i> Customer Management</h1>
        <p>Manage all registered customers: View, add, update, or delete their details.</p>
        <button class="action-btn add" onclick="openAddCustomerModal()">+ Add Customer</button>

        <!-- Customer Table -->
        <table class="contact-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Password</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="customer-table-body">
            <?php
            require '../../../config/database.php';

            // Fetch customers from the database
            $sql = "SELECT id, name, email, '********' AS password, role FROM users WHERE role = 'customer'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr id='row-{$row['id']}'>
                            <td>{$row['id']}</td>
                            <td id='name-{$row['id']}'>{$row['name']}</td>
                            <td id='email-{$row['id']}'>{$row['email']}</td>
                            <td id='password-{$row['id']}'>{$row['password']}</td>
                            <td>{$row['role']}</td>
                            <td>
                              <div class='action-btn-group'>
                                <button class='action-btn edit' onclick='enableEdit({$row['id']})'><i class='fas fa-edit'></i> Edit</button>
                                <button class='action-btn save' id='save-{$row['id']}' style='display:none;' onclick='saveChanges({$row['id']})'><i class='fas fa-save'></i> Save</button>
                                <button class='action-btn cancel' id='cancel-{$row['id']}' style='display:none;' onclick='cancelEdit({$row['id']})'><i class='fas fa-times'></i> Cancel</button>
                                <button class='action-btn delete' onclick='deleteCustomer({$row['id']})'><i class='fas fa-trash-alt'></i> Delete</button>
                              </div>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No customers found.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </section>
    </main>

    <!-- Add Customer Modal -->
    <div id="addCustomerModal" class="modal">
      <div class="modal-content">
        <span class="close-btn" onclick="closeAddCustomerModal()">&times;</span>
        <h2>Add New Customer</h2>
        <form id="addCustomerForm" action="process_customermanagement.php" method="POST">
          <input type="hidden" name="action" value="add">
          <div class="input-group">
            <label for="add-name">Name</label>
            <input type="text" id="add-name" name="name" placeholder="Enter customer name" required>
          </div>
          <div class="input-group">
            <label for="add-email">Email</label>
            <input type="email" id="add-email" name="email" placeholder="Enter customer email" required>
          </div>
          <div class="input-group">
            <label for="add-password">Password</label>
            <input type="password" id="add-password" name="password" placeholder="Enter customer password" required>
          </div>
          <button type="submit" class="action-btn save">Add Customer</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    // Open Add Customer Modal
    function openAddCustomerModal() {
        document.getElementById('addCustomerModal').style.display = 'flex';
    }

    // Close Add Customer Modal
    function closeAddCustomerModal() {
        document.getElementById('addCustomerModal').style.display = 'none';
    }

    // Delete Customer with AJAX
    function deleteCustomer(id) {
        if (confirm('Are you sure you want to delete this customer?')) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'process_customermanagement.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    const row = document.getElementById(`row-${id}`);
                    if (row) {
                        row.remove();
                    }
                } else {
                    alert('Error deleting customer.');
                }
            };
            xhr.send(`action=delete&id=${id}`);
        }
    }

    // Enable editing for a specific row
    function enableEdit(id) {
        const nameCell = document.getElementById(`name-${id}`);
        const emailCell = document.getElementById(`email-${id}`);
        const passwordCell = document.getElementById(`password-${id}`);
        const saveButton = document.getElementById(`save-${id}`);
        const cancelButton = document.getElementById(`cancel-${id}`);

        // Backup original values in data attributes
        nameCell.setAttribute('data-original', nameCell.textContent);
        emailCell.setAttribute('data-original', emailCell.textContent);
        passwordCell.setAttribute('data-original', passwordCell.textContent);

        // Make cells editable
        nameCell.innerHTML = `<input type='text' id='edit-name-${id}' value='${nameCell.textContent}' class='edit-input'>`;
        emailCell.innerHTML = `<input type='email' id='edit-email-${id}' value='${emailCell.textContent}' class='edit-input'>`;
        passwordCell.innerHTML = `<input type='password' id='edit-password-${id}' placeholder='New Password' class='edit-input'>`;

        saveButton.style.display = 'inline-block';
        cancelButton.style.display = 'inline-block';
    }

    // Cancel editing for a specific row
    function cancelEdit(id) {
        const nameCell = document.getElementById(`name-${id}`);
        const emailCell = document.getElementById(`email-${id}`);
        const passwordCell = document.getElementById(`password-${id}`);
        const saveButton = document.getElementById(`save-${id}`);
        const cancelButton = document.getElementById(`cancel-${id}`);

        // Restore original values
        nameCell.textContent = nameCell.getAttribute('data-original');
        emailCell.textContent = emailCell.getAttribute('data-original');
        passwordCell.textContent = '********';

        saveButton.style.display = 'none';
        cancelButton.style.display = 'none';
    }

    // Save Changes with AJAX
    function saveChanges(id) {
        const name = document.getElementById(`edit-name-${id}`).value;
        const email = document.getElementById(`edit-email-${id}`).value;
        const password = document.getElementById(`edit-password-${id}`).value;

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'process_customermanagement.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function () {
            if (xhr.status === 200) {
                const nameCell = document.getElementById(`name-${id}`);
                const emailCell = document.getElementById(`email-${id}`);
                const passwordCell = document.getElementById(`password-${id}`);
                nameCell.textContent = name;
                emailCell.textContent = email;
                passwordCell.textContent = '********'; // Mask the password after update
                document.getElementById(`save-${id}`).style.display = 'none';
                document.getElementById(`cancel-${id}`).style.display = 'none';
            } else {
                alert('Failed to save changes.');
            }
        };
        xhr.send(`action=edit&id=${id}&name=${name}&email=${email}&password=${password}`);
    }
  </script>
</body>
</html>
