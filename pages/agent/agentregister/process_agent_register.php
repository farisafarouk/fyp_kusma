<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $business_name = $_POST['business_name'];
    $business_address = $_POST['business_address'];
    $password = $_POST['password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert into users table
        $sql_users = "INSERT INTO users (name, email, phone, role, password) VALUES (?, ?, ?, 'agent', ?)";
        $stmt_users = $conn->prepare($sql_users);
        $stmt_users->bind_param("ssss", $name, $email, $phone, $hashed_password);
        $stmt_users->execute();

        // Get the last inserted user ID
        $user_id = $conn->insert_id;

        // Insert into agents table
        $sql_agents = "INSERT INTO agents (user_id, business_name, business_address) VALUES (?, ?, ?)";
        $stmt_agents = $conn->prepare($sql_agents);
        $stmt_agents->bind_param("iss", $user_id, $business_name, $business_address);
        $stmt_agents->execute();

        // Commit transaction
        $conn->commit();

        // Redirect to a success page
        $_SESSION['success'] = "Registration successful!";
        header("Location: ../login/login.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();

        // Log error and show a friendly message
        error_log($e->getMessage());
        $_SESSION['error'] = "An error occurred. Please try again later.";
        header("Location: agentregister.php");
        exit();
    }
} else {
    header("Location: agentregister.php");
    exit();
}
?>
