<?php
session_start();
require_once '../../../config/database.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user profile data
$sqlUser = "
    SELECT 
        CONCAT(IFNULL(pd.first_name, ''), ' ', IFNULL(pd.last_name, '')) AS name
    FROM personal_details pd
    WHERE pd.user_id = ?
";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->bind_param("i", $user_id);
$stmtUser->execute();
$userProfile = $stmtUser->get_result()->fetch_assoc() ?? [];

// Set user name or default to "Guest"
$name = $userProfile['name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../../../assets/css/customer_dashboard.css">
    <link rel="stylesheet" href="../../../assets/css/customer_navbar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"> <!-- Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"> <!-- Fonts -->
</head>
<body>
    <!-- Navbar -->
    <?php include '../customer_navbar.php'; ?>

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Welcome, <span id="customer-name"><?= htmlspecialchars($name) ?></span>!</h1>
            <p>Your personalized dashboard to manage everything in one place.</p>
        </header>

        <!-- Features Section -->
        <div class="dashboard-sections">
            <!-- Profile Management -->
            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="card-content">
                    <h2>Manage Profile</h2>
                    <p>Update your personal details and preferences.</p>
                    <a href="manage_profile.php" class="dashboard-btn">Go to Profile</a>
                </div>
            </div>

            <!-- Recommendations -->
            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div class="card-content">
                    <h2>Recommendations</h2>
                    <p>Explore personalized loans, grants, and training programs.</p>
                    <a href="../recommendations.php" class="dashboard-btn">View Recommendations</a>
                </div>
            </div>

            <!-- Consultant Appointments -->
            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="card-content">
                    <h2>Consultant Appointments</h2>
                    <p>Schedule, reschedule, or cancel your appointments.</p>
                    <a href="../booking/customer_appointments.php" class="dashboard-btn">Manage Appointments</a>
                </div>
            </div>

            <!-- Notifications -->
            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="card-content">
                    <h2>Notifications</h2>
                    <p>Stay updated on appointments, recommendations, and more.</p>
                    <a href="customer_notifications.php" class="dashboard-btn">View Notifications</a>
                </div>
            </div>

            <!-- Logout -->
            <div class="dashboard-card">
                <div class="card-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <div class="card-content">
                    <h2>Logout</h2>
                    <p>Log out securely to ensure your session is safe.</p>
                    <a href="../../../login/logout.php" class="dashboard-btn logout-btn">Logout</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
