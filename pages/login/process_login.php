<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the user is trying to log in as admin
    if ($email === 'admin@kusma.com' && $password === 'password') {
        // Set session variables for admin
        $_SESSION['user_id'] = 0; // You can set a specific ID for admin if needed
        $_SESSION['role'] = 'admin';
        header("Location: ../admin/admindashboard.php");
        exit();
    }

    // Query to check if the email exists in the database for other users
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Check if the password matches
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            switch ($user['role']) {
                case 'customer':
                    // Check form completion status for customers
                    switch ($user['form_status']) {
                        case 'personal':
                            header("Location: ../customer/personal_details.php");
                            break;
                        case 'business':
                            header("Location: ../customer/business_details.php");
                            break;
                        case 'education':
                            header("Location: ../customer/edu_resources.php");
                            break;
                        case 'completed':
                            header("Location: ../customer/profile/user_dashboard.php");
                            break;
                        default:
                            $_SESSION['error'] = "Unexpected form status.";
                            header("Location: login.php");
                            break;
                    }
                    break;

                case 'agent':
                    // Check approval status for agents
                    $agent_sql = "SELECT approval_status FROM agents WHERE user_id = ?";
                    $agent_stmt = $conn->prepare($agent_sql);
                    $agent_stmt->bind_param("i", $user['id']);
                    $agent_stmt->execute();
                    $agent_result = $agent_stmt->get_result();

                    if ($agent_result->num_rows === 1) {
                        $agent = $agent_result->fetch_assoc();
                        if ($agent['approval_status'] === 'approved') {
                            // Redirect to agent dashboard
                            header("Location: ../agent/agentdashboard.php");
                        } else {
                            $_SESSION['error'] = "Your registration as an agent is pending approval.";
                            header("Location: login.php");
                        }
                    } else {
                        $_SESSION['error'] = "Agent data not found.";
                        header("Location: login.php");
                    }
                    break;

                case 'admin':
                    // Redirect to admin dashboard
                    header("Location: ../admin/admindashboard.php");
                    break;

                case 'consultant':
                    // Redirect to consultant dashboard
                    header("Location: ../consultant/consultantdashboard.php");
                    break;

                default:
                    $_SESSION['error'] = "Invalid role.";
                    header("Location: login.php");
                    exit();
            }
            exit();
        }
    }

    // If credentials are invalid
    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login.php");
}
