<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query to check if the email exists in the database
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
                    // Redirect to agent dashboard
                    header("Location: ../agent/agentdashboard.php");
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
?>
