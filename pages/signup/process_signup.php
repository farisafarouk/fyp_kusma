<?php
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        session_start();
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['role'] = 'customer';
        header("Location: ../customer/personal_details.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
