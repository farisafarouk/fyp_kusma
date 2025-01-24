<?php
require '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            echo "<tr id='row-{$newId}'>
                    <td>{$newId}</td>
                    <td id='name-{$newId}'>{$name}</td>
                    <td id='email-{$newId}'>{$email}</td>
                    <td id='password-{$newId}'>********</td>
                    <td>customer</td>
                    <td>
                      <div class='action-btn-group'>
                        <button class='action-btn edit' onclick='enableEdit({$newId})'><i class='fas fa-edit'></i> Edit</button>
                        <button class='action-btn save' id='save-{$newId}' style='display:none;' onclick='saveChanges({$newId})'><i class='fas fa-save'></i> Save</button>
                        <button class='action-btn cancel' id='cancel-{$newId}' style='display:none;' onclick='cancelEdit({$newId})'><i class='fas fa-times'></i> Cancel</button>
                        <button class='action-btn delete' onclick='deleteCustomer({$newId})'><i class='fas fa-trash-alt'></i> Delete</button>
                      </div>
                    </td>
                  </tr>";
        } else {
            echo "Error adding customer: " . $stmt->error;
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];

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
            echo "Error updating customer details.";
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'];
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
