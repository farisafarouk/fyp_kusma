<?php
session_start();
require_once '../../../config/database.php';

// Ensure the user is logged in and has the "admin" role
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin') {
    // Fetch all referrals data for the admin to manage
    $sql_referrals = "SELECT u.name AS agent_name, a.name AS customer_name, a.email AS customer_email, r.referral_date, r.status, r.commission_status 
                      FROM users u
                      JOIN referrals r ON u.id = r.agent_id
                      JOIN users a ON a.id = r.customer_id";
    $stmt_referrals = $conn->prepare($sql_referrals);
    $stmt_referrals->execute();
    $referrals_result = $stmt_referrals->get_result();
} else {
    header("Location: ../../login/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Referral Tracking</title>
    <link rel="stylesheet" href="../../../assets/css/admin_referral.css">
    <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include '../adminsidebar.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-content">
            <section class="dashboard-section">
                <h1><i class="fas fa-users"></i> Referral Tracking</h1>
                <p>Manage all agent referrals and monitor commissions.</p>

                <!-- Referral Table -->
                <table class="referral-table">
                    <thead>
                        <tr>
                            <th>Agent Name</th>
                            <th>Customer Name</th>
                            <th>Email</th>
                            <th>Referral Date</th>
                            <th>Status</th>
                            <th>Commission Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($referrals_result->num_rows > 0): ?>
                            <?php while ($row = $referrals_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['agent_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['customer_email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['referral_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['commission_status']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No referrals found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>
</html>
