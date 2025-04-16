<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: ../../login/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch agent's earnings
$summary_sql = "SELECT referral_earnings FROM agents WHERE user_id = ?";
$stmt_summary = $conn->prepare($summary_sql);
if (!$stmt_summary) {
    die("SQL error (summary): " . $conn->error);
}
$stmt_summary->bind_param("i", $user_id);
$stmt_summary->execute();
$summary_result = $stmt_summary->get_result();
$summary = $summary_result->fetch_assoc();

// Monthly payout report (with expected and paid commissions)
$monthly_sql = "
    SELECT DATE_FORMAT(referral_date, '%Y-%m') AS month,
           COUNT(*) AS total_referrals,
           SUM(CASE WHEN commission_status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
           COUNT(*) * 1.00 AS total_expected,
           SUM(CASE WHEN commission_status = 'paid' THEN 1.00 ELSE 0 END) AS total_paid
    FROM referrals
    WHERE agent_id = ?
    GROUP BY month
    ORDER BY month DESC
";
$stmt_monthly = $conn->prepare($monthly_sql);
if (!$stmt_monthly) {
    die("SQL error (monthly): " . $conn->error);
}
$stmt_monthly->bind_param("i", $user_id);
$stmt_monthly->execute();
$monthly_result = $stmt_monthly->get_result();

// Referral details
$details_sql = "
    SELECT u.name AS customer_name, r.referral_date, r.status, r.commission_status
    FROM referrals r
    JOIN users u ON u.id = r.customer_id
    WHERE r.agent_id = ?
    ORDER BY r.referral_date DESC
";
$stmt_details = $conn->prepare($details_sql);
if (!$stmt_details) {
    die("SQL error (details): " . $conn->error);
}
$stmt_details->bind_param("i", $user_id);
$stmt_details->execute();
$details_result = $stmt_details->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Commission Dashboard</title>
    <link rel="stylesheet" href="../../../assets/css/agent_sidebar.css">
    <link rel="stylesheet" href="../../../assets/css/agent_referral.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php include '../agentsidebar.php'; ?>

    <main class="dashboard-content">
        <section class="dashboard-section">
            <h1><i class="fas fa-wallet"></i> My Commissions</h1>
            <p>Track your total earnings, referral history, and monthly payouts.</p>

            <div class="profile-form">
                <div class="input-group">
                    <label>Total Earnings (RM)</label>
                    <input type="text" readonly value="<?= number_format($summary['referral_earnings'] ?? 0, 2) ?>">
                </div>
            </div>

            <h2 style="margin-top: 30px;">Monthly Payout Summary</h2>
            <div class="dashboard-section" style="overflow-x:auto;">
                <table class="commission-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Referrals</th>
                            <th>Paid Referrals</th>
                            <th>Total Expected Commission (RM)</th>
                            <th>Paid Commission (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $monthly_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['month'] ?></td>
                                <td><?= $row['total_referrals'] ?></td>
                                <td><?= $row['paid_count'] ?></td>
                                <td><?= number_format($row['total_expected'], 2) ?></td>
                                <td><?= number_format($row['total_paid'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <h2 style="margin-top: 30px;">Referral Details</h2>
            <div class="dashboard-section" style="overflow-x:auto;">
                <table class="commission-table">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Referral Date</th>
                            <th>Status</th>
                            <th>Commission Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $details_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                <td><?= htmlspecialchars($row['referral_date']) ?></td>
                                <td><?= ucfirst($row['status']) ?></td>
                                <td><?= ucfirst($row['commission_status']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
</body>
</html>
