<?php
session_start();
require_once '../../config/database.php';
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in as a consultant
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'consultant') {
    header("Location: ../login/login.php");
    exit();
}

$consultant_id = $_SESSION['user_id'];
$message = "";

// Fetch consultant details
$sql = "SELECT u.name, u.email, c.phone, c.expertise, c.rate_per_hour 
        FROM users u 
        INNER JOIN consultants c ON u.id = c.user_id 
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $consultant_id);
$stmt->execute();
$result = $stmt->get_result();
$consultant = $result->fetch_assoc();

if (!$consultant) {
    die("Consultant not found.");
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $expertise = trim($_POST['expertise'] ?? '');
    $rate_per_hour = trim($_POST['rate_per_hour'] ?? '');
    $password = isset($_POST['password']) ? trim($_POST['password']) : ''; // Check if password exists

    // Start a transaction to ensure data integrity
    $conn->begin_transaction();

    try {
        // Update users table
        $update_user_sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
        $stmt_user = $conn->prepare($update_user_sql);
        $stmt_user->bind_param("ssi", $name, $email, $consultant_id);
        $stmt_user->execute();

        // Update consultants table
        $update_consultant_sql = "UPDATE consultants SET phone = ?, expertise = ?, rate_per_hour = ? WHERE user_id = ?";
        $stmt_consultant = $conn->prepare($update_consultant_sql);
        $stmt_consultant->bind_param("ssdi", $phone, $expertise, $rate_per_hour, $consultant_id);
        $stmt_consultant->execute();

        // Update password if provided
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_password_sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt_password = $conn->prepare($update_password_sql);
            $stmt_password->bind_param("si", $hashed_password, $consultant_id);
            $stmt_password->execute();
        }

        // Commit the transaction
        $conn->commit();

        // Success message
        $message = "Profile updated successfully!";
    } catch (mysqli_sql_exception $e) {
        // Roll back the transaction if any error occurs
        $conn->rollback();

        // Error message
        $message = "An error occurred: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultant Profile Management</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/consultantsidebar.css">
    <link rel="stylesheet" href="../../assets/css/consultant_profile.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'consultantsidebar.php'; ?>

        <main class="dashboard-content">
            <section class="dashboard-section">
                <h1>Manage Profile</h1>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($consultant['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($consultant['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($consultant['phone']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="expertise" class="form-label">Expertise</label>
                        <input type="text" class="form-control" id="expertise" name="expertise" value="<?= htmlspecialchars($consultant['expertise']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="rate_per_hour" class="form-label">Rate Per Hour</label>
                        <input type="number" class="form-control" id="rate_per_hour" name="rate_per_hour" step="0.01" value="<?= htmlspecialchars($consultant['rate_per_hour']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password (Leave blank if unchanged)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>
