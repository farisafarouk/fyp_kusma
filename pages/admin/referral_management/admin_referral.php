<?php
require '../../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

// Handle commission status update and deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $referral_id = $_POST['referral_id'];
        $new_status = $_POST['commission_status'];
        $stmt = $conn->prepare("UPDATE referrals SET commission_status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $referral_id);
        $stmt->execute();
    }

    if (isset($_POST['delete_referral'])) {
        $referral_id = $_POST['referral_id'];
        $stmt = $conn->prepare("DELETE FROM referrals WHERE id = ?");
        $stmt->bind_param("i", $referral_id);
        $stmt->execute();
    }

    header("Location: admin_referral.php");
    exit();
}

// Fetch all referral grouped by agent
$sql = "
    SELECT 
        r.id, r.agent_id, ua.name AS agent_name, uc.name AS customer_name, 
        r.referral_code, r.status, r.commission_status, r.referral_date
    FROM referrals r
    JOIN users ua ON ua.id = r.agent_id
    JOIN users uc ON uc.id = r.customer_id
    ORDER BY ua.name ASC, r.referral_date DESC
";
$result = $conn->query($sql);

// Group referrals by agent
$grouped = [];
while ($row = $result->fetch_assoc()) {
    $grouped[$row['agent_id']]['name'] = $row['agent_name'];
    $grouped[$row['agent_id']]['referrals'][] = $row;
}

// Compute total commissions
$commission_sql = "
    SELECT agent_id, COUNT(*) AS paid_count, SUM(CASE WHEN commission_status = 'paid' THEN 1 ELSE 0 END) * 1.00 AS total_paid
    FROM referrals
    GROUP BY agent_id
";
$commissions = [];
$cres = $conn->query($commission_sql);
while ($row = $cres->fetch_assoc()) {
    $commissions[$row['agent_id']] = $row['total_paid'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Referral Management</title>
    <link rel="stylesheet" href="../../../assets/css/adminsidebar.css">
    <link rel="stylesheet" href="../../../assets/css/admin_referral.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container">
    <?php include '../adminsidebar.php'; ?>
    <main class="dashboard-content">
        <section class="dashboard-section">
            <h1><i class="fas fa-users"></i> Referral Management</h1>
            <p>View all referrals grouped by agent, update status, and track commission earnings.</p>

            <div class="filter-container">
                <input type="text" id="filterInput" placeholder="Search agent or customer..." onkeyup="filterTable()">
            </div>

            <div id="referralTable">
                <?php foreach ($grouped as $agent_id => $data): ?>
                    <div class="agent-block">
                        <h2><?= htmlspecialchars($data['name']) ?> 
                            <span class="commission-badge">
                                Total Commission: RM <?= number_format($commissions[$agent_id] ?? 0, 2) ?>
                            </span>
                        </h2>

                        <table class="contact-table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Referral Code</th>
                                    <th>Status</th>
                                    <th>Commission</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['referrals'] as $ref): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($ref['customer_name']) ?></td>
                                        <td><?= htmlspecialchars($ref['referral_code']) ?></td>
                                        <td><?= ucfirst($ref['status']) ?></td>
                                      
                                        <td style="text-align: center; font-weight: bold;">
    RM1.00
</td>


                                        <td><?= $ref['referral_date'] ?></td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('Delete this referral?');">
                                                <input type="hidden" name="referral_id" value="<?= $ref['id'] ?>">
                                                <button type="submit" name="delete_referral" class="action-btn delete">
     <span>Delete</span>
</button>

                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>

<script>
function filterTable() {
    const filter = document.getElementById("filterInput").value.toLowerCase();
    const blocks = document.querySelectorAll(".agent-block");
    blocks.forEach(block => {
        const text = block.textContent.toLowerCase();
        block.style.display = text.includes(filter) ? "block" : "none";
    });
}
</script>
</body>
</html>
