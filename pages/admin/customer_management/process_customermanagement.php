<?php
require '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            echo "<tr id='row-$id'>
                    <td>$name</td>
                    <td>$email</td>
                    <td>********</td>
                    <td>customer</td>
                    <td>
                      <div class='action-btn-group'>
                        <button class='action-btn edit' onclick=\"openEditCustomerModal($id, '$name', '$email')\"><i class='fas fa-edit'></i> Edit</button>
                        <button class='action-btn delete' onclick='deleteCustomer($id)'><i class='fas fa-trash-alt'></i> Delete</button>
                      </div>
                    </td>
                  </tr>";
        }

    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $email, $hashed, $id);
        } else {
            $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $name, $email, $id);
        }

        echo $stmt->execute() ? "Success" : "Error";

    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;

        if (!$id) {
            echo "Missing ID";
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Success";
        } else {
            echo "Error: " . $stmt->error;
        }
        exit();
    }
}
?>
