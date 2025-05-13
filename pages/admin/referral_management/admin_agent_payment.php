<?php
require '../../../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login/login.php");
    exit();
}

$agent_id = $_GET['agent_id'] ?? null;
if (!$agent_id) {
    die("Agent ID is required.");
}

// Fetch agent details
$agent = $conn->query("SELECT u.name, u.email, a.bank_name, a.bank_account 
                       FROM users u 
                       JOIN agents a ON u.id = a.user_id 
                       WHERE u.id = $agent_id")->fetch_assoc();

// Calculate total commission ever collected
$total_commission = $conn->query("SELECT COUNT(*) as total FROM referrals WHERE agent_id = $agent_id AND commission_status = 'paid'")
                         ->fetch_assoc()['total'];
$total_collected = $total_commission * 1.00;

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bank_name = $_POST['bank_name'];
    $bank_account = $_POST['bank_account'];
    $note = "Manual payout for $total_commission referrals.";

    // Insert dummy payment record
    $stmt = $conn->prepare("INSERT INTO agent_commission_payments (agent_id, amount, note) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $agent_id, $total_collected, $note);
    $stmt->execute();

    // Update agent bank info (if changed)
    $stmt2 = $conn->prepare("UPDATE agents SET bank_name = ?, bank_account = ? WHERE user_id = ?");
    $stmt2->bind_param("ssi", $bank_name, $bank_account, $agent_id);
    $stmt2->execute();

    header("Location: ../referral_management/admin_referral.php?paid=success");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pay Agent Commission</title>
  <link rel="stylesheet" href="../../../assets/css/simulate_gateway.css">
</head>
<body>
<div class="container">
    <form method="POST">
        <a href="../referral_management/admin_referral.php" class="back-icon">&larr; Back</a>

        <div class="row">
            <div class="column">
                <h3 class="title">Agent Details</h3>
                <div class="input-box">
                    <span>Agent Name:</span>
                    <input type="text" value="<?= htmlspecialchars($agent['name']) ?>" disabled>
                </div>
                <div class="input-box">
                    <span>Email:</span>
                    <input type="text" value="<?= htmlspecialchars($agent['email']) ?>" disabled>
                </div>
                <div class="input-box">
                    <span>Bank Name:</span>
                    <input type="text" name="bank_name" value="<?= htmlspecialchars($agent['bank_name'] ?? '') ?>" required>
                </div>
                <div class="input-box">
                    <span>Bank Account:</span>
                    <input type="text" name="bank_account" value="<?= htmlspecialchars($agent['bank_account'] ?? '') ?>" required>
                </div>
                <div class="input-box">
                    <strong>Total Commission Collected: RM<?= number_format($total_collected, 2) ?></strong>
                </div>
            </div>

            <div class="column">
                <h3 class="title">Simulate Payment</h3>
                <div class="input-box">
                    <span>Name on Card:</span>
                    <input type="text" placeholder="KUSMA Admin" required>
                </div>
                <div class="input-box">
                    <span>Card Number:</span>
                    <input type="text" placeholder="1111 2222 3333 4444" required>
                </div>
                <div class="input-box">
                    <span>Expiration:</span>
                    <input type="text" placeholder="04/29" required>
                </div>
                <div class="input-box">
                    <span>CVV:</span>
                    <input type="text" placeholder="123" required>
                </div>
                <div class="input-box">
                    <strong>Payment Total: RM<?= number_format($total_collected, 2) ?></strong>
                </div>
            </div>
        </div>

        <button type="submit" class="btn">Pay Agent Now</button>
    </form>
</div>
</body>
</html>
