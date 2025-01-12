<?php
require '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Insert new customer
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            header("Location: admin_customermanagement.php");
            exit();
        } else {
            echo "Error adding customer: " . $stmt->error;
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $email = $_POST['email'];

        // Update customer details
        $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $email, $id);

        if ($stmt->execute()) {
            echo "Success";
        } else {
            echo "Error updating customer details.";
        }
    }
} elseif (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];

        // Delete customer
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: admin_customermanagement.php");
            exit();
        } else {
            echo "Error deleting customer.";
        }
    }
}
?>
