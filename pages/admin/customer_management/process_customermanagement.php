
<?php
require '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            echo "<tr id='row-{$id}'>
                    <td id='name-{$id}'>{$name}</td>
                    <td id='email-{$id}'>{$email}</td>
                    <td id='password-{$id}'>********</td>
                    <td>customer</td>
                    <td>
                      <div class='action-btn-group'>
                        <button class='action-btn edit' onclick='openEditCustomerModal({$id}, \"{$name}\", \"{$email}\")'><i class='fas fa-edit'></i> Edit</button>
                        <button class='action-btn delete' onclick='deleteCustomer({$id})'><i class='fas fa-trash-alt'></i> Delete</button>
                      </div>
                    </td>
                  </tr>";
        } else {
            echo "Error adding customer.";
        }

    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($password)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $email, $hashedPassword, $id);
        } else {
            $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $email, $id);
        }

        if ($stmt->execute()) {
            echo "Success";
        } else {
            echo "Error updating customer.";
        }

    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Success";
        } else {
            echo "Error deleting customer.";
        }
    }
}
?>
