<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Management</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_customermanagement.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
  <div class="dashboard-container">
    <?php include '../adminsidebar.php'; ?>

    <main class="dashboard-content">
      <section class="dashboard-section">
        <h1><i class="fas fa-users"></i> Customer Management</h1>
        <p>Manage all registered customers: View, add, update, or delete their details.</p>
        
        <!-- Filter Container -->
        <div class="filter-container">
          <input type="text" id="filter-email" placeholder="Search by Email" onkeyup="filterTable()">
        </div>
        
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
                                <button class='action-btn edit' onclick='openEditCustomerModal({$row['id']}, \"{$row['name']}\", \"{$row['email']}\")'><i class='fas fa-edit'></i> Edit</button>
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
        <form id="addCustomerForm">
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
            <label for="add-password">Password</label>
            <input type="password" id="add-password" name="password" required>
          </div>
          <button type="button" class="action-btn save" onclick="addCustomer()">Add Customer</button>
        </form>
      </div>
    </div>

    <!-- Edit Customer Modal -->
    <div id="editCustomerModal" class="modal">
      <div class="modal-content">
        <span class="close-btn" onclick="closeEditCustomerModal()">&times;</span>
        <h2>Edit Customer</h2>
        <form id="editCustomerForm">
          <input type="hidden" name="id" id="edit-customer-id">
          <div class="input-group">
            <label for="edit-name">Name</label>
            <input type="text" id="edit-name" name="name" required>
          </div>
          <div class="input-group">
            <label for="edit-email">Email</label>
            <input type="email" id="edit-email" name="email" required>
          </div>
          <div class="input-group">
            <label for="edit-password">Password (Leave blank to keep current password)</label>
            <input type="password" id="edit-password" name="password">
          </div>
          <button type="button" class="action-btn save" onclick="saveChanges()">Save Changes</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function filterTable() {
      const emailFilter = document.getElementById('filter-email').value.toLowerCase();
      const rows = document.querySelectorAll('#customer-table-body tr');
      rows.forEach(row => {
        const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
        row.style.display = email.includes(emailFilter) ? '' : 'none';
      });
    }

    function openAddCustomerModal() {
      document.getElementById('addCustomerModal').style.display = 'flex';
    }

    function closeAddCustomerModal() {
      document.getElementById('addCustomerModal').style.display = 'none';
    }

    function openEditCustomerModal(id, name, email) {
      document.getElementById('edit-customer-id').value = id;
      document.getElementById('edit-name').value = name;
      document.getElementById('edit-email').value = email;
      document.getElementById('editCustomerModal').style.display = 'flex';
    }

    function closeEditCustomerModal() {
      document.getElementById('editCustomerModal').style.display = 'none';
    }

    function addCustomer() {
      const formData = $('#addCustomerForm').serialize() + '&action=add';
      $.post('process_customermanagement.php', formData, function(response) {
        $('#customer-table-body').append(response);
        $('#addCustomerForm')[0].reset();
        closeAddCustomerModal();
        alert('Customer added successfully!');
      }).fail(() => alert('Failed to add customer.'));
    }

    function saveChanges() {
      const formData = $('#editCustomerForm').serialize() + '&action=edit';
      $.post('process_customermanagement.php', formData, function(response) {
        if (response.trim() === 'Success') {
          location.reload();
        } else {
          alert('Failed to update customer.');
        }
      }).fail(() => alert('Failed to update customer.'));
    }

    function deleteCustomer(id) {
      if (confirm('Are you sure you want to delete this customer?')) {
        $.post('process_customermanagement.php', { action: 'delete', id }, function(response) {
          if (response.trim() === 'Success') {
            $(`#row-${id}`).remove();
            alert('Customer deleted successfully!');
          } else {
            alert('Failed to delete customer.');
          }
        }).fail(() => alert('Failed to delete customer.'));
      }
    }
  </script>
</body>
</html>

