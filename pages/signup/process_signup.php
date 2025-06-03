<?php
require_once '../../config/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered.";
        header("Location: signup.php");
        exit();
    }

    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'customer')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $password);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['role'] = 'customer';
        header("Location: ../customer/personal_details.php");
    } else {
        $_SESSION['error'] = "Something went wrong. Please try again.";
        header("Location: signup.php");
    }
}
?>
