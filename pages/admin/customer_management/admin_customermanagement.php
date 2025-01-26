<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Management</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
  <link rel="stylesheet" href="../../../assets/css/admin_customermanagement.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
    // Filter table by email
    function filterTable() {
        const emailFilter = document.getElementById('filter-email').value.toLowerCase();
        const rows = document.querySelectorAll('#customer-table-body tr');

        rows.forEach(row => {
            const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();

            if (email.includes(emailFilter)) {
                row.style.display = ''; // Show row if it matches
            } else {
                row.style.display = 'none'; // Hide row if it doesn't match
            }
        });
    }

    // Open and close the "Add Customer" modal
    function openAddCustomerModal() {
        document.getElementById('addCustomerModal').style.display = 'flex';
    }

    function closeAddCustomerModal() {
        document.getElementById('addCustomerModal').style.display = 'none';
    }
  </script>
</body>
</html>
