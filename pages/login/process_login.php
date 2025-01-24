<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Admin login
    if ($email === 'admin@kusma.com' && $password === 'password') {
        $_SESSION['user_id'] = 0;
        $_SESSION['role'] = 'admin';
        header("Location: ../admin/admindashboard.php");
        exit();
    }

    // Check email in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            switch ($user['role']) {
                case 'customer':
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
                    $agent_sql = "SELECT approval_status FROM agents WHERE user_id = ?";
                    $agent_stmt = $conn->prepare($agent_sql);
                    $agent_stmt->bind_param("i", $user['id']);
                    $agent_stmt->execute();
                    $agent_result = $agent_stmt->get_result();

                    if ($agent_result->num_rows === 1) {
                        $agent = $agent_result->fetch_assoc();
                        if ($agent['approval_status'] === 'approved') {
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
                    header("Location: ../admin/admindashboard.php");
                    break;

                case 'consultant':
                    $consultant_sql = "SELECT approval_status FROM consultants WHERE user_id = ?";
                    $consultant_stmt = $conn->prepare($consultant_sql);
                    $consultant_stmt->bind_param("i", $user['id']);
                    $consultant_stmt->execute();
                    $consultant_result = $consultant_stmt->get_result();

                    if ($consultant_result->num_rows === 1) {
                        $consultant = $consultant_result->fetch_assoc();
                        if ($consultant['approval_status'] === 'approved') {
                            header("Location: ../consultant/consultantdashboard.php");
                        } else {
                            $_SESSION['error'] = "Your registration as a consultant is pending approval.";
                            header("Location: login.php");
                        }
                    } else {
                        $_SESSION['error'] = "Consultant data not found.";
                        header("Location: login.php");
                    }
                    break;

                default:
                    $_SESSION['error'] = "Invalid role.";
                    header("Location: login.php");
                    break;
            }
            exit();
        }
    }

    $_SESSION['error'] = "Invalid email or password.";
    header("Location: login.php");
}
