<?php
session_start();
require '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

$result = $conn->query("SELECT id, name, email, '********' AS password, role FROM users WHERE role = 'customer'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Customer Management</title>
  <link rel="stylesheet" href="../../../assets/css/adminsidebar.css" />
  <link rel="stylesheet" href="../../../assets/css/admin_customermanagement.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="dashboard-container">
  <?php include '../adminsidebar.php'; ?>

  <main class="dashboard-content">
    <section class="dashboard-section">
      <h1><i class="fas fa-users"></i> Customer Management</h1>
      <p>Manage all registered customers: View, add, update, or delete their details.</p>

      <div class="filter-container">
        <input type="text" id="filter-email" placeholder="Search by Email" onkeyup="filterTable()" />
      </div>

      <button class="action-btn add" onclick="openAddCustomerModal()">+ Add Customer</button>

      <table class="contact-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Password</th>
            <th>Role</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="customer-table-body">
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr id="row-<?= $row['id'] ?>">
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td>********</td>
              <td><?= $row['role'] ?></td>
              <td>
                <div class="action-btn-group">
                  <button class="action-btn edit" onclick="openEditCustomerModal(<?= $row['id'] ?>, '<?= $row['name'] ?>', '<?= $row['email'] ?>')"> Edit</button>
                  <button class="action-btn delete" onclick="deleteCustomer(<?= $row['id'] ?>)"> Delete</button>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>
  </main>
</div>

<!-- Add Modal -->
<div id="addCustomerModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeAddCustomerModal()">&times;</span>
    <h2>Add New Customer</h2>
    <form id="addCustomerForm">
      <input type="hidden" name="action" value="add" />
      <div class="input-group">
        <label>Name</label>
        <input type="text" name="name" required />
      </div>
      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" required />
      </div>
      <div class="input-group">
        <label>Password</label>
        <input type="password" name="password" required />
      </div>
      <button type="submit" class="action-btn save">Add</button>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div id="editCustomerModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" onclick="closeEditCustomerModal()">&times;</span>
    <h2>Edit Customer</h2>
    <form id="editCustomerForm">
      <input type="hidden" name="action" value="edit" />
      <input type="hidden" name="id" id="edit-customer-id" />
      <div class="input-group">
        <label>Name</label>
        <input type="text" name="name" id="edit-name" required />
      </div>
      <div class="input-group">
        <label>Email</label>
        <input type="email" name="email" id="edit-email" required />
      </div>
      <div class="input-group">
        <label>Password (Leave blank to keep current)</label>
        <input type="password" name="password" />
      </div>
      <button type="submit" class="action-btn save">Save</button>
    </form>
  </div>
</div>

<script>
  function filterTable() {
    const val = document.getElementById('filter-email').value.toLowerCase();
    $('#customer-table-body tr').each(function () {
      $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
    });
  }

  function openAddCustomerModal() {
    $('#addCustomerModal').css('display', 'flex');
  }

  function closeAddCustomerModal() {
    $('#addCustomerModal').hide();
  }

  function openEditCustomerModal(id, name, email) {
    $('#edit-customer-id').val(id);
    $('#edit-name').val(name);
    $('#edit-email').val(email);
    $('#editCustomerModal').css('display', 'flex');
  }

  function closeEditCustomerModal() {
    $('#editCustomerModal').hide();
  }

  $('#addCustomerForm').submit(function (e) {
    e.preventDefault();
    $.post('process_customermanagement.php', $(this).serialize(), function (response) {
      $('#customer-table-body').append(response);
      $('#addCustomerForm')[0].reset();
      closeAddCustomerModal();
    });
  });

  $('#editCustomerForm').submit(function (e) {
    e.preventDefault();
    $.post('process_customermanagement.php', $(this).serialize(), function (response) {
      if (response.trim() === 'Success') {
        location.reload();
      } else {
        alert('Update failed.');
      }
    });
  });

  function deleteCustomer(id) {
    if (confirm("Delete this customer?")) {
      $.post('process_customermanagement.php', { action: 'delete', id: id }, function (res) {
        if (res.trim() === 'Success') {
          $(`#row-${id}`).remove();
        } else {
          alert('Deletion failed.');
        }
      });
    }
  }
</script>
</body>
</html>
